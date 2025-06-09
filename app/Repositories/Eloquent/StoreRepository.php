<?php

namespace App\Repositories\Eloquent;

use App\Models\Store;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository implements StoreRepositoryInterface
{
    public function findById(int $id): ?Store
    {
        return Store::with(['seller', 'products'])->find($id);
    }

    public function getActiveStores(array $filters = []): LengthAwarePaginator
    {
        $query = Store::with(['seller'])
            ->where('status', 'active');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->paginate(12);
    }

    public function getPendingStores(): Collection
    {
        return Store::with(['seller'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function create(array $data): Store
    {
        return Store::create($data);
    }

    public function update(Store $store, array $data): bool
    {
        return $store->update($data);
    }

    public function updateStatus(Store $store, string $status): bool
    {
        return $store->update(['status' => $status]);
    }

    public function findBySlug(string $slug): ?Store
    {
        return Store::with(['seller', 'seller.products' => function ($query) {
            $query->where('status', 'active')->where('stock', '>', 0);
        }])
            ->where('slug', $slug)
            ->first();
    }
}
