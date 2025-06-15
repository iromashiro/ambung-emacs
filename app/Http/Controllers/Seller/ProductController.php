<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;

        $this->middleware(['auth', 'verified']);
        $this->middleware('role:seller');
        $this->middleware('store.owner'); // Products require active store
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        $perPage = $request->input('per_page', 10);
        $products = $this->productService->getProductsByStore($store, $perPage);

        return view('seller.products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        if (!$store->isActive()) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your store is not active. Please wait for admin approval.');
        }

        $categories = app('App\Models\Category')->all();

        return view('seller.products.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(CreateProductRequest $request): RedirectResponse
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        if (!$store->isActive()) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your store is not active. Please wait for admin approval.');
        }

        try {
            $product = $this->productService->createProduct(
                $store,
                $request->validated(),
                $request->file('image')
            );

            return redirect()->route('seller.products.index')
                ->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            return redirect()->route('seller.products.create')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified product.
     */
    public function show(string $id): View
    {
        $product = $this->productService->getProductById($id);

        // Check if user has permission to view this product
        if (auth()->user()->store->id !== $product->store_id) {
            abort(403, 'You do not have permission to view this product');
        }

        return view('seller.products.show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(string $id): View
    {
        $product = $this->productService->getProductById($id);

        // Check if user has permission to edit this product
        if (auth()->user()->store->id !== $product->store_id) {
            abort(403, 'You do not have permission to edit this product');
        }

        $categories = app('App\Models\Category')->all();

        return view('seller.products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, string $id): RedirectResponse
    {
        $product = $this->productService->getProductById($id);

        // Check if user has permission to update this product
        if (auth()->user()->store->id !== $product->store_id) {
            abort(403, 'You do not have permission to update this product');
        }

        try {
            $product = $this->productService->updateProduct(
                $product,
                $request->validated(),
                $request->file('image')
            );

            return redirect()->route('seller.products.show', $product->id)
                ->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('seller.products.edit', $product->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(string $id): RedirectResponse
    {
        $product = $this->productService->getProductById($id);

        // Check if user has permission to delete this product
        if (auth()->user()->store->id !== $product->store_id) {
            abort(403, 'You do not have permission to delete this product');
        }

        try {
            $this->productService->deleteProduct($product);

            return redirect()->route('seller.products.index')
                ->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('seller.products.index')
                ->with('error', $e->getMessage());
        }
    }
}
