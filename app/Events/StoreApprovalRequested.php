<?php

namespace App\Events;

use App\Models\Store;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreApprovalRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('stores');
    }
}