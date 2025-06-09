<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function getActiveProducts(array $filters = []): LengthAwarePaginator;

    public function getProductsBySeller(int $sellerId): Collection;

    public function getFeaturedProducts(int $limit = 8): Collection;

    public function create(array $data): Product;

    public function update(Product $product, array $data): bool;

    public function delete(Product $product): bool;

    public function updateStock(int $productId, int $quantity): bool;
}
