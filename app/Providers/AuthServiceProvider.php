<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\StorePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Store::class => StorePolicy::class,
        Order::class => OrderPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register policies
        $this->registerPolicies();

        // Define gates
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('seller-access', function (User $user) {
            return $user->isSeller();
        });

        Gate::define('buyer-access', function (User $user) {
            return $user->isBuyer();
        });

        Gate::define('manage-store', function (User $user, Store $store) {
            return $user->isAdmin() || ($user->isSeller() && $user->id === $store->user_id);
        });

        Gate::define('manage-product', function (User $user, Product $product) {
            return $user->isAdmin() || ($user->isSeller() && $user->store && $product->store_id === $user->store->id);
        });

        Gate::define('manage-order', function (User $user, Order $order) {
            return $user->isAdmin() || ($user->isSeller() && $user->store && $order->store_id === $user->store->id);
        });

        Gate::define('view-order', function (User $user, Order $order) {
            return $user->isAdmin() ||
                ($user->isBuyer() && $order->buyer_id === $user->id) ||
                ($user->isSeller() && $user->store && $order->store_id === $user->store->id);
        });
    }
}
