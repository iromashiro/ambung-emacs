<?php
// app/Http/Controllers/Web/CheckoutController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $orderService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    /**
     * Display the checkout page
     */
    public function index()
    {
        $user = auth()->user();
        // Change getCart() to getUserCart()
        $cart = $this->cartService->getUserCart();
        // Change getCartTotal() to getCartSummary()
        $summary = $this->cartService->getCartSummary();

        // Check if cart is empty
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add products before checkout.');
        }

        // Get user addresses
        $addresses = $user->addresses ?? collect();

        // Get default address
        $defaultAddress = $addresses->where('is_default', true)->first();

        return view('web.checkout.index', compact(
            'cart',
            'summary',
            'addresses',
            'defaultAddress'
        ));
    }

    /**
     * Process the checkout and create order
     */
    public function process(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500'
        ]);

        $user = auth()->user();
        // Change getCart() to getUserCart()
        $cart = $this->cartService->getUserCart();

        // Check if cart is empty
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add products before checkout.');
        }

        try {
            // Convert cart items to order items format
            $cartItems = $cart->map(function ($item) {
                return [
                    'product_id' => $item->product->id,
                    'quantity' => $item->quantity
                ];
            })->toArray();

            // Create order
            $order = $this->orderService->createOrderFromCart(
                $cartItems,
                $user,
                [
                    'shipping_address' => $request->shipping_address,
                    'phone' => $request->phone,
                    'notes' => $request->notes
                ]
            );

            // Clear cart after successful order
            $this->cartService->clearCart();

            return redirect()->route('checkout.success', $order->id);
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('checkout.index')->with('error', 'An error occurred while processing your order. Please try again.');
        }
    }

    /**
     * Display the order success page
     */
    public function success(Order $order)
    {
        // Check if order belongs to user
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        return view('web.checkout.success', compact('order'));
    }
}
