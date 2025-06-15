<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the user's wishlist
     */
    public function index()
    {
        $wishlistItems = auth()->user()->wishlist()
            ->with('product.category', 'product.images')
            ->get();
            
        return view('web.wishlist.index', compact('wishlistItems'));
    }
    
    /**
     * Add a product to the wishlist
     */
    public function add(Product $product)
    {
        $user = auth()->user();
        
        // Check if product is already in wishlist
        $exists = $user->wishlist()->where('product_id', $product->id)->exists();
        
        if ($exists) {
            return redirect()->back()->with('info', 'Product is already in your wishlist.');
        }
        
        // Add to wishlist
        $user->wishlist()->create([
            'product_id' => $product->id
        ]);
        
        return redirect()->back()->with('success', 'Product added to wishlist.');
    }
    
    /**
     * Remove a product from the wishlist
     */
    public function remove(Product $product)
    {
        $user = auth()->user();
        
        // Remove from wishlist
        $user->wishlist()->where('product_id', $product->id)->delete();
        
        return redirect()->back()->with('success', 'Product removed from wishlist.');
    }
}