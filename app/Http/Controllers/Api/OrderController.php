<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;

class OrderController extends Controller
{
    protected $orderService;
    protected $cartService;

    public function __construct(OrderService $orderService, CartService $cartService)
    {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status']);
        $orders = $this->orderService->getOrdersForUser(auth()->user(), $filters);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(CreateOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrderFromCart(
                $request->items,
                auth()->user(),
                $request->only(['shipping_address', 'phone', 'notes'])
            );

            // Clear cart after successful order
            $this->cartService->clearCart(auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:accepted,dispatched,delivered,canceled'
        ]);

        try {
            $result = $this->orderService->updateOrderStatus(
                $order,
                $request->status,
                auth()->user()
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'data' => $order->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status'
                ], 500);
            }
        } catch (InvalidStatusTransitionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
