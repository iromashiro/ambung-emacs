<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $orders = Order::where('buyer_id', auth()->id())
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('buyer.orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user can only see their own orders
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order');
        }

        $order->load(['items.product', 'buyer']);

        return view('buyer.orders.show', compact('order'));
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order');
        }

        if (!$order->canBeCanceled()) {
            return back()->with('error', 'This order cannot be canceled');
        }

        $order->update(['status' => 'canceled']);

        return back()->with('success', 'Order has been canceled successfully');
    }

    /**
     * Confirm delivery of the order.
     */
    public function confirmDelivery(Order $order)
    {
        // Ensure user can only confirm their own orders
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order');
        }

        if ($order->status !== 'dispatched') {
            return back()->with('error', 'Only dispatched orders can be confirmed as delivered');
        }

        $order->update(['status' => 'delivered']);

        return back()->with('success', 'Order delivery has been confirmed');
    }
}
