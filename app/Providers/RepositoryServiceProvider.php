<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Repository bindings
        $this->app->bind(
            \App\Repositories\Interfaces\ProductRepositoryInterface::class,
            \App\Repositories\Eloquent\ProductRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\OrderRepositoryInterface::class,
            \App\Repositories\Eloquent\OrderRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\StoreRepositoryInterface::class,
            \App\Repositories\Eloquent\StoreRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CategoryRepositoryInterface::class,
            \App\Repositories\Eloquent\CategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CartRepositoryInterface::class,
            \App\Repositories\Eloquent\CartRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\NotificationRepositoryInterface::class,
            \App\Repositories\Eloquent\NotificationRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\AddressRepositoryInterface::class,
            \App\Repositories\Eloquent\AddressRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
