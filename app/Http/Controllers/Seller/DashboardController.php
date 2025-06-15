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

        // If store is not approved, return basic view with store info
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

        // Store is approved, load full dashboard data
        try {
            $sellerId = $user->id;

            // Debug: Check seller ID
            Log::info('Dashboard Debug - Seller ID', ['seller_id' => $sellerId, 'user_id' => $user->id, 'store_id' => $store->id]);

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


    /**
     * Get performance statistics for the seller
     */
    private function getPerformanceStats($sellerId): array
    {
        try {
            // Debug: Check if products exist for this seller
            $productCount = Product::where('seller_id', $sellerId)->count();
            Log::info('Products count for seller', ['seller_id' => $sellerId, 'count' => $productCount]);

            // Debug: Check if orders exist
            $orderCount = Order::whereHas('items', function ($query) use ($sellerId) {
                $query->whereHas('product', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                });
            })->count();
            Log::info('Orders count for seller', ['seller_id' => $sellerId, 'count' => $orderCount]);

            // If no products, return zeros
            if ($productCount === 0) {
                Log::info('No products found for seller', ['seller_id' => $sellerId]);
                return $this->getDefaultStats();
            }

            $currentMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->startOfMonth()->subSecond();

            // Current month stats
            $currentProducts = Product::where('seller_id', $sellerId)
                ->whereBetween('created_at', [$currentMonth, Carbon::now()])
                ->count();

            // FIX: Query orders yang benar - coba beberapa cara
            $currentOrdersQuery = Order::whereHas('items', function ($query) use ($sellerId) {
                $query->whereHas('product', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                });
            })->whereBetween('created_at', [$currentMonth, Carbon::now()]);

            $currentOrders = $currentOrdersQuery->count();

            // FIX: Calculate revenue dari order items
            $currentRevenue = $this->calculateSellerRevenue($sellerId, $currentMonth, Carbon::now());

            // Last month stats for comparison
            $lastMonthProducts = Product::where('seller_id', $sellerId)
                ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
                ->count();

            $lastMonthOrders = Order::whereHas('items', function ($query) use ($sellerId) {
                $query->whereHas('product', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                });
            })->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

            $lastMonthRevenue = $this->calculateSellerRevenue($sellerId, $lastMonth, $lastMonthEnd);

            // Total stats (all time)
            $totalProducts = Product::where('seller_id', $sellerId)->count();

            $totalOrders = Order::whereHas('items', function ($query) use ($sellerId) {
                $query->whereHas('product', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                });
            })->count();

            $totalRevenue = $this->calculateSellerRevenue($sellerId);

            // Debug log the calculations
            Log::info('Stats calculation debug', [
                'seller_id' => $sellerId,
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'current_orders' => $currentOrders,
                'current_revenue' => $currentRevenue
            ]);

            // Calculate growth percentages
            $productGrowth = $this->calculateGrowth($currentProducts, $lastMonthProducts);
            $orderGrowth = $this->calculateGrowth($currentOrders, $lastMonthOrders);
            $revenueGrowth = $this->calculateGrowth($currentRevenue, $lastMonthRevenue);

            return [
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'average_rating' => 4.5, // Placeholder
                'total_reviews' => 0, // Placeholder
                'product_growth' => $productGrowth,
                'order_growth' => $orderGrowth,
                'revenue_growth' => $revenueGrowth,
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
     * Calculate seller revenue from order items
     */
    private function calculateSellerRevenue($sellerId, $startDate = null, $endDate = null): float
    {
        try {
            $query = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.seller_id', $sellerId);

            if ($startDate && $endDate) {
                $query->whereBetween('orders.created_at', [$startDate, $endDate]);
            }

            $revenue = $query->sum(DB::raw('order_items.quantity * order_items.price'));

            Log::info('Revenue calculation', [
                'seller_id' => $sellerId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'revenue' => $revenue
            ]);

            return (float) ($revenue ?: 0);
        } catch (\Exception $e) {
            Log::error('Error calculating seller revenue: ' . $e->getMessage(), [
                'seller_id' => $sellerId,
                'trace' => $e->getTraceAsString()
            ]);
            return 0.0;
        }
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
    private function getRecentOrders($sellerId)
    {
        try {
            $orders = Order::whereHas('items', function ($query) use ($sellerId) {
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
                    // Calculate total amount for this seller's items only
                    $sellerTotal = $order->items->sum(function ($item) {
                        return $item->quantity * $item->price;
                    });
                    $order->seller_total = $sellerTotal;
                    return $order;
                });

            Log::info('Recent orders debug', [
                'seller_id' => $sellerId,
                'orders_count' => $orders->count(),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'status' => $order->status,
                        'seller_total' => $order->seller_total,
                        'items_count' => $order->items->count()
                    ];
                })
            ]);

            return $orders;
        } catch (\Exception $e) {
            Log::error('Error getting recent orders: ' . $e->getMessage(), [
                'seller_id' => $sellerId,
                'trace' => $e->getTraceAsString()
            ]);
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

                $dailySales = $this->calculateSellerRevenue($sellerId, $date, $nextDate);
                $salesData[] = (float) $dailySales;
            }

            Log::info('Sales data debug', [
                'seller_id' => $sellerId,
                'sales_data' => $salesData
            ]);

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

            Log::info('Order status data debug', [
                'seller_id' => $sellerId,
                'status_counts' => $statusCounts
            ]);

            // Return in order: new, accepted, dispatched, delivered, canceled
            return [
                (int) ($statusCounts['new'] ?? 0),
                (int) ($statusCounts['accepted'] ?? 0),
                (int) ($statusCounts['dispatched'] ?? 0),
                (int) ($statusCounts['delivered'] ?? 0),
                (int) ($statusCounts['canceled'] ?? 0),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting order status data: ' . $e->getMessage());
            return [0, 0, 0, 0, 0];
        }
    }
}
