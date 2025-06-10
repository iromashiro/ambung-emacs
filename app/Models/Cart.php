<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
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

    public function getSubtotalAttribute(): int
    {
        return $this->product->price_int * $this->quantity;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->getSubtotalAttribute() / 100, 0, ',', '.');
    }

    public static function getBySession(string $sessionId)
    {
        return self::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('product.store')
            ->get();
    }

    public static function getByUser(string $userId)
    {
        return self::where('user_id', $userId)->with('product.store')->get();
    }

    /**
     * Merge guest cart items with user cart
     * FIXED: Parameter order consistency
     */
    public static function mergeGuestCart(string $sessionId, string $userId)
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
                $item->session_id = null; // Clear session ID
                $item->save();
            }
        }
    }

    public function getTotalAttribute()
    {
        return $this->product->price * $this->quantity;
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
