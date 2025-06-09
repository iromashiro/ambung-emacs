<?php
// app/Http/Controllers/Web/CartController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;
    protected $productService;

    public function __construct(
        CartService $cartService,
        ProductService $productService
    ) {
        $this->cartService = $cartService;
        $this->productService = $productService;
    }

    /**
     * Display the shopping cart
     */
    public function index()
    {
        $cart = $this->cartService->getUserCart();
        $summary = $this->cartService->getCartSummary();

        return view('web.cart.index', compact('cart', 'summary'));
    }

    /**
     * Add a product to the cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999'
        ]);

        try {
            $this->cartService->addToCart(
                $request->product_id,
                $request->quantity
            );

            return redirect()->back()->with('success', 'Product added to cart successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:999'
        ]);

        try {
            $this->cartService->updateCartItem(
                $cartItem,
                $request->quantity
            );

            return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Remove an item from the cart
     */
    public function remove($cartItem)
    {
        try {
            $this->cartService->removeCartItem($cartItem);

            return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Clear the entire cart
     */
    public function clear()
    {
        $this->cartService->clearCart();

        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully.');
    }
}
