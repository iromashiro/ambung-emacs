<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $orderService;

    public function __construct(CartService $cartService, OrderService $orderService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        // FIXED: Handle both cart_items[] and cart_item_ids parameter formats
        $selectedCartItemIds = $request->input('cart_items', $request->input('cart_item_ids', [])); // FIXED variable name

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

            $itemsByStore = $this->cartService->getCartItemsByStoreFiltered($selectedCartItemIds); // FIXED variable name
            $cartItems = $this->cartService->getCartItemsById($selectedCartItemIds);

            // Get user addresses if available
            $addresses = auth()->check() ? auth()->user()->addresses : collect([]);
            $selectedAddress = $addresses->where('is_default', true)->first() ?? $addresses->first();

            // ADDED: Get current user
            $user = auth()->user();

            // Calculate summary
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $storeCount = $cartItems->pluck('product.seller.store.id')->unique()->count();
            $shippingFeePerStore = 0; // Rp 10,000 per store
            $totalShippingFee = $shippingFeePerStore * $storeCount;
            $total = $subtotal + $totalShippingFee;

            return view('buyer.checkout.index', compact(
                'itemsByStore',           // FIXED variable name
                'cartItems',
                'addresses',
                'selectedAddress',
                'selectedCartItemIds',    // FIXED variable name
                'subtotal',
                'totalShippingFee',
                'total',
                'shippingFeePerStore',
                'user'                    // ADDED missing user variable
            ));
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Process checkout - Handle POST request
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

        // Validate cart items again
        $validation = $this->cartService->validateCartItems($validated['cart_items']);
        if (!$validation['valid']) {
            return back()->withErrors(['cart' => implode(' ', $validation['errors'])]);
        }

        $cartItems = $validation['items'];

        // Group by store for multiple orders
        $itemsByStore = $this->cartService->getCartItemsByStoreFiltered($validated['cart_items']);

        $orders = [];

        // Create order for each store
        foreach ($itemsByStore as $storeName => $items) {
            $storeTotal = $items->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $storeShippingFee = 10000; // Fixed shipping per store

            // Get store ID from first item
            $storeId = $items->first()->product->seller->store->id ?? null;

            $order = \App\Models\Order::create([
                'user_id' => auth()->id(),
                'store_id' => $storeId,
                'order_number' => 'ORD-' . time() . '-' . rand(1000, 9999),
                'status' => 'pending',
                'total_amount' => $storeTotal + $storeShippingFee,
                'shipping_address' => $validated['shipping_address'],
                'phone' => $validated['phone'],
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'shipping_fee' => $storeShippingFee,
            ]);

            // Create order items
            foreach ($items as $cartItem) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'total' => $cartItem->product->price * $cartItem->quantity,
                ]);
            }

            $orders[] = $order;
        }

        // Remove cart items after successful order
        $this->cartService->removeMultipleItems($validated['cart_items']);

        // Redirect to order success page
        if (count($orders) === 1) {
            return redirect()->route('buyer.orders.show', $orders[0]->id)
                ->with('success', 'Order placed successfully!');
        } else {
            return redirect()->route('buyer.orders.index')
                ->with('success', count($orders) . ' orders placed successfully!');
        }
    }

    /**
     * Direct checkout from product page
     */
    public function direct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            // Add product to cart temporarily for direct checkout
            $this->cartService->addToCart($request->product_id, $request->quantity);

            // Get the cart item that was just added
            $cartItems = $this->cartService->getUserCart();
            $cartItem = $cartItems->where('product_id', $request->product_id)->first();

            // Redirect to checkout with this item
            return redirect()->route('checkout.index', ['cart_items' => [$cartItem->id]]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
