<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        try {
            $cartItems = Auth::check()
                ? $this->cartService->getUserCart()
                : $this->cartService->getSessionCart();

            $cartItemsByStore = Auth::check()
                ? $this->cartService->getCartItemsByStore()
                : $this->cartService->getSessionCartByStore();

            $totalPrice = $this->cartService->getTotalPrice();

            return view('buyer.cart.index', compact('cartItems', 'cartItemsByStore', 'totalPrice'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load cart: ' . $e->getMessage());
        }
    }

    public function add(Request $request)
    {
        // Validate request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            // Add to cart
            $this->cartService->addToCart(
                $request->product_id,
                $request->quantity
            );

            // Get updated cart count
            $cartCount = $this->cartService->getCartItemsCount();

            // Log success for debugging
            Log::info('Product added to cart successfully', [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'cart_count' => $cartCount
            ]);

            // ALWAYS return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully!',
                    'cart_count' => $cartCount
                ], 200, [
                    'Content-Type' => 'application/json'
                ]);
            }

            // Fallback for regular form submission
            return redirect()->back()->with('success', 'Product added to cart successfully');
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error adding product to cart', [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'error' => $e->getMessage()
            ]);

            // ALWAYS return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400, [
                    'Content-Type' => 'application/json'
                ]);
            }

            // Fallback for regular form submission
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:carts,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $this->cartService->updateCartItem(
                $request->cart_item_id,
                $request->quantity
            );

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                $totalPrice = $this->cartService->getTotalPrice();

                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'total_price' => $totalPrice,
                    'cart_count' => $this->cartService->getCartItemsCount()
                ], 200, [
                    'Content-Type' => 'application/json'
                ]);
            }

            return redirect()->back()->with('success', 'Cart updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400, [
                    'Content-Type' => 'application/json'
                ]);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:carts,id'
        ]);

        try {
            $this->cartService->removeCartItem($request->cart_item_id);

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                $totalPrice = $this->cartService->getTotalPrice();

                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'total_price' => $totalPrice,
                    'cart_count' => $this->cartService->getCartItemsCount()
                ], 200, [
                    'Content-Type' => 'application/json'
                ]);
            }

            return redirect()->back()->with('success', 'Item removed from cart');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400, [
                    'Content-Type' => 'application/json'
                ]);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function count()
    {
        try {
            $count = $this->cartService->getCartItemsCount();

            return response()->json([
                'success' => true,
                'count' => $count
            ], 200, [
                'Content-Type' => 'application/json'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    }
}
