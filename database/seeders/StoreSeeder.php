<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create store for demo seller
        $demoSeller = User::where('email', 'seller@example.com')->first();
        
        if ($demoSeller) {
            Store::create([
                'seller_id' => $demoSeller->id,
                'name' => 'Demo UMKM Store',
                'description' => 'This is a demo store for testing purposes. It offers a variety of products across different categories.',
                'address' => 'Jl. Seller Demo No. 123, Jakarta',
                'phone' => '081234567890',
                'status' => 'active',
            ]);
        }

        // Create stores for active sellers
        $activeSellers = User::where('role', 'seller')
            ->where('status', 'active')
            ->where('email', '!=', 'seller@example.com')
            ->get();

        foreach ($activeSellers as $seller) {
            Store::create([
                'seller_id' => $seller->id,
                'name' => $seller->name . '\'s Store',
                'description' => 'Welcome to ' . $seller->name . '\'s Store. We offer quality products at affordable prices.',
                'address' => $seller->address ?? 'Jakarta, Indonesia',
                'phone' => $seller->phone ?? '08123456789',
                'status' => 'active',
            ]);
        }

        // Create pending stores for pending sellers
        $pendingSellers = User::where('role', 'seller')
            ->where('status', 'pending')
            ->get();

        foreach ($pendingSellers as $seller) {
            Store::create([
                'seller_id' => $seller->id,
                'name' => $seller->name . '\'s Store',
                'description' => 'Welcome to ' . $seller->name . '\'s Store. We offer quality products at affordable prices.',
                'address' => $seller->address ?? 'Jakarta, Indonesia',
                'phone' => $seller->phone ?? '08123456789',
                'status' => 'pending',
            ]);
        }
    }
}