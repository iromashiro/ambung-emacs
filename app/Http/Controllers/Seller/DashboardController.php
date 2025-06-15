<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $orderService;
    protected $productService;

    public function __construct(
        OrderService $orderService,
        ProductService $productService
    ) {
        $this->orderService = $orderService;
        $this->productService = $productService;

        $this->middleware(['auth', 'verified']);
        $this->middleware('role:seller');
        $this->middleware('store.owner');
    }

    /**
     * Display the seller dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $store = $user->store;

        // Initialize default data
        $data = [
            'store' => $store,
            'stats' => [
                'total_products' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'average_rating' => 0,
                'total_reviews' => 0,
                'product_growth' => 0,
                'order_growth' => 0,
                'revenue_growth' => 0,
            ],
            'recentOrders' => collect(),
            'lowStockProducts' => collect(),
            'salesData' => [0, 0, 0, 0, 0, 0, 0],
            'orderStatusData' => [0, 0, 0, 0, 0],
        ];

        // If user doesn't have store, return basic view
        if (!$store) {
            return view('seller.dashboard', $data);
        }

        // If store is not approved, return basic view with store info
        if ($store->status !== 'approved') {
            return view('seller.dashboard', $data);
        }

        // Store is approved, load full dashboard data
        try {
            // Get performance stats
            $stats = $this->getPerformanceStats($store);
            $data['stats'] = array_merge($data['stats'], $stats);

            // Get recent orders
            $data['recentOrders'] = $this->getRecentOrders($store);

            // Get low stock products
            $data['lowStockProducts'] = $this->getLowStockProducts($store);

            // Get chart data
            $data['salesData'] = $this->getSalesData($store);
            $data['orderStatusData'] = $this->getOrderStatusData($store);
        } catch (\Exception $e) {
            Log::error('Seller dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'store_id' => $store->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return view('seller.dashboard', $data);
    }

    /**
     * Get performance statistics for the seller
     */
    private function getPerformanceStats($store): array
    {
        try {
            $sellerId = $store->seller_id;
            $currentMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            // Get current month stats
            $currentStats = $this->getStatsForPeriod($sellerId, $currentMonth, Carbon::now());
            $lastMonthStats = $this->getStatsForPeriod($sellerId, $lastMonth, $currentMonth);

            // Calculate growth percentages
            $productGrowth = $this->calculateGrowth($currentStats['products'], $lastMonthStats['products']);
            $orderGrowth = $this->calculateGrowth($currentStats['orders'], $lastMonthStats['orders']);
            $revenueGrowth = $this->calculateGrowth($currentStats['revenue'], $lastMonthStats['revenue']);

            return [
                'total_products' => $currentStats['products'],
                'total_orders' => $currentStats['orders'],
                'total_revenue' => $currentStats['revenue'],
                'average_rating' => $currentStats['rating'],
                'total_reviews' => $currentStats['reviews'],
                'product_growth' => $productGrowth,
                'order_growth' => $orderGrowth,
                'revenue_growth' => $revenueGrowth,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting performance stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get stats for specific period
     */
    private function getStatsForPeriod($sellerId, $startDate, $endDate): array
    {
        // Total products
        $totalProducts = Product::where('seller_id', $sellerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Total orders and revenue
        $orderStats = Order::whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
            ->first();

        // Average rating (simplified - you might want to implement proper review system)
        $averageRating = 4.5; // Placeholder
        $totalReviews = 0; // Placeholder

        return [
            'products' => $totalProducts,
            'orders' => $orderStats->total_orders ?? 0,
            'revenue' => $orderStats->total_revenue ?? 0,
            'rating' => $averageRating,
            'reviews' => $totalReviews,
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Get recent orders for the seller
     */
    private function getRecentOrders($store)
    {
        try {
            return Order::whereHas('items.product', function ($q) use ($store) {
                $q->where('seller_id', $store->seller_id);
            })
                ->with(['user', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting recent orders: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get low stock products for the seller
     */
    private function getLowStockProducts($store)
    {
        try {
            return Product::where('seller_id', $store->seller_id)
                ->where('stock', '<', 10)
                ->orderBy('stock', 'asc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting low stock products: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get sales data for chart (last 7 days)
     */
    private function getSalesData($store): array
    {
        try {
            $salesData = [];
            $sellerId = $store->seller_id;

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->startOfDay();
                $nextDate = $date->copy()->addDay();

                $dailySales = Order::whereHas('items.product', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                })
                    ->whereBetween('created_at', [$date, $nextDate])
                    ->sum('total_amount');

                $salesData[] = $dailySales ?? 0;
            }

            return $salesData;
        } catch (\Exception $e) {
            Log::error('Error getting sales data: ' . $e->getMessage());
            return [0, 0, 0, 0, 0, 0, 0];
        }
    }

    /**
     * Get order status data for chart
     */
    private function getOrderStatusData($store): array
    {
        try {
            $sellerId = $store->seller_id;

            $statusCounts = Order::whereHas('items.product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Return in order: new, accepted, dispatched, delivered, canceled
            return [
                $statusCounts['new'] ?? 0,
                $statusCounts['accepted'] ?? 0,
                $statusCounts['dispatched'] ?? 0,
                $statusCounts['delivered'] ?? 0,
                $statusCounts['canceled'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting order status data: ' . $e->getMessage());
            return [0, 0, 0, 0, 0];
        }
    }
}
