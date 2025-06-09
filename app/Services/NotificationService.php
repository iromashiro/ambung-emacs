<?php

namespace App\Services;

use App\Jobs\SendOrderNotification;
use App\Jobs\SendStoreApprovalNotification;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Notifications\NewOrderReceived;
use App\Notifications\OrderStatusChanged as OrderStatusChangedNotification;
use App\Notifications\StoreApprovalRequested;

class NotificationService
{
    /**
     * Create a notification.
     */
    public function createNotification(User $user, string $type, array $data): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type_enum' => $type,
            'data_json' => $data,
        ]);
    }

    /**
     * Get unread notifications for user.
     */
    public function getUnreadNotifications(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->markAsRead();
        return $notification;
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Notify about new order.
     */
    public function notifyNewOrder(Order $order): void
    {
        $this->createNotification(
            $order->store->user,
            'new_order',
            [
                'title' => 'New Order Received',
                'message' => "You have received a new order #{$order->id}",
                'url' => route('seller.orders.show', $order->id),
                'order_id' => $order->id,
            ]
        );
        
        SendOrderNotification::dispatch(
            $order->store->user,
            new NewOrderReceived($order)
        );
    }

    /**
     * Notify about order status change.
     */
    public function notifyOrderStatusChanged(Order $order, string $oldStatus): void
    {
        $this->createNotification(
            $order->buyer,
            'order_status_changed',
            [
                'title' => 'Order Status Updated',
                'message' => "Your order #{$order->id} status has been updated to {$order->status_enum}",
                'url' => route('buyer.orders.show', $order->id),
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $order->status_enum,
            ]
        );
        
        SendOrderNotification::dispatch(
            $order->buyer,
            new OrderStatusChangedNotification($order, $oldStatus)
        );
    }

    /**
     * Notify about store approval request.
     */
    public function notifyStoreApprovalRequest(Store $store): void
    {
        $admins = User::where('role_enum', 'admin')->get();
        
        foreach ($admins as $admin) {
            $this->createNotification(
                $admin,
                'store_approval_requested',
                [
                    'title' => 'New Store Approval Request',
                    'message' => "Store '{$store->name}' is requesting approval",
                    'url' => route('admin.stores.approval.show', $store->id),
                    'store_id' => $store->id,
                ]
            );
        }
        
        SendStoreApprovalNotification::dispatch(
            $admins,
            new StoreApprovalRequested($store)
        );
    }

    /**
     * Notify about store approval status.
     */
    public function notifyStoreApprovalStatus(Store $store, string $status, ?string $reason = null): void
    {
        $statusText = $status === 'active' ? 'approved' : 'rejected';
        
        $this->createNotification(
            $store->user,
            'store_approval_status',
            [
                'title' => 'Store Approval Status',
                'message' => "Your store '{$store->name}' has been {$statusText}",
                'url' => route('seller.store.edit'),
                'store_id' => $store->id,
                'status' => $status,
                'reason' => $reason,
            ]
        );
    }
}