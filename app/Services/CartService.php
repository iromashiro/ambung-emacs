<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function getUserCart()
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect(); // Return empty collection for guests
        }

        return Cart::where('user_id', $userId)
            ->with('product.store')
            ->get();
    }

    /**
     * Get session cart for guest users
     */
    public function getSessionCart()
    {
        $sessionId = Session::getId();

        return Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('product.store')
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

        // Check if product already in cart
        if ($userId) {
            // User is logged in - check user cart
            $cartItem = Cart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();
        } else {
            // Guest user - check session cart
            $cartItem = Cart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->where('product_id', $productId)
                ->first();
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
            Cart::create([
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
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
        } elseif (!$userId && $cartItem->session_id !== $sessionId) {
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
        } elseif (!$userId && $cartItem->session_id !== $sessionId) {
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
        } else {
            Cart::where('session_id', $sessionId)->whereNull('user_id')->delete();
        }

        return collect();
    }

    protected function validateCartOwnership($cartItem)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        if ($userId && $cartItem->user_id !== $userId) {
            throw new \Exception('Unauthorized access to cart.');
        } elseif (!$userId && $cartItem->session_id !== $sessionId) {
            throw new \Exception('Unauthorized access to cart.');
        }
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
     * Get the subtotal price of specific cart items
     */
    public function getSubtotalForItems(array $cartItemIds)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $cartItems = Cart::whereIn('id', $cartItemIds)
            ->where(function ($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId)->whereNull('user_id');
                }
            })
            ->with('product')
            ->get();

        return $cartItems->sum(function ($item) {
            return $item->total;
        });
    }

    /**
     * Get cart items count
     */
    public function getCartItemsCount()
    {
        $cartItems = Auth::id() ? $this->getUserCart() : $this->getSessionCart();

        if ($cartItems->isEmpty()) {
            return 0;
        }

        return $cartItems->sum('quantity');
    }

    /**
     * Get cart items grouped by store
     */
    public function getCartItemsByStore()
    {
        $cartItems = Auth::id() ? $this->getUserCart() : $this->getSessionCart();

        if ($cartItems->isEmpty()) {
            return [];
        }

        $groupedItems = [];

        foreach ($cartItems as $item) {
            $storeName = $item->product->store->name;

            if (!isset($groupedItems[$storeName])) {
                $groupedItems[$storeName] = [];
            }

            $groupedItems[$storeName][] = $item;
        }

        return $groupedItems;
    }

    /**
     * Get specific cart items by IDs
     */
    public function getCartItemsById(array $cartItemIds)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        if ($userId) {
            return Cart::whereIn('id', $cartItemIds)
                ->where('user_id', $userId)
                ->with('product.store')
                ->get();
        } else {
            return Cart::whereIn('id', $cartItemIds)
                ->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->with('product.store')
                ->get();
        }
    }

    /**
     * Get specific cart items grouped by store
     */
    public function getCartItemsByStoreFiltered(array $cartItemIds)
    {
        $cartItems = $this->getCartItemsById($cartItemIds);

        if ($cartItems->isEmpty()) {
            return [];
        }

        $groupedItems = [];

        foreach ($cartItems as $item) {
            $storeName = $item->product->store->name;

            if (!isset($groupedItems[$storeName])) {
                $groupedItems[$storeName] = [];
            }

            $groupedItems[$storeName][] = $item;
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
            throw new \Exception('Cart items not found.');
        }

        $errors = [];

        foreach ($cartItems as $item) {
            // Check if product still exists and is active
            if (!$item->product || !$item->product->is_active) {
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
     * Calculate shipping fee for cart items
     */
    public function calculateShippingFee(array $cartItemIds)
    {
        $cartItems = $this->getCartItemsById($cartItemIds);
        $storeCount = $cartItems->pluck('product.store_id')->unique()->count();

        // Base shipping fee per store
        $baseShippingFee = 10000; // Rp 10,000 per store

        return $baseShippingFee * $storeCount;
    }

    /**
     * Get cart summary
     */
    public function getCartSummary()
    {
        $cart = Auth::id() ? $this->getUserCart() : $this->getSessionCart();

        if ($cart->isEmpty()) {
            return [
                'subtotal' => 0,
                'shipping_fee' => 0,
                'total' => 0,
                'items_count' => 0
            ];
        }

        $subtotal = $cart->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        $itemsCount = $cart->sum('quantity');

        return [
            'subtotal' => $subtotal,
            'shipping_fee' => 0, // Free shipping for COD
            'total' => $subtotal,
            'items_count' => $itemsCount
        ];
    }

    /**
     * Merge session cart with user cart after login - METHOD YANG HILANG
     * This method is called from LoginController after successful login
     */
    public function mergeSessionCartWithUserCart(User $user)
    {
        try {
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

    /**
     * Merge guest cart with user cart - ALTERNATIVE METHOD
     * This method can be used as alternative with different parameters
     */
    public function mergeGuestCart($userId)
    {
        try {
            $sessionId = Session::getId();
            Cart::mergeGuestCart($sessionId, $userId);

            \Log::info("Guest cart merged successfully for user {$userId}");
        } catch (\Exception $e) {
            \Log::error("Error merging guest cart for user {$userId}: " . $e->getMessage());
        }
    }

    /**
     * Clean up old session carts (can be called by scheduled task)
     */
    public function cleanupOldSessionCarts($daysOld = 7)
    {
        $cutoffDate = now()->subDays($daysOld);

        $deletedCount = Cart::whereNotNull('session_id')
            ->whereNull('user_id')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        \Log::info("Cleaned up {$deletedCount} old session cart items");

        return $deletedCount;
    }

    /**
     * Get session cart items grouped by store
     */
    public function getSessionCartByStore()
    {
        $cartItems = $this->getSessionCart();

        if ($cartItems->isEmpty()) {
            return [];
        }

        $groupedItems = [];

        foreach ($cartItems as $item) {
            $storeName = $item->product->store->name;

            if (!isset($groupedItems[$storeName])) {
                $groupedItems[$storeName] = [];
            }

            $groupedItems[$storeName][] = $item;
        }

        return $groupedItems;
    }
}
