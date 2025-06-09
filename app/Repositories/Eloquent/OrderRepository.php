<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class OrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return Order::with(['buyer', 'items.product.seller', 'items.product.images'])->find($id);
    }

    public function getOrdersByBuyer(int $buyerId, array $filters = []): Collection
    {
        $query = Order::with(['items.product'])
            ->where('buyer_id', $buyerId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }

    public function getOrdersBySeller(int $sellerId, array $filters = []): Collection
    {
        $query = Order::with(['buyer', 'items.product'])
            ->whereHas('items.product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            });

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }

    public function getAllOrders(array $filters = []): LengthAwarePaginator
    {
        $query = Order::with(['buyer', 'items.product.seller']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->whereHas('buyer', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate(20);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function createOrderItems(Order $order, array $items): void
    {
        $order->items()->createMany($items);
    }

    public function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }

    public function getOrderStatistics(): array
    {
        return Cache::remember('order.statistics', 3600, function () {
            return [
                'total' => Order::count(),
                'completed' => Order::where('status', 'delivered')->count(),
                'pending' => Order::whereIn('status', ['new', 'accepted', 'dispatched'])->count(),
                'canceled' => Order::where('status', 'canceled')->count(),
                'revenue' => Order::where('status', 'delivered')->sum('total_amount')
            ];
        });
    }

    public function getSellerOrderStatistics(int $sellerId): array
    {
        $cacheKey = "order.statistics.seller.{$sellerId}";

        return Cache::remember($cacheKey, 3600, function () use ($sellerId) {
            $orders = Order::whereHas('items.product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })->get();

            $completed = $orders->where('status', 'delivered')->count();
            $pending = $orders->whereIn('status', ['new', 'accepted', 'dispatched'])->count();
            $canceled = $orders->where('status', 'canceled')->count();

            $revenue = 0;
            foreach ($orders->where('status', 'delivered') as $order) {
                foreach ($order->items as $item) {
                    if ($item->product->seller_id === $sellerId) {
                        $revenue += $item->price * $item->quantity;
                    }
                }
            }

            return [
                'total' => $orders->count(),
                'completed' => $completed,
                'pending' => $pending,
                'canceled' => $canceled,
                'revenue' => $revenue
            ];
        });
    }

    public function getRecentOrders(int $limit = 5): Collection
    {
        return Order::with(['buyer', 'items.product.seller'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getOrdersByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $query = Order::with(['buyer', 'items.product.seller'])
            ->whereBetween('created_at', [$start, $end]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['buyer_id'])) {
            $query->where('buyer_id', $filters['buyer_id']);
        }

        if (isset($filters['seller_id'])) {
            $query->whereHas('items.product', function ($q) use ($filters) {
                $q->where('seller_id', $filters['seller_id']);
            });
        }

        return $query->latest()->get();
    }
}
