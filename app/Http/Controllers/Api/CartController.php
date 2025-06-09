<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart(auth()->user());
        $total = $this->cartService->getCartTotal(auth()->user());

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cart,
                'total' => $total
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999'
        ]);

        $result = $this->cartService->addToCart(
            $request->product_id,
            $request->quantity,
            auth()->user()
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart'
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:999'
        ]);

        $result = $this->cartService->updateCartItem(
            $id,
            $request->quantity,
            auth()->user()
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item'
            ], 422);
        }
    }

    public function destroy($id)
    {
        $result = $this->cartService->removeFromCart($id, auth()->user());

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart'
            ], 422);
        }
    }

    public function clear()
    {
        $this->cartService->clearCart(auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}
