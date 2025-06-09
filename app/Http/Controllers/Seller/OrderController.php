<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $store = auth()->user()->store;
        
        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }
        
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);
        
        if ($status) {
            $orders = app('App\Repositories\Interfaces\OrderRepositoryInterface')
                ->findByStoreAndStatusWithPagination($store->id, $status, $perPage);
        } else {
            $orders = $this->orderService->getOrdersByStore($store->id, $perPage);
        }
        
        return view('seller.orders.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(string $id): View
    {
        $order = $this->orderService->getOrderById($id);
        
        // Check if user has permission to view this order
        if (auth()->user()->store->id !== $order->store_id) {
            abort(403, 'You do not have permission to view this order');
        }
        
        return view('seller.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:ACCEPTED,DISPATCHED,DELIVERED,CANCELED'],
        ]);
        
        $order = $this->orderService->getOrderById($id);
        
        // Check if user has permission to update this order
        if (auth()->user()->store->id !== $order->store_id) {
            abort(403, 'You do not have permission to update this order');
        }
        
        try {
            $this->orderService->updateStatus(
                $order,
                $validated['status'],
                auth()->user()
            );
            
            return redirect()->route('seller.orders.show', $order->id)
                ->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('seller.orders.show', $order->id)
                ->with('error', $e->getMessage());
        }
    }
}