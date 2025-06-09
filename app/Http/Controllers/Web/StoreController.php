<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\StoreService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $storeService;
    protected $productService;
    
    public function __construct(
        StoreService $storeService,
        ProductService $productService
    ) {
        $this->storeService = $storeService;
        $this->productService = $productService;
    }
    
    /**
     * Display a listing of active stores
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filters = [];
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        $stores = $this->storeService->getActiveStores($filters);
        
        return view('web.stores.index', compact('stores', 'search'));
    }
    
    /**
     * Display the specified store
     */
    public function show($slug)
    {
        // Find store by slug
        $store = $this->storeService->getStoreBySlug($slug);
        
        if (!$store || $store->status !== 'active') {
            abort(404);
        }
        
        // Get featured products from this store
        $featuredProducts = $this->productService->getActiveProducts([
            'seller_id' => $store->seller_id,
            'is_featured' => true,
            'limit' => 4
        ]);
        
        // Get latest products from this store
        $latestProducts = $this->productService->getActiveProducts([
            'seller_id' => $store->seller_id,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 8
        ]);
        
        // Get product categories from this store
        $categories = $this->productService->getStoreCategoriesWithProductCount($store->seller_id);
        
        return view('web.stores.show', compact(
            'store',
            'featuredProducts',
            'latestProducts',
            'categories'
        ));
    }
    
    /**
     * Display products from the specified store
     */
    public function products($slug, Request $request)
    {
        // Find store by slug
        $store = $this->storeService->getStoreBySlug($slug);
        
        if (!$store || $store->status !== 'active') {
            abort(404);
        }
        
        // Get filter parameters
        $filters = $request->only([
            'category_id',
            'min_price',
            'max_price',
            'sort',
            'direction'
        ]);
        
        // Add seller filter
        $filters['seller_id'] = $store->seller_id;
        
        // Get products from this store
        $products = $this->productService->getActiveProducts($filters);
        
        // Get product categories from this store
        $categories = $this->productService->getStoreCategoriesWithProductCount($store->seller_id);
        
        // Get price range for filter
        $priceRange = $this->productService->getProductPriceRange(['seller_id' => $store->seller_id]);
        
        return view('web.stores.products', compact(
            'store',
            'products',
            'categories',
            'priceRange',
            'filters'
        ));
    }
}