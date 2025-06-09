<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * The old status.
     *
     * @var string
     */
    public $oldStatus;

    /**
     * The user who changed the status.
     *
     * @var \App\Models\User|null
     */
    public $actor;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, string $oldStatus, ?User $actor = null)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->actor = $actor;
    }
}