<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
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

        // If user doesn't have store, return basic view
        if (!$store) {
            return view('seller.dashboard', [
                'store' => null,
                'stats' => $this->getDefaultStats(),
                'recentOrders' => collect(),
                'lowStockProducts' => collect(),
                'salesData' => [0, 0, 0, 0, 0, 0, 0],
                'orderStatusData' => [0, 0, 0, 0, 0],
            ]);
        }

        // FIX: Change from 'approved' to 'active'
        if ($store->status !== 'active') {
            return view('seller.dashboard', [
                'store' => $store,
                'stats' => $this->getDefaultStats(),
                'recentOrders' => collect(),
                'lowStockProducts' => collect(),
                'salesData' => [0, 0, 0, 0, 0, 0, 0],
                'orderStatusData' => [0, 0, 0, 0, 0],
            ]);
        }

        // Store is active, load full dashboard data
        try {
            $sellerId = $user->id;

            // Debug: Check seller ID and store
            Log::info('Dashboard Debug', [
                'seller_id' => $sellerId,
                'store_id' => $store->id,
                'store_status' => $store->status
            ]);

            // Get performance stats
            $stats = $this->getPerformanceStats($sellerId);

            // Get recent orders (last 5)
            $recentOrders = $this->getRecentOrders($sellerId);

            // Get low stock products (stock <= 10)
            $lowStockProducts = $this->getLowStockProducts($sellerId);

            // Get chart data
            $salesData = $this->getSalesData($sellerId);
            $orderStatusData = $this->getOrderStatusData($sellerId);

            // Debug logging
            Log::info('Dashboard Data Debug', [
                'seller_id' => $sellerId,
                'store_id' => $store->id,
                'stats' => $stats,
                'recent_orders_count' => $recentOrders->count(),
                'sales_data' => $salesData,
                'order_status_data' => $orderStatusData
            ]);

            return view('seller.dashboard', [
                'store' => $store,
                'stats' => $stats,
                'recentOrders' => $recentOrders,
                'lowStockProducts' => $lowStockProducts,
                'salesData' => $salesData,
                'orderStatusData' => $orderStatusData,
            ]);
        } catch (\Exception $e) {
            Log::error('Seller dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'store_id' => $store->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            // Return with default data if error occurs
            return view('seller.dashboard', [
                'store' => $store,
                'stats' => $this->getDefaultStats(),
                'recentOrders' => collect(),
                'lowStockProducts' => collect(),
                'salesData' => [0, 0, 0, 0, 0, 0, 0],
                'orderStatusData' => [0, 0, 0, 0, 0],
            ]);
        }
    }

    /**
     * Get default stats
     */
    private function getDefaultStats(): array
    {
        return [
            'total_products' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
            'average_rating' => 4.5,
            'total_reviews' => 0,
            'product_growth' => 0,
            'order_growth' => 0,
            'revenue_growth' => 0,
        ];
    }

    /**
     * Get performance statistics for the seller
     */
    /**
     * Get performance statistics for the seller
     */
    private function getPerformanceStats($sellerId): array
    {
        try {
            // Debug: Check what seller ID we're using
            Log::info('Getting stats for seller ID: ' . $sellerId);

            // Get basic counts
            $totalProducts = Product::where('seller_id', $sellerId)->count();
            Log::info('Total products for seller ' . $sellerId . ': ' . $totalProducts);

            // FIX: Query orders dengan join langsung
            $ordersWithSellerItems = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.seller_id', $sellerId)
                ->select('orders.id')
                ->distinct()
                ->count();

            Log::info('Orders with seller items for seller ' . $sellerId . ': ' . $ordersWithSellerItems);

            // FIX: Calculate revenue from order_items with better join
            $totalRevenue = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.seller_id', $sellerId)
                ->sum(DB::raw('order_items.quantity * order_items.price'));

            Log::info('Total revenue for seller ' . $sellerId . ': ' . $totalRevenue);

            // Debug: Check raw data
            $orderItems = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.seller_id', $sellerId)
                ->select('order_items.*', 'products.name as product_name')
                ->get();

            Log::info('Order items for seller ' . $sellerId . ':', $orderItems->toArray());

            return [
                'total_products' => $totalProducts,
                'total_orders' => $ordersWithSellerItems,
                'total_revenue' => (float) ($totalRevenue ?: 0),
                'average_rating' => 4.5,
                'total_reviews' => 0,
                'product_growth' => 0,
                'order_growth' => 0,
                'revenue_growth' => 0,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting performance stats: ' . $e->getMessage(), [
                'seller_id' => $sellerId,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getDefaultStats();
        }
    }
    /**
     * Get recent orders for the seller
     */
    private function getRecentOrders($sellerId)
    {
        try {
            return Order::whereHas('items', function ($query) use ($sellerId) {
                $query->whereHas('product', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                });
            })
                ->with([
                    'user:id,name,email',
                    'items' => function ($query) use ($sellerId) {
                        $query->whereHas('product', function ($q) use ($sellerId) {
                            $q->where('seller_id', $sellerId);
                        });
                    },
                    'items.product:id,name,seller_id'
                ])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    // Calculate seller total
                    $sellerTotal = $order->items->sum(function ($item) {
                        return $item->quantity * $item->price;
                    });
                    $order->seller_total = $sellerTotal;
                    return $order;
                });
        } catch (\Exception $e) {
            Log::error('Error getting recent orders: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get low stock products for the seller
     */
    private function getLowStockProducts($sellerId)
    {
        try {
            return Product::where('seller_id', $sellerId)
                ->where('stock', '<=', 10)
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
    private function getSalesData($sellerId): array
    {
        try {
            $salesData = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->startOfDay();
                $nextDate = $date->copy()->addDay();

                $dailySales = DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('products.seller_id', $sellerId)
                    ->whereBetween('orders.created_at', [$date, $nextDate])
                    ->sum(DB::raw('order_items.quantity * order_items.price'));

                $salesData[] = (float) ($dailySales ?: 0);
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
    private function getOrderStatusData($sellerId): array
    {
        try {
            $statusCounts = Order::whereHas('items', function ($query) use ($sellerId) {
                $query->whereHas('product', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                });
            })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Return in order: new, processing, completed, canceled
            return [
                (int) ($statusCounts['new'] ?? 0),
                (int) ($statusCounts['processing'] ?? 0),
                (int) ($statusCounts['completed'] ?? 0),
                (int) ($statusCounts['canceled'] ?? 0),
                0 // Extra for future use
            ];
        } catch (\Exception $e) {
            Log::error('Error getting order status data: ' . $e->getMessage());
            return [0, 0, 0, 0, 0];
        }
    }
}
