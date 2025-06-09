<?php

namespace App\Http\Controllers\Buyer;

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
        try {
            $cartItems = $this->cartService->getUserCart();
            $cartItemsByStore = $this->cartService->getCartItemsByStore();
            $totalPrice = $this->cartService->getTotalPrice();

            return view('buyer.cart.index', compact('cartItems', 'cartItemsByStore', 'totalPrice'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load cart: ' . $e->getMessage());
        }
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $this->cartService->addToCart(
                $request->product_id,
                $request->quantity
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'cart_count' => $this->cartService->getCartItemsCount()
                ]);
            }

            return redirect()->back()->with('success', 'Product added to cart successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

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

            if ($request->ajax()) {
                $totalPrice = $this->cartService->getTotalPrice();

                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'total_price' => $totalPrice,
                    'cart_count' => $this->cartService->getCartItemsCount()
                ]);
            }

            return redirect()->back()->with('success', 'Cart updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
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

            if ($request->ajax()) {
                $totalPrice = $this->cartService->getTotalPrice();

                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'total_price' => $totalPrice,
                    'cart_count' => $this->cartService->getCartItemsCount()
                ]);
            }

            return redirect()->back()->with('success', 'Item removed from cart');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeMultiple(Request $request)
    {
        $request->validate([
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'integer|exists:carts,id'
        ]);

        try {
            $this->cartService->removeMultipleItems($request->cart_item_ids);

            if ($request->ajax()) {
                $totalPrice = $this->cartService->getTotalPrice();

                return response()->json([
                    'success' => true,
                    'message' => 'Items removed from cart',
                    'total_price' => $totalPrice,
                    'cart_count' => $this->cartService->getCartItemsCount()
                ]);
            }

            return redirect()->back()->with('success', 'Items removed from cart');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function clear()
    {
        try {
            $this->cartService->clearCart();
            return redirect()->back()->with('success', 'Cart cleared successfully');
        } catch (\Exception $e) {
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
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
