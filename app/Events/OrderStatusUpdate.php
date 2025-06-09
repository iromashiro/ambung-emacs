<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $newStatus;
    public $oldStatus;

    public function __construct(Order $order, string $newStatus)
    {
        $this->order = $order;
        $this->newStatus = $newStatus;
        $this->oldStatus = $order->getOriginal('status');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('orders');
    }
}