<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        // Handle both cart_items[] and cart_item_ids parameter formats
        $selectedCartItemIds = $request->input('cart_items', $request->input('cart_item_ids', []));

        if (empty($selectedCartItemIds)) {
            return redirect()->route('cart.index')->with('error', 'Please select items to checkout');
        }

        try {
            // Validate cart items
            $validation = $this->cartService->validateCartItems($selectedCartItemIds);

            if (!$validation['valid']) {
                return redirect()->route('cart.index')
                    ->with('error', 'Some items in your cart are no longer available: ' . implode(', ', $validation['errors']));
            }

            $itemsByStore = $this->cartService->getCartItemsByStoreFiltered($selectedCartItemIds);
            $cartItems = $this->cartService->getCartItemsById($selectedCartItemIds);

            // Get current user
            $user = auth()->user();

            // Calculate summary
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $totalShippingFee = 0; // Free shipping
            $total = $subtotal + $totalShippingFee;

            return view('buyer.checkout.index', compact(
                'itemsByStore',
                'cartItems',
                'selectedCartItemIds',
                'subtotal',
                'totalShippingFee',
                'total',
                'user'
            ));
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    /**
     * FIXED: Process checkout sesuai database schema
     */
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'cart_items' => 'required|array',
            'cart_items.*' => 'integer|exists:carts,id',
            'shipping_address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:cod,transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Validate cart items again
            $validation = $this->cartService->validateCartItems($validated['cart_items']);
            if (!$validation['valid']) {
                return back()->withErrors(['cart' => implode(' ', $validation['errors'])]);
            }

            $cartItems = $validation['items'];

            // Calculate total amount
            $totalAmount = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            // FIXED: Create order dengan field yang sesuai database schema
            $order = \App\Models\Order::create([
                'buyer_id' => auth()->id(),           // FIXED: gunakan buyer_id
                'total_amount' => $totalAmount,       // FIXED: gunakan total_amount
                'status' => 'new',                    // FIXED: gunakan status enum yang valid
                'shipping_address' => $validated['shipping_address'],
                'phone' => $validated['phone'],
                'notes' => $validated['notes'] ?? null,
                // REMOVED: field yang tidak ada di database
                // 'user_id', 'store_id', 'order_number', 'payment_method', 'shipping_fee'
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'total' => $cartItem->product->price * $cartItem->quantity,
                ]);
            }

            // Remove cart items after successful order
            $this->cartService->removeMultipleItems($validated['cart_items']);

            // Redirect to order success page
            return redirect()->route('buyer.orders.show', $order->id)
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            \Log::error('Checkout error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'validated_data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while processing your order. Please try again.');
        }
    }
}
