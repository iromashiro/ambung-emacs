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
        $this->middleware(['auth', 'role:seller', 'store.owner']);
    }

    /**
     * Display the seller dashboard
     */
    public function index()
    {
        $sellerId = auth()->id();

        // Get performance data
        $performanceData = $this->reportService->getSellerPerformanceReport($sellerId);

        // Get recent orders
        $recentOrders = $this->orderService->getOrdersForUser(
            auth()->user(),
            ['limit' => 5]
        );

        // Get low stock products
        $products = $this->productService->getSellerProducts(auth()->user());
        $lowStockProducts = $products->where('stock', '<', 10)->take(5);

        return view('seller.dashboard', compact(
            'performanceData',
            'recentOrders',
            'lowStockProducts'
        ));
    }
}
