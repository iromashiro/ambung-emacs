<?php

namespace App\Http\Controllers\Buyer;

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
        $perPage = $request->input('per_page', 10);
        $orders = $this->orderService->getOrdersByBuyer(auth()->user(), $perPage);
        
        return view('buyer.orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(string $id): View
    {
        $order = $this->orderService->getOrderById($id);
        
        // Check if user has permission to view this order
        if (auth()->id() !== $order->buyer_id) {
            abort(403, 'You do not have permission to view this order');
        }
        
        return view('buyer.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancel(string $id): RedirectResponse
    {
        $order = $this->orderService->getOrderById($id);
        
        // Check if user has permission to cancel this order
        if (auth()->id() !== $order->buyer_id) {
            abort(403, 'You do not have permission to cancel this order');
        }
        
        try {
            $this->orderService->cancelOrder($order, auth()->user());
            
            return redirect()->route('buyer.orders.show', $order->id)
                ->with('success', 'Order canceled successfully');
        } catch (\Exception $e) {
            return redirect()->route('buyer.orders.show', $order->id)
                ->with('error', $e->getMessage());
        }
    }
}