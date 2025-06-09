<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/search', [ProductController::class, 'search']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

    // Stores
    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{store}', [StoreController::class, 'show']);
    Route::get('/stores/{store}/products', [StoreController::class, 'products']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User profile
        Route::get('/user', [UserController::class, 'profile']);
        Route::put('/user', [UserController::class, 'updateProfile']);
        Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
        Route::get('/user/orders', [UserController::class, 'orders']);

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

        Route::get('/cart/count', [CartController::class, 'count']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::post('/cart/update', [CartController::class, 'update']);
        Route::post('/cart/remove', [CartController::class, 'remove']);
        Route::post('/cart/remove-multiple', [CartController::class, 'removeMultiple']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // Seller routes
        Route::middleware('role:seller')->group(function () {
            // Store management
            Route::post('/stores', [StoreController::class, 'store']);
            Route::put('/stores/{store}', [StoreController::class, 'update']);
            Route::get('/seller/store', [StoreController::class, 'myStore']);

            // Product management
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{product}', [ProductController::class, 'update']);
            Route::delete('/products/{product}', [ProductController::class, 'destroy']);
            Route::patch('/products/{product}/status', [ProductController::class, 'updateStatus']);
            Route::get('/seller/products', [ProductController::class, 'myProducts']);

            // Order management
            Route::get('/seller/orders', [OrderController::class, 'sellerOrders']);

            // Analytics
            Route::get('/seller/analytics/sales', [OrderController::class, 'salesAnalytics']);
            Route::get('/seller/analytics/products', [ProductController::class, 'productAnalytics']);
        });

        // Admin routes
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // Users
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{user}', [UserController::class, 'show']);
            Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

            // Stores
            Route::get('/stores/pending', [StoreController::class, 'pendingStores']);
            Route::patch('/stores/{store}/approve', [StoreController::class, 'approveStore']);
            Route::patch('/stores/{store}/reject', [StoreController::class, 'rejectStore']);

            // Orders
            Route::get('/orders', [OrderController::class, 'adminOrders']);

            // Categories
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{category}', [CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

            // Reports
            Route::get('/reports/sales', [OrderController::class, 'salesReport']);
            Route::get('/reports/users', [UserController::class, 'userReport']);
            Route::get('/reports/products', [ProductController::class, 'productReport']);
        });
    });
});
