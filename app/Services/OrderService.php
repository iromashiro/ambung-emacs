<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Events\OrderCreated;
use App\Events\OrderStatusUpdate;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderService
{
    protected $orderRepository;
    protected $productRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }

    public function getOrderById(int $id): ?Order
    {
        $order = $this->orderRepository->findById($id);

        if ($order) {
            Gate::authorize('view', $order);
        }

        return $order;
    }

    /**
     * Get orders by store - MISSING METHOD ADDED
     */
    // Di OrderService.php
    public function getOrdersByStore($store, array $options = [])
    {
        $limit = $options['limit'] ?? 10;

        return Order::whereHas('items.product', function ($q) use ($store) {
            $q->where('seller_id', $store->seller_id);
        })
            ->with([
                'user:id,name,email',
                'items:id,order_id,product_id,quantity,price',
                'items.product:id,name,seller_id'
            ])
            ->select('orders.*')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    public function getRecentOrders(User $user, array $filters = [])
    {
        $limit = $filters['limit'] ?? 5;

        if ($user->role === 'admin') {
            return $this->orderRepository->getRecentOrders($limit);
        } elseif ($user->role === 'seller') {
            return $this->orderRepository->getOrdersBySeller($user->id, ['limit' => $limit]);
        } else {
            return $this->orderRepository->getOrdersByBuyer($user->id, ['limit' => $limit]);
        }
    }

    public function getOrdersByDateRange(User $user, string $startDate, string $endDate, array $filters = [])
    {
        if ($user->role === 'admin') {
            return $this->orderRepository->getOrdersByDateRange($startDate, $endDate, $filters);
        } elseif ($user->role === 'seller') {
            $filters['seller_id'] = $user->id;
            return $this->orderRepository->getOrdersByDateRange($startDate, $endDate, $filters);
        } else {
            $filters['buyer_id'] = $user->id;
            return $this->orderRepository->getOrdersByDateRange($startDate, $endDate, $filters);
        }
    }

    /**
     * Get orders for a specific user (seller)
     */
    public function getOrdersForUser($user, array $options = [])
    {
        try {
            $limit = $options['limit'] ?? 10;

            return \App\Models\Order::whereHas('items.product', function ($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
                ->with(['user', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error in getOrdersForUser: ' . $e->getMessage());
            return collect();
        }
    }

    public function createOrderFromCart(array $cartItems, User $buyer, array $shippingData): Order
    {
        Gate::authorize('create', Order::class);

        return DB::transaction(function () use ($cartItems, $buyer, $shippingData) {
            $totalAmount = 0;
            $orderItems = [];

            // Validate stock and calculate total
            foreach ($cartItems as $item) {
                $product = $this->productRepository->findById($item['product_id']);

                if (!$product || $product->status !== 'active') {
                    throw new \Exception("Product not available: {$product->name}");
                }

                if ($product->stock < $item['quantity']) {
                    throw new InsufficientStockException("Insufficient stock for product: {$product->name}");
                }

                $itemTotal = $product->price * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $itemTotal
                ];

                // Update product stock
                $this->productRepository->updateStock($product->id, $item['quantity']);
            }

            // Create order
            $order = $this->orderRepository->create([
                'buyer_id' => $buyer->id,
                'total_amount' => $totalAmount,
                'status' => 'new',
                'shipping_address' => $shippingData['shipping_address'],
                'phone' => $shippingData['phone'],
                'notes' => $shippingData['notes'] ?? null
            ]);

            // Create order items
            $this->orderRepository->createOrderItems($order, $orderItems);

            // Dispatch event
            event(new OrderCreated($order));

            return $order;
        });
    }

    public function updateOrderStatus(Order $order, string $newStatus, User $user): bool
    {
        Gate::authorize('update', $order);

        $validTransitions = [
            'new' => ['accepted', 'canceled'],
            'accepted' => ['dispatched', 'canceled'],
            'dispatched' => ['delivered'],
            'delivered' => [],
            'canceled' => []
        ];

        if (!isset($validTransitions[$order->status]) || !in_array($newStatus, $validTransitions[$order->status])) {
            throw new InvalidStatusTransitionException("Invalid status transition from {$order->status} to {$newStatus}");
        }

        $updated = $this->orderRepository->updateStatus($order, $newStatus);

        if ($updated) {
            // PERBAIKI BARIS INI - ganti dari OrderStatusUpdated ke OrderStatusUpdate
            event(new OrderStatusUpdate($order, $newStatus));  // ✅ BENAR
        }

        return $updated;
    }

    public function getOrderStatistics()
    {
        return $this->orderRepository->getOrderStatistics();
    }

    public function getSellerOrderStatistics(User $seller)
    {
        return $this->orderRepository->getSellerOrderStatistics($seller->id);
    }
}
