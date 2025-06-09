<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'path',
        'is_primary',
        'order'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'order' => 'integer'
    ];
    
    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the URL for the image.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
    
    /**
     * Get the thumbnail URL for the image.
     */
    public function getThumbnailUrlAttribute()
    {
        $pathInfo = pathinfo($this->path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['basename'];
        
        return asset('storage/' . $thumbnailPath);
    }
}