<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their orders
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Buyer can view their own orders
        if ($user->id === $order->buyer_id) {
            return true;
        }

        // Admin can view all orders
        if ($user->role === 'admin') {
            return true;
        }

        // Seller can view orders containing their products
        if ($user->role === 'seller') {
            return $order->items()->whereHas('product', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'buyer' && $user->status === 'active';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        // Buyer can cancel their own orders if they're new
        if ($user->id === $order->buyer_id && $order->status === 'new') {
            return true;
        }

        // Admin can update any order
        if ($user->role === 'admin') {
            return true;
        }

        // Seller can update orders containing their products
        if ($user->role === 'seller') {
            return $order->items()->whereHas('product', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }
}
