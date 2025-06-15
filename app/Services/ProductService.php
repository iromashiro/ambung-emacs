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

        return DB::transaction(function () use ($data, $seller) {
            $productData = [
                'seller_id' => $seller->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . Str::random(6), // Manual slug generation
                'description' => $data['description'],
                'price' => $data['price'],
                'stock' => $data['stock'] ?? 0,
                'category_id' => $data['category_id'],
                'status' => 'active',
                'is_featured' => $data['is_featured'] ?? false
            ];

            \Log::info('Creating product with clean data:', $productData);

            // Create product using repository
            $product = $this->productRepository->create($productData);

            \Log::info('Product created successfully:', [
                'id' => $product->id,
                'name' => $product->name,
                'seller_id' => $product->seller_id
            ]);

            // Handle images if provided
            if (isset($data['images']) && is_array($data['images'])) {
                $this->handleProductImages($product, $data['images']);
            }

            return $product;
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        Gate::authorize('update', $product);

        return DB::transaction(function () use ($product, $data) {
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'category_id' => $data['category_id'],
                'is_featured' => $data['is_featured'] ?? $product->is_featured
            ];

            // Update slug if name changed
            if ($product->name !== $data['name']) {
                $updateData['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
            }

            $this->productRepository->update($product, $updateData);

            if (isset($data['images']) && is_array($data['images'])) {
                $this->handleProductImages($product, $data['images']);
            }

            return $product->fresh();
        });
    }

    public function deleteProduct(Product $product): bool
    {
        Gate::authorize('delete', $product);

        return DB::transaction(function () use ($product) {
            // Delete product images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
                Storage::disk('public')->delete('thumbs/' . basename($image->path));
            }

            return $this->productRepository->delete($product);
        });
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
