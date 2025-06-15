<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

class CartService
{
    protected $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Check if session_id column exists in carts table
     */
    private function hasSessionIdColumn(): bool
    {
        return Schema::hasColumn('carts', 'session_id');
    }

    public function getUserCart()
    {
        $userId = Auth::id();

        if (!$userId) {
            return $this->getSessionCart();
        }

        return Cart::where('user_id', $userId)
            ->with(['product.seller.store', 'product.images'])
            ->get();
    }

    /**
     * Get session cart for guest users - with safe column check
     */
    public function getSessionCart()
    {
        // If session_id column doesn't exist yet, return empty collection
        if (!$this->hasSessionIdColumn()) {
            return collect();
        }

        $sessionId = Session::getId();

        return Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with(['product.seller.store', 'product.images'])
            ->get();
    }

    public function addToCart($productId, $quantity)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $product = Product::findOrFail($productId);

        // Check if product is in stock
        if ($product->stock < $quantity) {
            throw new \Exception('Product is out of stock or not enough quantity available.');
        }

        // Check if session_id column exists
        $hasSessionColumn = $this->hasSessionIdColumn();

        // Check if product already in cart
        if ($userId) {
            // User is logged in - check user cart
            $cartItem = Cart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();
        } elseif ($hasSessionColumn) {
            // Guest user and session column exists - check session cart
            $cartItem = Cart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->where('product_id', $productId)
                ->first();
        } else {
            // Session column doesn't exist, can't support guest cart yet
            throw new \Exception('Please login to add products to cart.');
        }

        if ($cartItem) {
            // Update quantity if product already in cart
            $newQuantity = $cartItem->quantity + $quantity;

            // Check if new quantity exceeds stock
            if ($newQuantity > $product->stock) {
                throw new \Exception('Cannot add more of this product. Stock limit reached.');
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            // Add new cart item
            $cartData = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];

            if ($userId) {
                $cartData['user_id'] = $userId;
            } elseif ($hasSessionColumn) {
                $cartData['session_id'] = $sessionId;
            } else {
                throw new \Exception('Please login to add products to cart.');
            }

            Cart::create($cartData);
        }

