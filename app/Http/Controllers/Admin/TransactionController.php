<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected $orderService;
    protected $reportService;

    public function __construct(OrderService $orderService, ReportService $reportService)
    {
        $this->orderService = $orderService;
        $this->reportService = $reportService;
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of all transactions
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search', 'date_from', 'date_to']);
        $orders = $this->orderService->getOrdersForUser(auth()->user(), $filters);

        return view('admin.transactions.index', compact('orders'));
    }

    /**
     * Display the specified transaction
     */
    public function show($id)
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            return redirect()->route('admin.transactions.index')
                ->with('error', 'Order not found');
        }

        return view('admin.transactions.show', compact('order'));
    }

    /**
     * Display transaction analytics
     */
    public function analytics(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'day');

        $salesData = $this->reportService->getSalesReport($startDate, $endDate, $groupBy);
        $orderStatusData = $this->reportService->getOrderStatusDistribution($startDate, $endDate);

        return view('admin.transactions.analytics', compact(
            'salesData',
            'orderStatusData',
            'startDate',
            'endDate',
            'groupBy'
        ));
    }

    /**
     * Export transactions to CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return $this->reportService->generateOrdersCsvReport($startDate, $endDate);
    }
}
