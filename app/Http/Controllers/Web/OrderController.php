<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;
    
    public function __construct(OrderService $orderService)
    {
        $this->middleware('auth');
        $this->orderService = $orderService;
    }
    
    /**
     * Display a listing of the user's orders
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $filters = [];
        
        if ($status) {
            $filters['status'] = $status;
        }
        
        $orders = $this->orderService->getOrdersForUser(auth()->user(), $filters);
        
        return view('web.orders.index', compact('orders', 'status'));
    }
    
    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        // Check if order belongs to user
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }
        
        return view('web.orders.show', compact('order'));
    }
    
    /**
     * Cancel an order
     */
    public function cancel(Order $order)
    {
        // Check if order belongs to user
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }
        
        // Check if order can be canceled
        if (!$order->canBeCanceled()) {
            return redirect()->route('orders.show', $order)->with('error', 'This order cannot be canceled.');
        }
        
        try {
            $result = $this->orderService->updateOrderStatus($order, 'canceled', auth()->user());
            
            if ($result) {
                return redirect()->route('orders.show', $order)->with('success', 'Order canceled successfully.');
            } else {
                return redirect()->route('orders.show', $order)->with('error', 'Failed to cancel order.');
            }
        } catch (\Exception $e) {
            return redirect()->route('orders.show', $order)->with('error', $e->getMessage());
        }
    }
    
    /**
     * Confirm delivery of an order
     */
    public function confirmDelivery(Order $order)
    {
        // Check if order belongs to user
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }
        
        // Check if order can be confirmed as delivered
        if ($order->status !== 'dispatched') {
            return redirect()->route('orders.show', $order)->with('error', 'This order cannot be confirmed as delivered.');
        }
        
        try {
            $result = $this->orderService->updateOrderStatus($order, 'delivered', auth()->user());
            
            if ($result) {
                return redirect()->route('orders.show', $order)->with('success', 'Order confirmed as delivered.');
            } else {
                return redirect()->route('orders.show', $order)->with('error', 'Failed to confirm delivery.');
            }
        } catch (\Exception $e) {
            return redirect()->route('orders.show', $order)->with('error', $e->getMessage());
        }
    }
}