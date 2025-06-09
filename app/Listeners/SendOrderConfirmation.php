<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public function handle(OrderCreated $event)
    {
        $event->order->buyer->notify(new OrderConfirmation($event->order));
    }

    public function failed(OrderCreated $event, $exception)
    {
        \Log::error('Failed to send order confirmation', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}
