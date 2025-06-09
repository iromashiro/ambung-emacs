<?php

namespace Database\Seeders;

use App\Models\Wishlist;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all buyers
        $buyers = User::where('role', 'buyer')->get();
        
        // Get all active products
        $products = Product::where('status', 'active')->get();
        
        if ($buyers->isEmpty() || $products->isEmpty()) {
            return;
        }
        
        // Create wishlist items for some buyers
        foreach ($buyers as $buyer) {
            // 70% chance a buyer has wishlist items
            if (rand(1, 10) <= 7) {
                // Add 1-5 products to wishlist
                $wishlistCount = rand(1, 5);
                $selectedProducts = $products->random($wishlistCount);
                
                foreach ($selectedProducts as $product) {
                    Wishlist::create([
                        'user_id' => $buyer->id,
                        'product_id' => $product->id,
                    ]);
                }
            }
        }
    }
}