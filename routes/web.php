<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StoreApprovalController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Buyer\CartController;
use App\Http\Controllers\Buyer\CheckoutController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\ProductController as BuyerProductController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\StoreController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
Route::get('/stores/{store}', [StoreController::class, 'show'])->name('stores.public.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Buyer routes
Route::middleware(['auth', 'role:buyer'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    Route::get('/orders', [BuyerOrderController::class, 'index'])->name('buyer.orders.index');
    Route::get('/orders/{order}', [BuyerOrderController::class, 'show'])->name('buyer.orders.show');
    Route::patch('/orders/{order}/confirm', [BuyerOrderController::class, 'confirmDelivery'])->name('buyer.orders.confirm');
});

// Seller routes
Route::middleware(['auth', 'role:seller'])->prefix('seller')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('seller.dashboard');

    Route::get('/store', [SellerStoreController::class, 'edit'])->name('seller.store.edit');
    Route::post('/store', [SellerStoreController::class, 'store'])->name('seller.store.store');
    Route::put('/store', [SellerStoreController::class, 'update'])->name('seller.store.update');

    Route::get('/products', [SellerProductController::class, 'index'])->name('seller.products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])->name('seller.products.create');
    Route::post('/products', [SellerProductController::class, 'store'])->name('seller.products.store');
    Route::get('/products/{product}/edit', [SellerProductController::class, 'edit'])->name('seller.products.edit');
    Route::put('/products/{product}', [SellerProductController::class, 'update'])->name('seller.products.update');
    Route::delete('/products/{product}', [SellerProductController::class, 'destroy'])->name('seller.products.destroy');

    Route::get('/orders', [SellerOrderController::class, 'index'])->name('seller.orders.index');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('seller.orders.show');
    Route::patch('/orders/{order}/status', [SellerOrderController::class, 'updateStatus'])->name('seller.orders.update-status');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('users', UserController::class, ['as' => 'admin']);

    Route::get('/store-approvals', [StoreApprovalController::class, 'index'])->name('admin.store-approvals.index');
    Route::patch('/store-approvals/{store}/approve', [StoreApprovalController::class, 'approve'])->name('admin.store-approvals.approve');
    Route::patch('/store-approvals/{store}/reject', [StoreApprovalController::class, 'reject'])->name('admin.store-approvals.reject');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
    Route::get('/transactions/{order}', [TransactionController::class, 'show'])->name('admin.transactions.show');

    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('admin.reports.sales');
    Route::get('/reports/users', [ReportController::class, 'users'])->name('admin.reports.users');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('admin.reports.products');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('admin.reports.export');

    // Category management
    Route::resource('categories', AdminCategoryController::class, ['as' => 'admin']);
});

include __DIR__ . '/auth.php';
