<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // TAMBAH INI!
use App\Models\OrderItem; // TAMBAH INI!

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'total_amount',
        'status',
        'shipping_address',
        'phone',
        'notes'
    ];

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
     * Get the user that owns the order (alias for buyer).
     * TAMBAH METHOD INI UNTUK MENGATASI ERROR!
     */
    public function user()
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
     * TAMBAH: Get calculated total from order items
     */
    public function getCalculatedTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * TAMBAH: Get total items count
     */
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * TAMBAH: Get formatted total
     */
    public function getFormattedTotalAttribute()
    {
        $total = $this->calculated_total ?: $this->total_amount;
        return 'Rp ' . number_format($total, 0, ',', '.');
    }

    // ... rest of existing methods tetap sama

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
