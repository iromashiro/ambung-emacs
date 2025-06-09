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
        return self::where('session_id', $sessionId)->with('product.store')->get();
    }

    public static function getByUser(string $userId)
    {
        return self::where('user_id', $userId)->with('product.store')->get();
    }

    public static function mergeGuestCart(string $sessionId, string $userId)
    {
        $guestItems = self::where('session_id', $sessionId)->get();

        foreach ($guestItems as $item) {
            $existingItem = self::where('user_id', $userId)
                ->where('product_id', $item->product_id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $item->quantity;
                $existingItem->save();
                $item->delete();
            } else {
                $item->user_id = $userId;
                $item->save();
            }
        }
    }

    public function getTotalAttribute()
    {
        return $this->product->price * $this->quantity;
    }
}
