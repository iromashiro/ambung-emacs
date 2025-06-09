<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buyer_id',
        'total_amount',
        'status',
        'shipping_address',
        'phone',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2'
    ];

    /**
     * Get the buyer that owns the order.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by buyer.
     */
    public function scopeByBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    /**
     * Scope a query to filter by seller.
     */
    public function scopeBySeller($query, $sellerId)
    {
        return $query->whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        });
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if the order can be canceled.
     */
    public function canBeCanceled(): bool
    {
        return in_array($this->status, ['new', 'accepted']);
    }

    /**
     * Check if the order can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'new';
    }

    /**
     * Check if the order can be dispatched.
     */
    public function canBeDispatched(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if the order can be delivered.
     */
    public function canBeDelivered(): bool
    {
        return $this->status === 'dispatched';
    }

    /**
     * Get the sellers associated with this order.
     */
    public function getSellersAttribute()
    {
        return User::whereIn('id', $this->items->pluck('product.seller_id')->unique())->get();
    }
}
