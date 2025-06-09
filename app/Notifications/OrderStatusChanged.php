<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

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
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusLabels = [
            'NEW' => 'New Order',
            'ACCEPTED' => 'Accepted',
            'DISPATCHED' => 'Dispatched',
            'DELIVERED' => 'Delivered',
            'CANCELED' => 'Canceled',
        ];
        
        $url = route('buyer.orders.show', $this->order->id);
        
        return (new MailMessage)
            ->subject('Order Status Updated - ' . $statusLabels[$this->order->status_enum])
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your order #' . $this->order->id . ' status has been updated.')
            ->line('Status: ' . $statusLabels[$this->oldStatus] . ' → ' . $statusLabels[$this->order->status_enum])
            ->action('View Order', $url)
            ->line('Thank you for shopping with Ambung Emac\'s!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'NEW' => 'New Order',
            'ACCEPTED' => 'Accepted',
            'DISPATCHED' => 'Dispatched',
            'DELIVERED' => 'Delivered',
            'CANCELED' => 'Canceled',
        ];
        
        return [
            'title' => 'Order Status Updated',
            'message' => 'Your order #' . $this->order->id . ' status has been updated to ' . $statusLabels[$this->order->status_enum],
            'url' => route('buyer.orders.show', $this->order->id),
            'order_id' => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->order->status_enum,
        ];
    }
}