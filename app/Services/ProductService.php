<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;   // TAMBAH INI!

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Get products by store - FIXED METHOD
     */
    public function getProductsByStore($store, int $perPage = 10)
    {
        // FIXED: Use auth()->user()->id directly as seller_id
        $sellerId = auth()->user()->id;

        return Product::where('seller_id', $sellerId)
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active products with filters and pagination
     */
    public function getActiveProducts(array $filters = [], int $perPage = 12)
    {
        $query = Product::query()
            ->where('status', 'active')
            ->with(['seller.store', 'category', 'images']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $query->whereIn('category_id', $filters['category_ids']);
        } elseif (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Apply price range filters
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply sorting
        $this->applySorting($query, $filters['sort'] ?? 'newest');

        // Return paginated results
        return $query->paginate($perPage);
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, string $sort)
    {
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popularity':
                $query->orderBy('views_count', 'desc')
                    ->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    public function getFeaturedProducts(int $limit = 8)
    {
        return $this->productRepository->getFeaturedProducts($limit);
    }

    /**
     * Get products for a specific seller
     */
    public function getSellerProducts($user)
    {
        try {
            return \App\Models\Product::where('seller_id', $user->id)
                ->with(['images', 'category'])
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error in getSellerProducts: ' . $e->getMessage());
            return collect();
        }
    }

    public function createProduct(array $data, User $seller): Product
    {
        Gate::authorize('create', Product::class);

        try {
            $productData = [
                'seller_id' => $seller->id,
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'stock' => $data['stock'] ?? 0,
                'category_id' => $data['category_id'],
                'status' => 'active',
                'is_featured' => $data['is_featured'] ?? false
            ];

            \Log::info('Creating product with validated data:', $productData);

            // CREATE LANGSUNG DENGAN ELOQUENT
            $product = Product::create($productData);

            \Log::info('Product created successfully:', [
                'id' => $product->id,
                'name' => $product->name,
                'seller_id' => $product->seller_id
            ]);

            // Handle images if provided
            if (isset($data['images']) && is_array($data['images'])) {
                \Log::info('Processing product images:', ['count' => count($data['images'])]);
                $this->handleProductImages($product, $data['images']);
            }

            // Clear relevant caches
            $this->clearProductRelatedCache();

            return $product;
        } catch (\Exception $e) {
            \Log::error('Error creating product:', [
                'error' => $e->getMessage(),
                'data' => $data,
                'seller_id' => $seller->id
            ]);
            throw $e;
        }
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function clearProductRelatedCache(): void
    {
        try {
            Cache::forget('products.featured');
            Cache::forget('categories.with.products');
            // Add other cache keys as needed
        } catch (\Exception $e) {
            \Log::warning('Failed to clear product cache:', ['error' => $e->getMessage()]);
            // Don't throw exception, just log warning
        }
    }

    public function updateProduct(Product $product, array $data): Product
    {
        Gate::authorize('update', $product);

        try {
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'category_id' => $data['category_id'],
                'status' => $data['status'] ?? $product->status, // TAMBAH STATUS
                'is_featured' => $data['is_featured'] ?? $product->is_featured
            ];

            \Log::info('Updating product with data:', [
                'product_id' => $product->id,
                'update_data' => $updateData
            ]);

            // Update slug if name changed
            if ($product->name !== $data['name']) {
                $updateData['slug'] = $this->generateUniqueSlug($data['name']);
            }

            // UPDATE LANGSUNG TANPA TRANSACTION DAN REPOSITORY
            $product->update($updateData);

            \Log::info('Product updated successfully:', [
                'id' => $product->id,
                'name' => $product->name
            ]);

            // Handle images if provided
            if (isset($data['images']) && is_array($data['images'])) {
                \Log::info('Processing updated product images:', ['count' => count($data['images'])]);
                $this->handleProductImages($product, $data['images']);
            }

            // Clear relevant caches
            $this->clearProductRelatedCache();

            // Return fresh model
            return $product->fresh();
        } catch (\Exception $e) {
            \Log::error('Error updating product:', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Product $product): bool
    {
        Gate::authorize('delete', $product);

        try {
            \Log::info('Deleting product (soft delete):', [
                'id' => $product->id,
                'name' => $product->name,
                'seller_id' => $product->seller_id
            ]);

            // CEK APAKAH PRODUK SUDAH PERNAH DIPESAN
            $hasOrders = \DB::table('order_items')
                ->where('product_id', $product->id)
                ->exists();

            if ($hasOrders) {
                \Log::info('Product has orders, using soft delete:', [
                    'product_id' => $product->id
                ]);

                // SOFT DELETE - Produk masih ada di database tapi hidden
                $result = $product->delete(); // Ini akan soft delete

                \Log::info('Product soft deleted successfully:', [
                    'product_id' => $product->id,
                    'deleted_at' => $product->deleted_at
                ]);
            } else {
                \Log::info('Product has no orders, using hard delete:', [
                    'product_id' => $product->id
                ]);

                // HARD DELETE - Hapus images dulu, lalu produk
                if ($product->images()->exists()) {
                    foreach ($product->images as $image) {
                        // Delete physical file
                        if (Storage::disk('public')->exists($image->path)) {
                            Storage::disk('public')->delete($image->path);
                        }
                        // Delete thumbnail
                        if (Storage::disk('public')->exists('thumbs/' . basename($image->path))) {
                            Storage::disk('public')->delete('thumbs/' . basename($image->path));
                        }
                        // Delete image record
                        $image->delete();
                    }
                }

                // Force delete (permanent)
                $result = $product->forceDelete();

                \Log::info('Product hard deleted successfully:', [
                    'product_id' => $product->id
                ]);
            }

            // Clear caches
            $this->clearProductRelatedCache();

            return $result;
        } catch (\Exception $e) {
            \Log::error('Error deleting product:', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function updateProductStatus(Product $product, string $status): bool
    {
        Gate::authorize('update', $product);

        if (!in_array($status, ['active', 'inactive'])) {
            throw new \InvalidArgumentException('Invalid product status');
        }

        return $this->productRepository->update($product, ['status' => $status]);
    }

    private function handleProductImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('products', $filename, 'public');

                // Create thumbnail
                $img = Image::make($image);
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $thumbPath = 'products/thumbs';
                if (!Storage::disk('public')->exists($thumbPath)) {
                    Storage::disk('public')->makeDirectory($thumbPath);
                }

                $img->save(storage_path('app/public/products/thumbs/' . $filename));

                // Save image record
                $product->images()->create(['path' => $path]);
            }
        }
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)
            ->where('status', 'active')
            ->with(['seller.store', 'category', 'images'])
            ->first();
    }

    public function getProductPriceRange(): array
    {
        $minPrice = Product::where('status', 'active')->min('price') ?? 0;
        $maxPrice = Product::where('status', 'active')->max('price') ?? 1000000;

        return [
            'min' => $minPrice,
            'max' => $maxPrice
        ];
    }

    /**
     * Search products with advanced criteria
     */
    public function searchProducts(array $criteria, int $perPage = 12)
    {
        return $this->getActiveProducts($criteria, $perPage);
    }

    /**
     * Get related products - FIXED VERSION
     */
    public function getRelatedProducts(Product $product, int $limit = 4)
    {
        return Product::where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['seller.store', 'images']) // Pastikan eager loading berjalan dengan benar
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
