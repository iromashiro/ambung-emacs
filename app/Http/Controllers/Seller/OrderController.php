<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
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
    public function index(Request $request): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        $orders = $this->orderService->getOrdersByStore($store);

        return view('seller.orders.index', [
            'orders' => $orders,
        ]);
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
     * Display processing orders
     */
    public function processing(Request $request): View
    {
        $store = auth()->user()->store;

        $orders = $this->orderService->getOrdersByStore($store, [
            'status' => 'processing',
            'limit' => 20
        ]);

        return view('seller.orders.processing', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display completed orders
     */
    public function completed(Request $request): View
    {
        $store = auth()->user()->store;

        $orders = $this->orderService->getOrdersByStore($store, [
            'status' => 'completed',
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
        $order = $this->orderService->getOrderById($orderId);

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Check if user has permission to view this order
        $store = auth()->user()->store;
        $hasPermission = $order->items->some(function ($item) use ($store) {
            return $item->product->seller_id === $store->seller_id;
        });

        if (!$hasPermission) {
            abort(403, 'You do not have permission to view this order');
        }

        return view('seller.orders.show', [
            'order' => $order,
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
