<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@ambungemac.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create demo seller
        User::create([
            'name' => 'Demo Seller',
            'email' => 'seller@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'status' => 'active',
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'address' => 'Jl. Seller Demo No. 123, Jakarta',
        ]);

        // Create demo buyer
        User::create([
            'name' => 'Demo Buyer',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'role' => 'buyer',
            'status' => 'active',
            'email_verified_at' => now(),
            'phone' => '089876543210',
            'address' => 'Jl. Buyer Demo No. 456, Jakarta',
        ]);

        // Create additional sellers (some active, some pending)
        User::factory()->seller()->count(5)->create();
        User::factory()->seller()->pending()->count(3)->create();

        // Create additional buyers
        User::factory()->count(20)->create();
    }
}