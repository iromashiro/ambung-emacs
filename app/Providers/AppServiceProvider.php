<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\StoreRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap pagination
        Paginator::useBootstrap();

        // Share categories with all views
        View::composer('*', function ($view) {
            $categories = Category::active()
                ->withCount('products')
                ->orderBy('name')
                ->limit(10)
                ->get();

            $view->with('categories', $categories);
        });

        // Share store data with seller layouts
        View::composer(['layouts.seller', 'seller.*'], function ($view) {
            if (auth()->check() && auth()->user()->role === 'seller') {
                $store = auth()->user()->store;
                $view->with('store', $store);
            }
        });

        // Share notification data with admin layouts
        View::composer(['layouts.admin', 'admin.*'], function ($view) {
            if (auth()->check() && auth()->user()->role === 'admin') {
                $notifications = auth()->user()->notifications()
                    ->latest()
                    ->limit(5)
                    ->get();

                $unreadNotifications = auth()->user()->unreadNotifications()->count();

                $view->with([
                    'notifications' => $notifications,
                    'unreadNotifications' => $unreadNotifications
                ]);
            }
        });

        // Custom Blade directives
        Blade::directive('currency', function ($expression) {
            return "<?php echo 'Rp ' . number_format($expression, 0, ',', '.'); ?>";
        });

        Blade::directive('status', function ($expression) {
            return "<?php echo ucfirst(str_replace('_', ' ', $expression)); ?>";
        });

        // Set default string length for schema
        Schema::defaultStringLength(191);

        // Register observers only if not in console or if in production
        if (!$this->app->runningInConsole() || $this->app->environment('production')) {
            Order::observe(OrderObserver::class);
            Product::observe(ProductObserver::class);
            User::observe(UserObserver::class);
        }
    }
}
