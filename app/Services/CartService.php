<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
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

    public function addToCart($productId, $quantity)
    {
        $userId = Auth::id();

        // Check if user is logged in
        if (!$userId) {
            throw new \Exception('You must be logged in to add items to cart.');
        }

        $product = Product::findOrFail($productId);

        // Check if product is in stock
        if ($product->stock < $quantity) {
            throw new \Exception('Product is out of stock or not enough quantity available.');
        }

        // Check if product already in cart
        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

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
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return $this->getUserCart();
    }

    public function updateCartItem($cartItemId, $quantity)
    {
        $userId = Auth::id();
        $cartItem = Cart::findOrFail($cartItemId);

        // Ensure cart belongs to user
        if ($cartItem->user_id !== $userId) {
            throw new \Exception('Unauthorized access to cart.');
        }

        // Check if product has enough stock
        if ($quantity > $cartItem->product->stock) {
            throw new \Exception('Cannot update quantity. Not enough stock available.');
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return $this->getUserCart();
    }

    public function removeCartItem($cartItemId)
    {
        $userId = Auth::id();
        $cartItem = Cart::findOrFail($cartItemId);

        // Ensure cart belongs to user
        if ($cartItem->user_id !== $userId) {
            throw new \Exception('Unauthorized access to cart.');
        }

        $cartItem->delete();

        return $this->getUserCart();
    }

    public function removeMultipleItems(array $cartItemIds)
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        // Validate all items belong to user's cart
        $cartItems = Cart::whereIn('id', $cartItemIds)
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->count() !== count($cartItemIds)) {
            throw new \Exception('Some cart items not found or do not belong to your cart.');
        }

        // Delete the items
        Cart::whereIn('id', $cartItemIds)->delete();

        return $this->getUserCart();
    }

    public function clearCart()
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        Cart::where('user_id', $userId)->delete();

        return collect();
    }

    protected function validateCartOwnership($cartItem)
    {
        $userId = Auth::id();

        if ($userId && $cartItem->user_id !== $userId) {
            throw new \Exception('Unauthorized access to cart.');
        }
    }

    /**
     * Get the total price of items in the cart
     *
     * @return float
     */
    public function getTotalPrice()
    {
        $cartItems = $this->getUserCart();

        if ($cartItems->isEmpty()) {
            return 0;
        }

        return $cartItems->sum(function ($item) {
            return $item->total;
        });
    }

    /**
     * Get the subtotal price of specific cart items
     *
     * @param array $cartItemIds
     * @return float
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
                    $query->where('session_id', $sessionId);
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
     *
     * @return int
     */
    public function getCartItemsCount()
    {
        $cartItems = $this->getUserCart();

        if ($cartItems->isEmpty()) {
            return 0;
        }

        return $cartItems->sum('quantity');
    }

    /**
     * Get cart items grouped by store
     *
     * @return array
     */
    public function getCartItemsByStore()
    {
        $cartItems = $this->getUserCart();

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
     *
     * @param array $cartItemIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCartItemsById(array $cartItemIds)
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        return Cart::whereIn('id', $cartItemIds)
            ->where('user_id', $userId)
            ->with('product.store')
            ->get();
    }

    /**
     * Get specific cart items grouped by store
     *
     * @param array $cartItemIds
     * @return array
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
     *
     * @param array $cartItemIds
     * @return array
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
     *
     * @param array $cartItemIds
     * @return float
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
     *
     * @param array|null $cartItemIds
     * @return array
     */
    public function getCartSummary()
    {
        $cart = $this->getUserCart();

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
     * Merge guest cart with user cart after login
     *
     * @param string $sessionId
     * @param string $userId
     * @return void
     */
    public function mergeGuestCart($userId)
    {
        Cart::mergeGuestCart($userId);
    }
}
