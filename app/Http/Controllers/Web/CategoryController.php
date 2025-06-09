<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;
    protected $productService;
    
    public function __construct(
        CategoryService $categoryService,
        ProductService $productService
    ) {
        $this->categoryService = $categoryService;
        $this->productService = $productService;
    }
    
    /**
     * Display a listing of all categories
     */
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        
        return view('web.categories.index', compact('categories'));
    }
    
    /**
     * Display the specified category with its products
     */
    public function show($slug, Request $request)
    {
        // Find category by slug
        $category = $this->categoryService->getCategoryBySlug($slug);
        
        if (!$category) {
            abort(404);
        }
        
        // Get filter parameters
        $filters = $request->only([
            'min_price',
            'max_price',
            'sort',
            'direction'
        ]);
        
        // Add category filter
        $filters['category_id'] = $category->id;
        
        // Get products in this category
        $products = $this->productService->getActiveProducts($filters);
        
        // Get price range for filter
        $priceRange = $this->productService->getProductPriceRange(['category_id' => $category->id]);
        
        // Get subcategories if any
        $subcategories = $this->categoryService->getSubcategories($category->id);
        
        return view('web.categories.show', compact(
            'category',
            'products',
            'priceRange',
            'subcategories',
            'filters'
        ));
    }
}