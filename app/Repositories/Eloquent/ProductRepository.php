<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'seller', 'images'])->find($id);
    }

    public function getActiveProducts(array $filters = []): LengthAwarePaginator
    {
        $cacheKey = 'products.active.' . md5(json_encode($filters));

        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Product::with(['category', 'seller'])
                ->where('status', 'active')
                ->where('stock', '>', 0);

            if (isset($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            if (isset($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }

            if (isset($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }

            if (isset($filters['seller_id'])) {
                $query->where('seller_id', $filters['seller_id']);
            }

            return $query->latest()->paginate(12);
        });
    }

    public function getProductsBySeller(int $sellerId): Collection
    {
        return Product::where('seller_id', $sellerId)
            ->with(['category', 'images'])
            ->latest()
            ->get();
    }

    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Cache::remember('products.featured', 3600, function () use ($limit) {
            return Product::with(['category', 'seller', 'images'])
                ->where('status', 'active')
                ->where('stock', '>', 0)
                ->where('is_featured', true)
                ->latest()
                ->limit($limit)
                ->get();
        });
    }

    public function create(array $data): Product
    {
        $product = Product::create($data);
        $this->clearProductCache();
        return $product;
    }

    public function update(Product $product, array $data): bool
    {
        $result = $product->update($data);
        $this->clearProductCache();
        return $result;
    }

    public function delete(Product $product): bool
    {
        $result = $product->delete();
        $this->clearProductCache();
        return $result;
    }

    public function updateStock(int $productId, int $quantity): bool
    {
        $result = Product::where('id', $productId)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity) > 0;

        if ($result) {
            $this->clearProductCache();
        }

        return $result;
    }

    private function clearProductCache(): void
    {
        Cache::forget('products.featured');
        // Clear other product-related caches
        Cache::forget('categories.with.products');
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::with(['category', 'seller', 'images'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get related products
     *
     * @param int $productId
     * @param int $categoryId
     * @param int $limit
     * @return Collection
     */
    public function getRelatedProducts(int $productId, int $categoryId, int $limit = 4): Collection
    {
        return Product::with(['category', 'seller', 'images'])
            ->where('id', '!=', $productId)
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Get product price range
     *
     * @param array $filters
     * @return array
     */
    public function getProductPriceRange(array $filters = []): array
    {
        $query = Product::query()->where('status', 'active');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        $result = $query->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        return [
            'min' => $result ? (float)$result->min_price : 0,
            'max' => $result ? (float)$result->max_price : 1000
        ];
    }

    /**
     * Get store categories with product count
     *
     * @param int $sellerId
     * @return Collection
     */
    public function getStoreCategoriesWithProductCount(int $sellerId): Collection
    {
        return DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->where('products.seller_id', $sellerId)
            ->where('products.status', 'active')
            ->where('categories.is_active', true)
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->select('categories.id', 'categories.name', 'categories.slug', DB::raw('COUNT(products.id) as product_count'))
            ->get();
    }
}
