<?php

namespace App\Repositories\Interfaces;

use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StoreRepositoryInterface
{
    public function findById(int $id): ?Store;

    public function getActiveStores(array $filters = []): LengthAwarePaginator;

    public function getPendingStores(): Collection;

    public function create(array $data): Store;

    public function update(Store $store, array $data): bool;

    public function updateStatus(Store $store, string $status): bool;
}
