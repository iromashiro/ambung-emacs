<?php

namespace App\Notifications;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreApprovalRequested extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The store instance.
     *
     * @var \App\Models\Store
     */
    protected $store;

    /**
     * Create a new notification instance.
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
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
        $url = route('admin.stores.approval.show', $this->store->id);
        
        return (new MailMessage)
            ->subject('New Store Approval Request - ' . $this->store->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new store is requesting approval:')
            ->line('Store Name: ' . $this->store->name)
            ->line('Owner: ' . $this->store->user->name)
            ->line('Email: ' . $this->store->user->email)
            ->line('Phone: ' . $this->store->phone)
            ->action('Review Store', $url)
            ->line('Thank you for your attention!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Store Approval Request',
            'message' => 'Store "' . $this->store->name . '" is requesting approval',
            'url' => route('admin.stores.approval.show', $this->store->id),
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'owner_name' => $this->store->user->name,
        ];
    }
}