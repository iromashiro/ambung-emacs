<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function getOrdersByBuyer(int $buyerId, array $filters = []): Collection;

    public function getOrdersBySeller(int $sellerId, array $filters = []): Collection;

    public function getAllOrders(array $filters = []): LengthAwarePaginator;

    public function create(array $data): Order;

    public function createOrderItems(Order $order, array $items): void;

    public function updateStatus(Order $order, string $status): bool;

    public function getOrderStatistics(): array;

    public function getSellerOrderStatistics(int $sellerId): array;

    public function getRecentOrders(int $limit = 5): Collection;

    public function getOrdersByDateRange(string $startDate, string $endDate, array $filters = []): Collection;
}
