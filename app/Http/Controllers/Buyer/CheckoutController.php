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

        \Log::info('Checkout index accessed', [
            'user_id' => auth()->id(),
            'requested_cart_items' => $selectedCartItemIds,
            'url' => $request->fullUrl()
        ]);

        if (empty($selectedCartItemIds)) {
            return redirect()->route('cart.index')->with('error', 'Please select items to checkout');
        }

        try {
            // FIRST: Check if cart items actually exist for this user
            $availableCartItems = auth()->check()
                ? $this->cartService->getUserCart()
                : $this->cartService->getSessionCart();

            $availableCartItemIds = $availableCartItems->pluck('id')->toArray();

            \Log::info('Available cart items for user', [
                'user_id' => auth()->id(),
                'available_cart_item_ids' => $availableCartItemIds,
                'requested_cart_item_ids' => $selectedCartItemIds
            ]);

            // Find missing cart items
            $missingCartItemIds = array_diff($selectedCartItemIds, $availableCartItemIds);

            if (!empty($missingCartItemIds)) {
                \Log::warning('Some cart items not found', [
                    'missing_cart_item_ids' => $missingCartItemIds,
                    'available_cart_item_ids' => $availableCartItemIds,
                    'requested_cart_item_ids' => $selectedCartItemIds
                ]);

                // Filter out missing items and continue with available ones
                $selectedCartItemIds = array_intersect($selectedCartItemIds, $availableCartItemIds);

                if (empty($selectedCartItemIds)) {
                    return redirect()->route('cart.index')
                        ->with('error', 'Selected cart items are no longer available. Please refresh your cart and try again.');
                }

                // Show warning but continue
                session()->flash('warning', 'Some items were removed from your selection because they are no longer available.');
            }

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
            \Log::error('Checkout index error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('cart.index')->with('error', 'Unable to load checkout page. Please try again.');
        }
    }

    /**
     * Process checkout with enhanced error handling
     */
    public function store(Request $request)
    {
        // Log the incoming request
        \Log::info('Checkout store request', [
            'user_id' => auth()->id(),
            'request_data' => $request->all(),
            'url' => $request->fullUrl()
        ]);

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
            // ENHANCED: Verify cart items belong to current user/session
            $userId = auth()->id();
            $sessionId = session()->getId();

            // Get user's actual cart items
            if ($userId) {
                $userCartItems = \App\Models\Cart::where('user_id', $userId)
                    ->pluck('id')
                    ->toArray();
            } else {
                $userCartItems = \App\Models\Cart::where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->pluck('id')
                    ->toArray();
            }

            // Check if requested cart items belong to user
            $invalidCartItems = array_diff($validated['cart_items'], $userCartItems);
            if (!empty($invalidCartItems)) {
                \Log::error('Invalid cart items detected', [
                    'user_id' => $userId,
                    'invalid_cart_items' => $invalidCartItems,
                    'user_cart_items' => $userCartItems
                ]);

                return back()->with('error', 'Some selected items do not belong to your cart. Please refresh and try again.');
            }

            // Validate cart items again
            $validation = $this->cartService->validateCartItems($validated['cart_items']);
            if (!$validation['valid']) {
                return back()->withErrors(['cart' => implode(' ', $validation['errors'])]);
            }

            $cartItems = $validation['items'];

            // PERBAIKAN UTAMA: Group cart items by store/seller
            $itemsByStore = $cartItems->groupBy(function ($item) {
                return $item->product->seller_id;
            });

            \Log::info('Cart items grouped by store', [
                'user_id' => $userId,
                'stores_count' => $itemsByStore->count(),
                'stores' => $itemsByStore->keys()->toArray()
            ]);

            $createdOrders = [];

            // PERBAIKAN: Create separate order for each store
            foreach ($itemsByStore as $sellerId => $storeItems) {
                // Calculate total amount for this store
                $storeTotal = $storeItems->sum(function ($item) {
                    return $item->product->price * $item->quantity;
                });

                \Log::info('Creating order for store', [
                    'seller_id' => $sellerId,
                    'items_count' => $storeItems->count(),
                    'store_total' => $storeTotal
                ]);

                // Create order for this store
                $order = \App\Models\Order::create([
                    'buyer_id' => $userId,
                    'total_amount' => $storeTotal,
                    'status' => 'new',
                    'shipping_address' => $validated['shipping_address'],
                    'phone' => $validated['phone'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Create order items for this store only
                foreach ($storeItems as $cartItem) {
                    // Validate data
                    if (!$cartItem->product) {
                        throw new \Exception("Product not found for cart item {$cartItem->id}");
                    }

                    if (!$cartItem->quantity || $cartItem->quantity <= 0) {
                        throw new \Exception("Invalid quantity for cart item {$cartItem->id}");
                    }

                    if (!$cartItem->product->price || $cartItem->product->price <= 0) {
                        throw new \Exception("Invalid product price for cart item {$cartItem->id}");
                    }

                    $itemTotal = $cartItem->product->price * $cartItem->quantity;

                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => (int)$cartItem->quantity,
                        'price' => (float)$cartItem->product->price,
                        'total' => (float)$itemTotal,
                    ]);

                    \Log::info('Created order item', [
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'seller_id' => $cartItem->product->seller_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                        'total' => $itemTotal
                    ]);
                }

                $createdOrders[] = $order;
            }

            // Remove cart items after successful order
            $this->cartService->removeMultipleItems($validated['cart_items']);

            \Log::info('Checkout completed successfully', [
                'user_id' => $userId,
                'orders_created' => count($createdOrders),
                'order_ids' => array_map(fn($order) => $order->id, $createdOrders)
            ]);

            // Redirect to first order (or create a summary page later)
            $firstOrder = $createdOrders[0];

            if (count($createdOrders) > 1) {
                return redirect()->route('buyer.orders.index')
                    ->with('success', 'Orders placed successfully! You have ' . count($createdOrders) . ' orders from different stores.');
            } else {
                return redirect()->route('buyer.orders.show', $firstOrder->id)
                    ->with('success', 'Order placed successfully!');
            }
        } catch (\Exception $e) {
            \Log::error('Checkout error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'validated_data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while processing your order. Please try again. Error: ' . $e->getMessage());
        }
    }
}
