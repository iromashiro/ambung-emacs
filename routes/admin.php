<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StoreApprovalController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/status', [UserController::class, 'updateStatus'])->name('status.update');
    });

    // Store Approval Management
    Route::prefix('stores')->name('stores.')->group(function () {
        Route::get('/pending', [StoreApprovalController::class, 'index'])->name('pending');
        Route::get('/{store}', [StoreApprovalController::class, 'show'])->name('show');
        Route::post('/{store}/approve', [StoreApprovalController::class, 'approve'])->name('approve');
        Route::post('/{store}/reject', [StoreApprovalController::class, 'reject'])->name('reject');
        Route::get('/', [StoreApprovalController::class, 'all'])->name('index');
    });

    // Order Management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('status.update');
    });

    // Transaction Management
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/analytics', [TransactionController::class, 'analytics'])->name('analytics');
        Route::get('/{id}', [TransactionController::class, 'show'])->name('show');
        Route::get('/export/csv', [TransactionController::class, 'export'])->name('export');
    });

    // Category Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Product Management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::patch('/{product}/status', [ProductController::class, 'updateStatus'])->name('status.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('/products', [ReportController::class, 'productsReport'])->name('products');
        Route::get('/stores', [ReportController::class, 'storesReport'])->name('stores');
        Route::get('/users', [ReportController::class, 'usersReport'])->name('users');
        Route::get('/seller/{sellerId}', [ReportController::class, 'sellerPerformance'])->name('seller');

        // Downloads
        Route::get('/download/orders', [ReportController::class, 'downloadOrdersCsv'])->name('download.orders');
        Route::get('/download/products', [ReportController::class, 'downloadProductsCsv'])->name('download.products');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/', [SettingController::class, 'update'])->name('update');
        Route::get('/system', [SettingController::class, 'system'])->name('system');
        Route::get('/backup', [SettingController::class, 'backup'])->name('backup');
        Route::post('/backup/create', [SettingController::class, 'createBackup'])->name('backup.create');
        Route::get('/logs', [SettingController::class, 'logs'])->name('logs');
    });
});
