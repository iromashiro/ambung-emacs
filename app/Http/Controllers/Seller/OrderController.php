<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->middleware(['auth', 'verified']);
        $this->middleware('role:seller');
        $this->middleware('store.owner');
    }

    /**
     * Display a listing of orders
     */
    // Tambahkan di method index() untuk debugging:
    public function index(Request $request): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        // Get paginated orders untuk table
        $orders = $this->orderService->getOrdersByStore($store);

        // PERBAIKAN UTAMA: Hitung stats dari database langsung, bukan dari paginated collection
        $stats = $this->calculateOrderStats($store);

        return view('seller.orders.index', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    /**
     * Calculate order statistics from database
     */
    private function calculateOrderStats($store): array
    {
        // Query langsung ke database untuk mendapatkan stats yang akurat
        $baseQuery = Order::whereHas('items.product', function ($q) use ($store) {
            $q->where('seller_id', $store->seller_id);
        });

        return [
            'new' => (clone $baseQuery)->where('status', 'new')->count(),
            'accepted' => (clone $baseQuery)->where('status', 'accepted')->count(),
            'dispatched' => (clone $baseQuery)->where('status', 'dispatched')->count(),
            'delivered' => (clone $baseQuery)->where('status', 'delivered')->count(),
            'canceled' => (clone $baseQuery)->where('status', 'canceled')->count(),
        ];
    }

    /**
     * Display new orders
     */
    public function new(Request $request): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        $orders = $this->orderService->getOrdersByStore($store, [
            'status' => 'new',
            'limit' => 20
        ]);

        return view('seller.orders.new', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display processing orders - FIXED: Use 'accepted' status
     */
    public function processing(Request $request): View
    {
        $store = auth()->user()->store;

        $orders = $this->orderService->getOrdersByStore($store, [
            'status' => 'accepted', // PERBAIKAN: Gunakan 'accepted' bukan 'processing'
            'limit' => 20
        ]);

        return view('seller.orders.processing', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display completed orders - FIXED: Use 'delivered' status
     */
    public function completed(Request $request): View
    {
        $store = auth()->user()->store;

        $orders = $this->orderService->getOrdersByStore($store, [
            'status' => 'delivered', // PERBAIKAN: Gunakan 'delivered' bukan 'completed'
            'limit' => 20
        ]);

        return view('seller.orders.completed', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display canceled orders
     */
    public function canceled(Request $request): View
    {
        $store = auth()->user()->store;

        $orders = $this->orderService->getOrdersByStore($store, [
            'status' => 'canceled',
            'limit' => 20
        ]);

        return view('seller.orders.canceled', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show the specified order - FIX TYPE MISMATCH
     */
    public function show(string $id): View
    {
        // Convert string to int to match OrderService::getOrderById signature
        $orderId = (int) $id;
        $order = Order::with([
            'user:id,name,email',
            // PERBAIKAN KRITIS: Only load items that belong to this seller
            'items' => function ($query) {
                $store = auth()->user()->store;
                $query->whereHas('product', function ($q) use ($store) {
                    $q->where('seller_id', $store->seller_id);
                });
            },
            'items.product:id,name,seller_id,price'
        ])->findOrFail($orderId);

        // Check if this order has any items from this seller
        $store = auth()->user()->store;
        $hasPermission = $order->items->count() > 0;

        if (!$hasPermission) {
            abort(403, 'You do not have permission to view this order');
        }

        // PERBAIKAN: Recalculate totals based on seller's items only
        $sellerSubtotal = $order->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return view('seller.orders.show', [
            'order' => $order,
            'sellerSubtotal' => $sellerSubtotal,
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,dispatched,delivered,canceled'
        ]);

        // Convert string to int to match OrderService::getOrderById signature
        $orderId = (int) $id;
        $order = $this->orderService->getOrderById($orderId);

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found');
        }

        try {
            $this->orderService->updateOrderStatus(
                $order,
                $request->status,
                auth()->user()
            );

            return redirect()->back()->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
