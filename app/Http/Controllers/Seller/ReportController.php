<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
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
        $this->middleware(['auth', 'role:seller', 'store.owner']);
    }

    /**
     * Display the seller dashboard with summary statistics
     */
    public function dashboard()
    {
        $sellerId = auth()->id();

        // Get performance data
        $performanceData = $this->reportService->getSellerPerformanceReport($sellerId);

        // Get order statistics
        $orderStats = $this->orderService->getSellerOrderStatistics(auth()->user());

        // Get products
        $products = $this->productService->getSellerProducts(auth()->user());

        // Get low stock products
        $lowStockProducts = $products->where('stock', '<', 10);

        return view('seller.dashboard', compact(
            'performanceData',
            'orderStats',
            'products',
            'lowStockProducts'
        ));
    }

    /**
     * Display the sales report page for seller
     */
    public function salesReport(Request $request)
    {
        $sellerId = auth()->id();
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));

        $performanceData = $this->reportService->getSellerPerformanceReport(
            $sellerId,
            $startDate,
            $endDate
        );

        // Get orders for this seller in the date range
        $orders = $this->orderService->getOrdersForUser(
            auth()->user(),
            ['date_from' => $startDate, 'date_to' => $endDate]
        );

        return view('seller.reports.sales', compact(
            'performanceData',
            'orders',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display the products report page for seller
     */
    public function productsReport()
    {
        $sellerId = auth()->id();

        // Get all products for this seller
        $products = $this->productService->getSellerProducts(auth()->user());

        // Get top selling products for this seller
        $topProducts = $this->reportService->getSellerPerformanceReport($sellerId)['top_products'];

        // Get low stock products
        $lowStockProducts = $products->where('stock', '<', 10);

        return view('seller.reports.products', compact(
            'products',
            'topProducts',
            'lowStockProducts'
        ));
    }

    /**
     * Display the inventory management page
     */
    public function inventory()
    {
        // Get all products for this seller
        $products = $this->productService->getSellerProducts(auth()->user());

        // Get low stock products
        $lowStockProducts = $products->where('stock', '<', 10);

        // Get out of stock products
        $outOfStockProducts = $products->where('stock', 0);

        return view('seller.inventory', compact(
            'products',
            'lowStockProducts',
            'outOfStockProducts'
        ));
    }
}
