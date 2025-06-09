<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrderStatusChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    protected $order;

    /**
     * The old status.
     *
     * @var string
     */
    protected $oldStatus;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, string $oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        // Send notification about status change
        $notificationService->notifyOrderStatusChanged($this->order, $this->oldStatus);
        
        // Log activity
        activity()
            ->performedOn($this->order)
            ->withProperties([
                'old_status' => $this->oldStatus,
                'new_status' => $this->order->status_enum,
            ])
            ->log('order_status_changed');
    }
}