<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ReportService
{
    protected $orderRepository;
    protected $productRepository;
    protected $storeRepository;
    protected $userRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get dashboard summary statistics
     *
     * @return array
     */
    public function getDashboardSummary(): array
    {
        return Cache::remember('dashboard.summary', 3600, function () {
            return [
                'total_orders' => Order::count(),
                'total_revenue' => Order::where('status', 'delivered')->sum('total_amount'),
                'total_products' => Product::where('status', 'active')->count(),
                'total_stores' => Store::where('status', 'active')->count(),
                'total_users' => User::count(),
                'pending_orders' => Order::whereIn('status', ['new', 'accepted', 'dispatched'])->count(),
                'pending_stores' => Store::where('status', 'pending')->count(),
                'low_stock_products' => Product::where('stock', '<', 10)->where('status', 'active')->count()
            ];
        });
    }

    /**
     * Get sales report by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $groupBy day|week|month
     * @return Collection
     */
    public function getSalesReport(string $startDate, string $endDate, string $groupBy = 'day'): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $cacheKey = "sales.report.{$start->format('Y-m-d')}.{$end->format('Y-m-d')}.{$groupBy}";

        return Cache::remember($cacheKey, 3600, function () use ($start, $end, $groupBy) {
            $dateFormat = $this->getDateFormat($groupBy);

            return DB::table('orders')
                ->select(
                    DB::raw("DATE_TRUNC('{$groupBy}', created_at) as date"),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('COUNT(DISTINCT buyer_id) as unique_customers')
                )
                ->whereBetween('created_at', [$start, $end])
                ->where('status', '!=', 'canceled')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) use ($dateFormat) {
                    return [
                        'date' => Carbon::parse($item->date)->format($dateFormat),
                        'total_orders' => $item->total_orders,
                        'total_revenue' => $item->total_revenue,
                        'unique_customers' => $item->unique_customers
                    ];
                });
        });
    }

    /**
     * Get top selling products
     *
     * @param int $limit
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Collection
     */
    public function getTopSellingProducts(int $limit = 10, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->where('orders.status', '!=', 'canceled');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('orders.created_at', [$start, $end]);

            $cacheKey = "top.products.{$start->format('Y-m-d')}.{$end->format('Y-m-d')}.{$limit}";
        } else {
            $cacheKey = "top.products.all.{$limit}";
        }

        return Cache::remember($cacheKey, 3600, function () use ($query, $limit) {
            return $query->groupBy('products.id', 'products.name', 'products.price')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get top performing stores
     *
     * @param int $limit
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Collection
     */
    public function getTopPerformingStores(int $limit = 10, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('users', 'products.seller_id', '=', 'users.id')
            ->join('stores', 'users.id', '=', 'stores.seller_id')
            ->select(
                'stores.id',
                'stores.name',
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->where('orders.status', '!=', 'canceled');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('orders.created_at', [$start, $end]);

            $cacheKey = "top.stores.{$start->format('Y-m-d')}.{$end->format('Y-m-d')}.{$limit}";
        } else {
            $cacheKey = "top.stores.all.{$limit}";
        }

        return Cache::remember($cacheKey, 3600, function () use ($query, $limit) {
            return $query->groupBy('stores.id', 'stores.name')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get order status distribution
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Collection
     */
    public function getOrderStatusDistribution(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = DB::table('orders')
            ->select(
                'status',
                DB::raw('COUNT(*) as count')
            );

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);

            $cacheKey = "order.status.distribution.{$start->format('Y-m-d')}.{$end->format('Y-m-d')}";
        } else {
            $cacheKey = "order.status.distribution.all";
        }

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            return $query->groupBy('status')
                ->orderBy('status')
                ->get();
        });
    }

    /**
     * Get user registration statistics
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $groupBy day|week|month
     * @return Collection
     */
    public function getUserRegistrationStats(string $startDate, string $endDate, string $groupBy = 'day'): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $cacheKey = "user.registration.{$start->format('Y-m-d')}.{$end->format('Y-m-d')}.{$groupBy}";

        return Cache::remember($cacheKey, 3600, function () use ($start, $end, $groupBy) {
            $dateFormat = $this->getDateFormat($groupBy);

            return DB::table('users')
                ->select(
                    DB::raw("DATE_TRUNC('{$groupBy}', created_at) as date"),
                    DB::raw('COUNT(*) as total'),
                    'role'
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date', 'role')
                ->orderBy('date')
                ->get()
                ->map(function ($item) use ($dateFormat) {
                    return [
                        'date' => Carbon::parse($item->date)->format($dateFormat),
                        'role' => $item->role,
                        'total' => $item->total
                    ];
                });
        });
    }

    /**
     * Get inventory status report
     *
     * @param int $lowStockThreshold
     * @return Collection
     */
    public function getInventoryStatusReport(int $lowStockThreshold = 10): Collection
    {
        return Cache::remember("inventory.status.{$lowStockThreshold}", 3600, function () use ($lowStockThreshold) {
            return DB::table('products')
                ->join('users', 'products.seller_id', '=', 'users.id')
                ->join('stores', 'users.id', '=', 'stores.seller_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.stock',
                    'products.price',
                    'stores.name as store_name'
                )
                ->where('products.status', 'active')
                ->where('products.stock', '<', $lowStockThreshold)
                ->orderBy('products.stock')
                ->get();
        });
    }

    /**
     * Generate CSV report for orders
     *
     * @param string $startDate
     * @param string $endDate
     * @return string CSV content
     */
    public function generateOrdersCsvReport(string $startDate, string $endDate): string
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $orders = Order::with(['buyer', 'items.product.seller'])
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $csvHeader = [
            'Order ID',
            'Date',
            'Buyer Name',
            'Buyer Email',
            'Total Amount',
            'Status',
            'Items Count',
            'Shipping Address',
            'Phone'
        ];

        $csvData = [];

        foreach ($orders as $order) {
            $csvData[] = [
                $order->id,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->buyer->name,
                $order->buyer->email,
                $order->total_amount,
                $order->status,
                $order->items->count(),
                $order->shipping_address,
                $order->phone
            ];
        }

        return $this->arrayToCsv($csvHeader, $csvData);
    }

    /**
     * Generate CSV report for products
     *
     * @return string CSV content
     */
    public function generateProductsCsvReport(): string
    {
        $products = Product::with(['seller', 'category'])
            ->where('status', 'active')
            ->get();

        $csvHeader = [
            'Product ID',
            'Name',
            'Category',
            'Price',
            'Stock',
            'Seller',
            'Status',
            'Created At'
        ];

        $csvData = [];

        foreach ($products as $product) {
            $csvData[] = [
                $product->id,
                $product->name,
                $product->category->name,
                $product->price,
                $product->stock,
                $product->seller->name,
                $product->status,
                $product->created_at->format('Y-m-d H:i:s')
            ];
        }

        return $this->arrayToCsv($csvHeader, $csvData);
    }

    /**
     * Get seller performance report
     */
    public function getSellerPerformanceReport(int $userId): array
    {
        try {
            // Get basic stats
            $totalProducts = \App\Models\Product::where('seller_id', $userId)->count();
            $totalOrders = \App\Models\Order::whereHas('items.product', function ($q) use ($userId) {
                $q->where('seller_id', $userId);
            })->count();

            $totalRevenue = \App\Models\Order::whereHas('items.product', function ($q) use ($userId) {
                $q->where('seller_id', $userId);
            })->sum('total');

            return [
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'average_rating' => 4.5, // Default for now
                'total_reviews' => 0,
                'product_growth' => 0,
                'order_growth' => 0,
                'revenue_growth' => 0,
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getSellerPerformanceReport: ' . $e->getMessage());
            return [
                'total_products' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'average_rating' => 0,
                'total_reviews' => 0,
                'product_growth' => 0,
                'order_growth' => 0,
                'revenue_growth' => 0,
            ];
        }
    }

    /**
     * Get daily sales data for chart
     */
    public function getDailySalesData(int $userId, int $days = 7): array
    {
        try {
            // Simple implementation - return dummy data for now
            return [1000000, 1500000, 800000, 2000000, 1200000, 1800000, 2200000];
        } catch (\Exception $e) {
            \Log::error('Error in getDailySalesData: ' . $e->getMessage());
            return array_fill(0, $days, 0);
        }
    }

    /**
     * Get order status data for chart
     */
    public function getOrderStatusData(int $userId): array
    {
        try {
            // Simple implementation - return dummy data for now
            return [5, 3, 2, 8, 1]; // [New, Processing, Shipped, Delivered, Cancelled]
        } catch (\Exception $e) {
            \Log::error('Error in getOrderStatusData: ' . $e->getMessage());
            return [0, 0, 0, 0, 0];
        }
    }

    /**
     * Get date format based on grouping
     *
     * @param string $groupBy
     * @return string
     */
    private function getDateFormat(string $groupBy): string
    {
        switch ($groupBy) {
            case 'week':
                return 'Y-W';
            case 'month':
                return 'Y-m';
            case 'year':
                return 'Y';
            default:
                return 'Y-m-d';
        }
    }

    /**
     * Convert array to CSV
     *
     * @param array $header
     * @param array $data
     * @return string
     */
    private function arrayToCsv(array $header, array $data): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $header);

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
