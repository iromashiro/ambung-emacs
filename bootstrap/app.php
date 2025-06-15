<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'store.owner' => \App\Http\Middleware\StoreOwnerMiddleware::class,
            'store.active' => \App\Http\Middleware\CheckStoreStatus::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'seller.nostore' => \App\Http\Middleware\CheckSellerNoStore::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Register custom exception handlers
        $exceptions->reportable(function (\App\Exceptions\BusinessException $e) {
            // Don't report business exceptions to error tracking
            return false;
        });

        $exceptions->render(function (\App\Exceptions\BusinessException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], $e->getCode() ?: 400);
            }

            return back()->with('error', $e->getMessage());
        });
    })
    ->create();
