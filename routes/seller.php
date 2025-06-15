<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\StoreController;
use App\Http\Controllers\Seller\ReportController;
use App\Http\Controllers\Seller\ProfileController;

/*
|--------------------------------------------------------------------------
| Seller Routes
|--------------------------------------------------------------------------
|
| Here is where you can register seller routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Store Setup & Management
    Route::prefix('store')->name('store.')->group(function () {
        // Store setup for new sellers
        Route::middleware('seller.nostore')->group(function () {
            Route::get('/setup', [StoreController::class, 'setup'])->name('setup');
            Route::post('/setup', [StoreController::class, 'storeSetup'])->name('setup.store');
        });

        // Store management for approved sellers
        Route::middleware('store.owner')->group(function () {
            Route::get('/', [StoreController::class, 'show'])->name('show');
            Route::get('/edit', [StoreController::class, 'edit'])->name('edit');
            Route::put('/', [StoreController::class, 'update'])->name('update');
        });

        // Store status for pending sellers
        Route::get('/status', [StoreController::class, 'status'])->name('status');
    });

    // Product Management (requires approved store)
    Route::middleware('store.owner')->prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::patch('/{product}/status', [ProductController::class, 'updateStatus'])->name('status.update');
        Route::post('/{product}/images', [ProductController::class, 'addImages'])->name('images.add');
        Route::delete('/images/{image}', [ProductController::class, 'removeImage'])->name('images.remove');
    });

    // Order Management (requires approved store)
    // Order Management (requires approved store)
    Route::middleware('store.owner')->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('status.update');

        // PERBAIKI INI - SESUAIKAN DENGAN METHOD DI CONTROLLER
        Route::get('/new', [OrderController::class, 'new'])->name('new'); // UBAH dari 'newOrders' ke 'new'
        Route::get('/processing', [OrderController::class, 'processing'])->name('processing'); // UBAH dari 'processingOrders' ke 'processing'
        Route::get('/completed', [OrderController::class, 'completed'])->name('completed'); // UBAH dari 'completedOrders' ke 'completed'
        Route::get('/canceled', [OrderController::class, 'canceled'])->name('canceled'); // UBAH dari 'canceledOrders' ke 'canceled'
    });

    // Reports & Analytics (requires approved store)
    Route::middleware('store.owner')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('/products', [ReportController::class, 'productsReport'])->name('products');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
    });

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });
});
