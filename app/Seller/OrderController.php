<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $store = $user->store;
        
        if (!$store) {
            return redirect()->route('seller.dashboard')
                            ->with('error', 'You need to create a store first.');
        }
        
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);
        
        if ($status) {
            $orders = $this->orderRepository->findByStoreAndStatusWithPagination($store->id, $status, $perPage);
        } else {
            $orders = $this->orderService->getStoreOrders($store->id, $perPage);
        }
        
        return view('seller.orders.index', compact('orders', 'status'));
    }

    public function show(string $id, Request $request)
    {
        $user = $request->user();
        $store = $user->store;
        
        if (!$store) {
            return redirect()->route('seller.dashboard')
                            ->with('error', 'You need to create a store first.');
        }
        
        $order = $this->orderRepository->findByIdWithRelations($id, ['buyer', 'items.product']);
        
        if (!$order) {
            return redirect()->route('seller.orders.index')
                            ->with('error', 'Order not found.');
        }
        
        // Check if order belongs to the seller's store
        if ($order->store_id !== $store->id) {
            return redirect()->route('seller.orders.index')
                            ->with('error', 'You do not have permission to view this order.');
        }
        
        return view('seller.orders.show', compact('order'));
    }

    public function updateStatus(string $id, Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:ACCEPTED,DISPATCHED,DELIVERED,CANCELED',
        ]);
        
        $