<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display the dashboard with summary statistics
     */
    public function dashboard()
    {
        $summary = $this->reportService->getDashboardSummary();

        // Get sales data for the last 30 days
        $endDate = Carbon::now()->format('Y-m-d');
        $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $salesData = $this->reportService->getSalesReport($startDate, $endDate, 'day');

        // Get order status distribution
        $orderStatusData = $this->reportService->getOrderStatusDistribution();

        // Get top selling products
        $topProducts = $this->reportService->getTopSellingProducts(5);

        // Get top performing stores
        $topStores = $this->reportService->getTopPerformingStores(5);

        return view('admin.dashboard', compact(
            'summary',
            'salesData',
            'orderStatusData',
            'topProducts',
            'topStores'
        ));
    }

    /**
     * Display the sales report page
     */
    public function salesReport(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'day');

        $salesData = $this->reportService->getSalesReport($startDate, $endDate, $groupBy);

        return view('admin.reports.sales', compact('salesData', 'startDate', 'endDate', 'groupBy'));
    }

    /**
     * Display the products report page
     */
    public function productsReport(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));

        $topProducts = $this->reportService->getTopSellingProducts(20, $startDate, $endDate);
        $lowStockProducts = $this->reportService->getInventoryStatusReport(10);

        return view('admin.reports.products', compact(
            'topProducts',
            'lowStockProducts',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display the stores report page
     */
    public function storesReport(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));

        $topStores = $this->reportService->getTopPerformingStores(20, $startDate, $endDate);

        return view('admin.reports.stores', compact('topStores', 'startDate', 'endDate'));
    }

    /**
     * Display the users report page
     */
    public function usersReport(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'day');

        $registrationStats = $this->reportService->getUserRegistrationStats($startDate, $endDate, $groupBy);

        return view('admin.reports.users', compact('registrationStats', 'startDate', 'endDate', 'groupBy'));
    }

    /**
     * Download orders CSV report
     */
    public function downloadOrdersCsv(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $csv = $this->reportService->generateOrdersCsvReport($startDate, $endDate);

        $filename = "orders_report_{$startDate}_to_{$endDate}.csv";

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ]);
    }

    /**
     * Download products CSV report
     */
    public function downloadProductsCsv()
    {
        $csv = $this->reportService->generateProductsCsvReport();

        $filename = "products_report_" . Carbon::now()->format('Y-m-d') . ".csv";

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ]);
    }

    /**
     * Display seller performance report
     */
    public function sellerPerformance(Request $request, $sellerId)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));

        $performanceData = $this->reportService->getSellerPerformanceReport(
            $sellerId,
            $startDate,
            $endDate
        );

        $seller = \App\Models\User::findOrFail($sellerId);

        return view('admin.reports.seller-performance', compact(
            'performanceData',
            'seller',
            'startDate',
            'endDate'
        ));
    }
}
