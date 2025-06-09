<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the notification system.
    |
    */

    // Types of notifications
    'types' => [
        'order_status_changed' => [
            'display_name' => 'Order Status Update',
            'icon' => 'fa-shopping-cart',
        ],
        'new_order' => [
            'display_name' => 'New Order',
            'icon' => 'fa-shopping-bag',
        ],
        'store_approval_requested' => [
            'display_name' => 'Store Approval Request',
            'icon' => 'fa-store',
        ],
        'store_approval_status' => [
            'display_name' => 'Store Approval Status',
            'icon' => 'fa-check-circle',
        ],
    ],

    // Notification channels
    'channels' => [
        'mail' => [
            'enabled' => true,
        ],
        'database' => [
            'enabled' => true,
        ],
    ],

    // Notification retention period (in days)
    'retention_period' => 30,
];