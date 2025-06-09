<?php

namespace App\Observers;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use Spatie\Activitylog\Facades\Activity as LogActivity;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        try {
            LogActivity::performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties([
                    'order_id' => $order->id,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                ])
                ->log('order_created');
        } catch (\Exception $e) {
            // Silently fail if no authenticated user
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if status has changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');

            // Dispatch event
            event(new OrderStatusChanged($order, $oldStatus, auth()->user()));

            try {
                LogActivity::performedOn($order)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'order_id' => $order->id,
                        'old_status' => $oldStatus,
                        'new_status' => $order->status,
                    ])
                    ->log('order_status_updated');
            } catch (\Exception $e) {
                // Silently fail if no authenticated user
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        try {
            LogActivity::performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties([
                    'order_id' => $order->id,
                ])
                ->log('order_deleted');
        } catch (\Exception $e) {
            // Silently fail if no authenticated user
        }
    }
}
