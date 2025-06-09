<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seller_id',
        'name',
        'slug',
        'description',
        'address',
        'phone',
        'logo',
        'status'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name when creating a store
        static::creating(function ($store) {
            $store->slug = $store->generateUniqueSlug($store->name);
        });

        // Update slug when name changes
        static::updating(function ($store) {
            if ($store->isDirty('name')) {
                $store->slug = $store->generateUniqueSlug($store->name);
            }
        });
    }

    /**
     * Generate a unique slug from the given name.
     *
     * @param string $name
     * @return string
     */
    protected function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        // Check if the slug already exists
        while (static::where('slug', $slug)
            ->where('id', '!=', $this->id ?? 0)
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Get the seller that owns the store.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the products for the store.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id', 'seller_id');
    }

    /**
     * Scope a query to only include active stores.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pending stores.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to search by name or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Check if the store is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the store is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the logo URL attribute.
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }

        return asset('images/default-store.png');
    }

    /**
     * Get the thumbnail URL attribute.
     */
    public function getThumbUrlAttribute()
    {
        if ($this->logo) {
            $path = pathinfo($this->logo);
            return asset('storage/stores/thumbs/' . $path['basename']);
        }

        return asset('images/default-store-thumb.png');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
