<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\OrderService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $reportService;
    protected $orderService;
    protected $storeService;

    public function __construct(
        ReportService $reportService,
        OrderService $orderService,
        StoreService $storeService
    ) {
        $this->reportService = $reportService;
        $this->orderService = $orderService;
        $this->storeService = $storeService;
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Get dashboard summary
        $summary = $this->reportService->getDashboardSummary();

        // Get recent orders
        $recentOrders = $this->orderService->getOrdersForUser(
            auth()->user(),
            ['limit' => 5]
        );

        // Get pending store approvals
        $pendingStores = $this->storeService->getPendingStores();

        // Get sales data for the last 7 days
        $endDate = Carbon::now()->format('Y-m-d');
        $startDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $salesData = $this->reportService->getSalesReport($startDate, $endDate, 'day');

        // Get order status distribution
        $orderStatusData = $this->reportService->getOrderStatusDistribution();

        return view('admin.dashboard', compact(
            'summary',
            'recentOrders',
            'pendingStores',
            'salesData',
            'orderStatusData'
        ));
    }
}
