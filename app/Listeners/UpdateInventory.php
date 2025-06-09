<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateInventory implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        // If order is canceled, restore inventory
        if ($event->order->status_enum === 'CANCELED' && 
            in_array($event->oldStatus, ['NEW', 'ACCEPTED', 'DISPATCHED'])) {
            
            foreach ($event->order->items as $item) {
                $item->product->increment('stock_int', $item->qty_int);
            }
        }
    }
}