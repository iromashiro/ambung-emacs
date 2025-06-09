<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        \App\Events\OrderCreated::class => [
            \App\Listeners\SendOrderConfirmation::class,
            \App\Listeners\NotifyStoreOwner::class,
            \App\Listeners\UpdateInventoryLog::class,
        ],

        \App\Events\OrderStatusUpdated::class => [
            \App\Listeners\SendOrderStatusUpdateEmail::class,
            \App\Listeners\LogOrderStatusChange::class,
        ],

        \App\Events\StoreApprovalRequested::class => [
            \App\Listeners\NotifyAdminOfStoreApprovalRequest::class,
        ],

        \App\Events\StoreApproved::class => [
            \App\Listeners\SendStoreApprovalEmail::class,
            \App\Listeners\CreateSellerDashboard::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register model observers
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
