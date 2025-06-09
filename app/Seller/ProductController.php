<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first.');
        }

        $products = $this->productService->getProductsByStore($store);

        return view('seller.products.index', compact('products', 'store'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first.');
        }

        if (!$store->isActive()) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your store is not active yet. Please wait for admin approval.');
        }

        $categories = $this->categoryRepository->findAll();

        return view('seller.products.create', compact('categories', 'store'));
    }

    public function store(CreateProductRequest $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first.');
        }

        if (!$store->isActive()) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your store is not active yet. Please wait for admin approval.');
        }

        $data = $request->validated();
        $image = $request->file('image');

        try {
            $this->productService->createProduct($store, $data, $image);

            return redirect()->route('seller.products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }

    public function destroy(string $id, Request $request)
    {
        $user = $request->user();
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return redirect()->route('seller.products.index')
                ->with('error', 'Product not found.');
        }

        // FIXED: Using policy authorization instead of manual check
        try {
            $this->authorize('delete', $product);

            $this->productService->deleteProduct($product);

            return redirect()->route('seller.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('seller.products.index')
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }

    // Similarly update other methods to use policy authorization
    public function edit(string $id, Request $request)
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return redirect()->route('seller.products.index')
                ->with('error', 'Product not found.');
        }

        // FIXED: Using policy authorization
        $this->authorize('update', $product);

        $categories = $this->categoryRepository->findAll();

        return view('seller.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return redirect()->route('seller.products.index')
                ->with('error', 'Product not found.');
        }

        // FIXED: Using policy authorization
        $this->authorize('update', $product);

        $data = $request->validated();
        $image = $request->file('image');

        try {
            $this->productService->updateProduct($product, $data, $image);

            return redirect()->route('seller.products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }
}
