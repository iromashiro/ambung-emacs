<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $reportService;
    protected $orderService;
    protected $productService;

    public function __construct(
        ReportService $reportService,
        OrderService $orderService,
        ProductService $productService
    ) {
        $this->reportService = $reportService;
        $this->orderService = $orderService;
        $this->productService = $productService;

        // FIX: Use correct middleware
        $this->middleware(['auth', 'verified']);
        $this->middleware('role:seller');
        // DON'T use store.owner middleware here
    }

    /**
     * Display the seller dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $store = $user->store; // Get store relationship

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
            $stats = $this->getPerformanceStats($user);
            $data['stats'] = array_merge($data['stats'], $stats);

            // Get recent orders
            $data['recentOrders'] = $this->getRecentOrders($user);

            // Get low stock products
            $data['lowStockProducts'] = $this->getLowStockProducts($user);

            // Get chart data
            $data['salesData'] = $this->getSalesData($user);
            $data['orderStatusData'] = $this->getOrderStatusData($user);
        } catch (\Exception $e) {
            \Log::error('Seller dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'store_id' => $store->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            // Continue with default data if there's an error
        }

        return view('seller.dashboard', $data);
    }

    /**
     * Get performance statistics for the seller
     */
    private function getPerformanceStats($user)
    {
        try {
            // Use services to get stats
            $stats = $this->reportService->getSellerPerformanceReport($user->id);
            return $stats;
        } catch (\Exception $e) {
            \Log::error('Error getting performance stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent orders for the seller
     */
    private function getRecentOrders($user)
    {
        try {
            return $this->orderService->getOrdersForUser($user, ['limit' => 5]);
        } catch (\Exception $e) {
            \Log::error('Error getting recent orders: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get low stock products for the seller
     */
    private function getLowStockProducts($user)
    {
        try {
            $products = $this->productService->getSellerProducts($user);
            return $products->where('stock', '<', 10)->take(5);
        } catch (\Exception $e) {
            \Log::error('Error getting low stock products: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get sales data for chart
     */
    private function getSalesData($user)
    {
        try {
            // Get last 7 days sales data
            $salesData = $this->reportService->getDailySalesData($user->id, 7);
            return $salesData ?? [0, 0, 0, 0, 0, 0, 0];
        } catch (\Exception $e) {
            \Log::error('Error getting sales data: ' . $e->getMessage());
            return [0, 0, 0, 0, 0, 0, 0];
        }
    }

    /**
     * Get order status data for chart
     */
    private function getOrderStatusData($user)
    {
        try {
            $orderStatusData = $this->reportService->getOrderStatusData($user->id);
            return $orderStatusData ?? [0, 0, 0, 0, 0];
        } catch (\Exception $e) {
            \Log::error('Error getting order status data: ' . $e->getMessage());
            return [0, 0, 0, 0, 0];
        }
    }
}
