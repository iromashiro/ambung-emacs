<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request): View
    {
        $criteria = $request->only([
            'category_id',
            'store_id',
            'min_price',
            'max_price',
            'search',
            'sort_by',
            'sort_direction',
        ]);
        
        $perPage = $request->input('per_page', 12);
        
        $products = $this->productService->searchProducts($criteria, $perPage);
        $categories = app('App\Models\Category')->whereNull('parent_id')->with('children')->get();
        
        return view('buyer.products.index', [
            'products' => $products,
            'categories' => $categories,
            'criteria' => $criteria,
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(string $slug): View
    {
        $product = $this->productService->getProductBySlug($slug);
        $relatedProducts = $this->productService->searchProducts([
            'category_id' => $product->category_id,
        ], 4);
        
        return view('buyer.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Display products by category.
     */
    public function byCategory(string $slug, Request $request): View
    {
        $category = app('App\Models\Category')->where('slug', $slug)->firstOrFail();
        
        $criteria = $request->only([
            'min_price',
            'max_price',
            'search',
            'sort_by',
            'sort_direction',
        ]);
        
        $criteria['category_id'] = $category->id;
        
        $perPage = $request->input('per_page', 12);
        
        $products = $this->productService->searchProducts($criteria, $perPage);
        
        return view('buyer.products.category', [
            'category' => $category,
            'products' => $products,
            'criteria' => $criteria,
        ]);
    }

    /**
     * Display products by store.
     */
    public function byStore(string $slug, Request $request): View
    {
        $store = app('App\Services\StoreService')->getStoreBySlug($slug);
        
        $criteria = $request->only([
            'category_id',
            'min_price',
            'max_price',
            'search',
            'sort_by',
            'sort_direction',
        ]);
        
        $criteria['store_id'] = $store->id;
        
        $perPage = $request->input('per_page', 12);
        
        $products = $this->productService->searchProducts($criteria, $perPage);
        
        return view('buyer.products.store', [
            'store' => $store,
            'products' => $products,
            'criteria' => $criteria,
        ]);
    }
}