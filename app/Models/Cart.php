<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',  // Add this to fillable
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get subtotal for this cart item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->product->price * $this->quantity;
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->getSubtotalAttribute(), 0, ',', '.');
    }

    /**
     * Get total (alias for subtotal)
     */
    public function getTotalAttribute(): float
    {
        return $this->getSubtotalAttribute();
    }

    /**
     * Get cart items by session ID (for guest users)
     */
    public static function getBySession(string $sessionId)
    {
        return self::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with(['product.seller.store', 'product.images'])
            ->get();
    }

    /**
     * Get cart items by user ID (for logged in users)
     */
    public static function getByUser(int $userId)
    {
        return self::where('user_id', $userId)
            ->with(['product.seller.store', 'product.images'])
            ->get();
    }

    /**
     * Merge guest cart items with user cart after login
     */
    public static function mergeGuestCart(string $sessionId, int $userId)
    {
        $guestItems = self::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestItems as $item) {
            $existingItem = self::where('user_id', $userId)
                ->where('product_id', $item->product_id)
                ->first();

            if ($existingItem) {
                // Merge quantities with stock limit check
                $totalQuantity = $existingItem->quantity + $item->quantity;
                $maxStock = $item->product->stock ?? 999;

                $existingItem->quantity = min($totalQuantity, $maxStock);
                $existingItem->save();
                $item->delete();
            } else {
                // Transfer ownership to user
                $item->user_id = $userId;
                $item->session_id = null;
                $item->save();
            }
        }
    }

    /**
     * Scope for getting cart items by user or session
     */
    public function scopeForUserOrSession($query, $userId = null, $sessionId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        } elseif ($sessionId) {
            return $query->where('session_id', $sessionId)->whereNull('user_id');
        }

        return $query->whereRaw('1 = 0'); // Return empty result
    }
}
