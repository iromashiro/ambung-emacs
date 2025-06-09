<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\NewOrderReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;

class NotifyStoreOwner implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public function handle(OrderCreated $event)
    {
        // Group order items by seller
        $sellerItems = collect($event->order->items)->groupBy(function ($item) {
            return $item->product->seller_id;
        });

        // Notify each seller about their items in the order
        $sellerItems->each(function ($items, $sellerId) use ($event) {
            $seller = \App\Models\User::find($sellerId);

            if ($seller) {
                $seller->notify(new NewOrderReceived($event->order, $items));
            }
        });
    }

    public function failed(OrderCreated $event, $exception)
    {
        \Log::error('Failed to notify store owner', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}
