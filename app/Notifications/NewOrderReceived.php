<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
        $url = route('seller.orders.show', $this->order->id);
        
        $mailMessage = (new MailMessage)
            ->subject('New Order Received - #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new order from ' . $this->order->buyer->name . '.')
            ->line('Order ID: #' . $this->order->id)
            ->line('Total: Rp ' . number_format($this->order->total_price, 0, ',', '.'))
            ->line('Items:');
        
        foreach ($this->order->items as $item) {
            $mailMessage->line('- ' . $item->product->name . ' x ' . $item->qty_int . ' = Rp ' . number_format($item->subtotal, 0, ',', '.'));
        }
        
        $mailMessage->action('View Order', $url)
            ->line('Please process this order as soon as possible.');
        
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Order Received',
            'message' => 'You have received a new order #' . $this->order->id . ' from ' . $this->order->buyer->name,
            'url' => route('seller.orders.show', $this->order->id),
            'order_id' => $this->order->id,
            'buyer_name' => $this->order->buyer->name,
            'total' => $this->order->total_price,
        ];
    }
}