        return $userId ? $this->getUserCart() : $this->getSessionCart();
    }

    public function updateCartItem($cartItemId, $quantity)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();
        $cartItem = Cart::findOrFail($cartItemId);

        // Ensure cart belongs to user or session
        if ($userId && $cartItem->user_id !== $userId) {
            throw new \Exception('Unauthorized access to cart.');
        } elseif (!$userId && $this->hasSessionIdColumn() && $cartItem->session_id !== $sessionId) {
            throw new \Exception('Unauthorized access to cart.');
        }

        // Check if product has enough stock
        if ($quantity > $cartItem->product->stock) {
            throw new \Exception('Cannot update quantity. Not enough stock available.');
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return $userId ? $this->getUserCart() : $this->getSessionCart();
    }

    public function removeCartItem($cartItemId)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();
        $cartItem = Cart::findOrFail($cartItemId);

        // Ensure cart belongs to user or session
        if ($userId && $cartItem->user_id !== $userId) {
            throw new \Exception('Unauthorized access to cart.');
        } elseif (!$userId && $this->hasSessionIdColumn() && $cartItem->session_id !== $sessionId) {
            throw new \Exception('Unauthorized access to cart.');
        }

        $cartItem->delete();

        return $userId ? $this->getUserCart() : $this->getSessionCart();
    }

    public function removeMultipleItems(array $cartItemIds)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        if ($userId) {
            // Validate all items belong to user's cart
            $cartItems = Cart::whereIn('id', $cartItemIds)
                ->where('user_id', $userId)
                ->get();
        } else {
            // Validate all items belong to session cart
            $cartItems = Cart::whereIn('id', $cartItemIds)
                ->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->get();
        }

        if ($cartItems->count() !== count($cartItemIds)) {
            throw new \Exception('Some cart items not found or do not belong to your cart.');
        }

        // Delete the items
        Cart::whereIn('id', $cartItemIds)->delete();

        return $userId ? $this->getUserCart() : $this->getSessionCart();
    }

    public function clearCart()
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        if ($userId) {
            Cart::where('user_id', $userId)->delete();
        } elseif ($this->hasSessionIdColumn()) {
            Cart::where('session_id', $sessionId)->whereNull('user_id')->delete();
        }

        return collect();
    }

    /**
     * Get the total price of items in the cart
     */
    public function getTotalPrice()
    {
        $cartItems = Auth::id() ? $this->getUserCart() : $this->getSessionCart();

        if ($cartItems->isEmpty()) {
            return 0;
        }

        return $cartItems->sum(function ($item) {
            return $item->total;
        });
    }

    /**
     * Get cart items count
     */
    public function getCartItemsCount()
    {
        try {
            $cartItems = Auth::id() ? $this->getUserCart() : $this->getSessionCart();

            if ($cartItems->isEmpty()) {
                return 0;
            }

            return $cartItems->sum('quantity');
        } catch (\Exception $e) {
            // Return 0 if there's any error (like missing column)
            return 0;
        }
    }

    /**
     * Get specific cart items by IDs with comprehensive validation
     */
    public function getCartItemsById(array $cartItemIds)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        \Log::info('Getting cart items by IDs', [
            'cart_item_ids' => $cartItemIds,
            'user_id' => $userId,
            'session_id' => $sessionId
        ]);

        if ($userId) {
            $cartItems = Cart::whereIn('id', $cartItemIds)
                ->where('user_id', $userId)
                ->with(['product.seller.store', 'product.images'])
                ->get();
        } else {
            $cartItems = Cart::whereIn('id', $cartItemIds)
                ->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->with(['product.seller.store', 'product.images'])
                ->get();
        }

        \Log::info('Cart items retrieved', [
            'requested_count' => count($cartItemIds),
            'retrieved_count' => $cartItems->count(),
            'cart_items' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'has_product' => isset($item->product),
                    'product_price' => $item->product->price ?? 'NULL'
                ];
            })
        ]);

        // Validate that we got all requested items
        if ($cartItems->count() !== count($cartItemIds)) {
            $foundIds = $cartItems->pluck('id')->toArray();
            $missingIds = array_diff($cartItemIds, $foundIds);
            throw new \Exception('Cart items not found: ' . implode(', ', $missingIds));
        }

        // Validate that all items have valid data
        foreach ($cartItems as $item) {
            if (!$item->product) {
                throw new \Exception("Product not found for cart item {$item->id}");
            }

            if (is_null($item->quantity) || $item->quantity <= 0) {
                throw new \Exception("Invalid quantity for cart item {$item->id}: quantity is '{$item->quantity}'");
            }

            if (is_null($item->product->price) || $item->product->price <= 0) {
                throw new \Exception("Invalid product price for cart item {$item->id}: price is '{$item->product->price}'");
            }

            // Additional validation untuk memastikan product masih aktif
            if ($item->product->status !== 'active') {
                throw new \Exception("Product '{$item->product->name}' is no longer active");
            }

            // Validate stock
            if ($item->quantity > $item->product->stock) {
                throw new \Exception("Insufficient stock for product '{$item->product->name}'. Available: {$item->product->stock}, Requested: {$item->quantity}");
            }
        }

        return $cartItems;
    }

    /**
     * Get cart items grouped by store - FIXED VERSION
     * Return items grouped by store ID instead of store name
     */
    public function getCartItemsByStore()
    {
        $cartItems = Auth::id() ? $this->getUserCart() : $this->getSessionCart();

        if ($cartItems->isEmpty()) {
            return [];
        }

        $groupedItems = [];

        foreach ($cartItems as $item) {
            // Use store ID as key instead of store name
            $storeId = $item->product->seller->store->id ?? 'unknown';

            if (!isset($groupedItems[$storeId])) {
                $groupedItems[$storeId] = [];
            }

            $groupedItems[$storeId][] = $item;
        }

        return $groupedItems;
    }

    /**
     * Get session cart items grouped by store
     */
    public function getSessionCartByStore()
    {
        return $this->getCartItemsByStore(); // Use the same method
    }

    /**
     * Merge session cart with user cart after login
     */
    public function mergeSessionCartWithUserCart(User $user)
    {
        try {
            // Only proceed if session_id column exists
            if (!$this->hasSessionIdColumn()) {
                return;
            }

            $sessionId = Session::getId();

            // Get guest cart items from session
            $guestCartItems = Cart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->get();

            if ($guestCartItems->isEmpty()) {
                return; // No guest cart to merge
            }

            foreach ($guestCartItems as $guestItem) {
                // Check if user already has this product in their cart
                $existingCartItem = Cart::where('user_id', $user->id)
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existingCartItem) {
                    // Merge quantities (respecting stock limits)
                    $totalQuantity = $existingCartItem->quantity + $guestItem->quantity;
                    $maxStock = $guestItem->product->stock;

                    // Set to maximum available stock if total exceeds stock
                    $existingCartItem->quantity = min($totalQuantity, $maxStock);
                    $existingCartItem->save();

                    // Delete guest cart item
                    $guestItem->delete();
                } else {
                    // Transfer guest cart item to user
                    $guestItem->user_id = $user->id;
                    $guestItem->session_id = null; // Clear session_id
                    $guestItem->save();
                }
            }

            // Log successful merge
            \Log::info("Cart merged successfully for user {$user->id}");
        } catch (\Exception $e) {
            // Log error but don't break login process
            \Log::error("Error merging cart for user {$user->id}: " . $e->getMessage());
        }
    }

    public function getCartItemsByStoreFiltered(array $cartItemIds)
    {
        $cartItems = $this->getCartItemsById($cartItemIds);

        if ($cartItems->isEmpty()) {
            return [];
        }

        $groupedItems = [];

        foreach ($cartItems as $item) {
            $storeName = $item->product->seller->store->name ?? 'Unknown Store';

            if (!isset($groupedItems[$storeName])) {
                $groupedItems[$storeName] = collect();
            }

            $groupedItems[$storeName]->push($item);
        }

        return $groupedItems;
    }

    /**
     * Validate cart items availability and stock
     */
    public function validateCartItems(array $cartItemIds)
    {
        $cartItems = $this->getCartItemsById($cartItemIds);

        if ($cartItems->isEmpty()) {
            return [
                'valid' => false,
                'errors' => ['Cart items not found.'],
                'items' => collect()
            ];
        }

        $errors = [];

        foreach ($cartItems as $item) {
            // Check if product still exists and is active
            if (!$item->product || $item->product->status !== 'active') {
                $errors[] = "Product '{$item->product->name}' is no longer available.";
                continue;
            }

            // Check stock availability
            if ($item->quantity > $item->product->stock) {
                $errors[] = "Product '{$item->product->name}' has insufficient stock. Available: {$item->product->stock}, Requested: {$item->quantity}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'items' => $cartItems
        ];
    }

    /**
     * Calculate shipping fee for selected cart items
     */
    public function calculateShippingFee(array $cartItemIds)
    {
        $cartItems = $this->getCartItemsById($cartItemIds);
        $storeCount = $cartItems->pluck('product.seller.store.id')->unique()->count();

        // Base shipping fee per store
        $baseShippingFee = 10000; // Rp 10,000 per store

        return $baseShippingFee * $storeCount;
    }
}
