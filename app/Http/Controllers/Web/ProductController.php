<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products with filtering options
     */
    public function index(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:newest,price_low,price_high,popularity',
            'view' => 'nullable|in:grid,list',
            'per_page' => 'nullable|integer|min:6|max:60'
        ]);

        // Build filters array that matches ProductService expectations
        $filters = array_filter([
            'search' => $validated['search'] ?? null,
            'category_ids' => $validated['categories'] ?? null, // Note: category_ids not category_id
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'sort' => $validated['sort'] ?? 'newest',
        ]);

        // Get products with pagination
        $perPage = $validated['per_page'] ?? 12;
        $products = $this->productService->getActiveProducts($filters);

        // Convert to paginated if not already
        if (!$products instanceof LengthAwarePaginator) {
            $products = $this->paginateCollection($products, $perPage, $request);
        }

        // Get categories with product counts
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();

        // Get price range
        $priceRange = $this->productService->getProductPriceRange();

        return view('web.products.index', compact(
            'products',
            'categories',
            'priceRange'
        ));
    }

    /**
     * Display the specified product
     */
    public function show($slug)
    {
        // Find product by slug
        $product = $this->productService->getProductBySlug($slug);

        if (!$product || $product->status !== 'active') {
            abort(404, 'Product not found');
        }

        // Get related products (same category)
        $relatedProducts = $this->getRelatedProducts($product, 4);

        return view('web.products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Search for products
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|max:255',
            'sort' => 'nullable|in:newest,price_low,price_high,popularity',
            'per_page' => 'nullable|integer|min:6|max:60'
        ]);

        $filters = [
            'search' => $validated['search'],
            'sort' => $validated['sort'] ?? 'newest',
        ];

        $perPage = $validated['per_page'] ?? 12;
        $products = $this->productService->getActiveProducts($filters);

        if (!$products instanceof LengthAwarePaginator) {
            $products = $this->paginateCollection($products, $perPage, $request);
        }

        $categories = Category::withCount(['products' => function ($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();

        $priceRange = $this->productService->getProductPriceRange();
        $query = $validated['search'];

        return view('web.products.search', compact(
            'products',
            'categories',
            'priceRange',
            'query'
        ));
    }

    /**
     * Get products by category
     */
    public function byCategory($categorySlug, Request $request)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:newest,price_low,price_high,popularity',
            'per_page' => 'nullable|integer|min:6|max:60'
        ]);

        $filters = array_filter([
            'category_ids' => [$category->id],
            'search' => $validated['search'] ?? null,
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'sort' => $validated['sort'] ?? 'newest',
        ]);

        $perPage = $validated['per_page'] ?? 12;
        $products = $this->productService->getActiveProducts($filters);

        if (!$products instanceof LengthAwarePaginator) {
            $products = $this->paginateCollection($products, $perPage, $request);
        }

        $categories = Category::withCount(['products' => function ($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();

        $priceRange = $this->productService->getProductPriceRange();

        return view('web.products.category', compact(
            'products',
            'categories',
            'priceRange',
            'category'
        ));
    }

    /**
     * Get related products
     */
    private function getRelatedProducts(Product $product, int $limit = 4)
    {
        return Product::where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Paginate a collection manually
     */
    private function paginateCollection($collection, $perPage, $request)
    {
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $items = $collection->slice($offset, $perPage);

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
    }
}
