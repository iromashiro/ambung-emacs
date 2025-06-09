<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all buyers
        $buyers = User::where('role', 'buyer')->get();
        
        // Get all active products with stock
        $availableProducts = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->get();
            
        if ($buyers->isEmpty() || $availableProducts->isEmpty()) {
            return;
        }
        
        // Create orders for the past 3 months
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        // Create 50-100 orders
        $orderCount = rand(50, 100);
        
        for ($i = 0; $i < $orderCount; $i++) {
            // Select random buyer
            $buyer = $buyers->random();
            
            // Generate random date between start and end date
            $orderDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );
            
            // Determine order status based on date
            $status = $this->determineOrderStatus($orderDate);
            
            // Create order
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'total_amount' => 0, // Will calculate after adding items
                'status' => $status,
                'shipping_address' => $buyer->address ?? 'Jl. Pembeli No. ' . rand(1, 999) . ', Jakarta',
                'phone' => $buyer->phone ?? '08' . rand(100000000, 999999999),
                'notes' => rand(0, 1) ? 'Please handle with care.' : null,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
            
            // Add 1-5 items to order
            $itemCount = rand(1, 5);
            $orderTotal = 0;
            
            // Shuffle products to get random selection
            $shuffledProducts = $availableProducts->shuffle()->take($itemCount);
            
            foreach ($shuffledProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $total = $price * $quantity;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ]);
                
                $orderTotal += $total;
            }
            
            // Update order total
            $order->update(['total_amount' => $orderTotal]);
        }
    }
    
    /**
     * Determine order status based on creation date.
     */
    private function determineOrderStatus(Carbon $orderDate): string
    {
        $daysSinceOrder = $orderDate->diffInDays(Carbon::now());
        
        // Orders from today are mostly new
        if ($daysSinceOrder < 1) {
            $statuses = ['new', 'new', 'new', 'accepted', 'canceled'];
            return $statuses[array_rand($statuses)];
        }
        
        // Orders from yesterday are mostly accepted or dispatched
        if ($daysSinceOrder < 2) {
            $statuses = ['new', 'accepted', 'accepted', 'dispatched', 'canceled'];
            return $statuses[array_rand($statuses)];
        }
        
        // Orders from 2-3 days ago are mostly dispatched or delivered
        if ($daysSinceOrder < 4) {
            $statuses = ['accepted', 'dispatched', 'dispatched', 'delivered', 'canceled'];
            return $statuses[array_rand($statuses)];
        }
        
        // Older orders are mostly delivered or canceled
        $statuses = ['dispatched', 'delivered', 'delivered', 'delivered', 'canceled'];
        return $statuses[array_rand($statuses)];
    }
}