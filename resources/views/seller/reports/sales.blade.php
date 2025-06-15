@extends('layouts.seller')

@section('title', 'Sales Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Sales Report</h1>
                    <p class="text-muted">Track your sales performance and revenue</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </button>
                    <button class="btn btn-outline-success" onclick="exportReport('excel')">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('seller.reports.sales') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from"
                                    value="{{ request('date_from', now()->subDays(30)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                    value="{{ request('date_to', now()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="period" class="form-label">Quick Select</label>
                                <select class="form-select" id="period" name="period">
                                    <option value="">Custom Range</option>
                                    <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Today
                                    </option>
                                    <option value="yesterday" {{ request('period') === 'yesterday' ? 'selected' : '' }}>
                                        Yesterday</option>
                                    <option value="last_7_days"
                                        {{ request('period') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="last_30_days"
                                        {{ request('period') === 'last_30_days' ? 'selected' : '' }}>Last 30 Days
                                    </option>
                                    <option value="this_month"
                                        {{ request('period') === 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_month"
                                        {{ request('period') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary mb-1">Rp {{ number_format($summary['total_revenue'] ?? 0) }}</h3>
                            <small class="text-muted">Total Revenue</small>
                            @if(isset($summary['revenue_growth']))
                            <div class="mt-1">
                                <small class="text-{{ $summary['revenue_growth'] >= 0 ? 'success' : 'danger' }}">
                                    <i
                                        class="fas fa-arrow-{{ $summary['revenue_growth'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                    {{ number_format(abs($summary['revenue_growth']), 1) }}%
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success mb-1">{{ $summary['total_orders'] ?? 0 }}</h3>
                            <small class="text-muted">Total Orders</small>
                            @if(isset($summary['orders_growth']))
                            <div class="mt-1">
                                <small class="text-{{ $summary['orders_growth'] >= 0 ? 'success' : 'danger' }}">
                                    <i
                                        class="fas fa-arrow-{{ $summary['orders_growth'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                    {{ number_format(abs($summary['orders_growth']), 1) }}%
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info mb-1">Rp {{ number_format($summary['avg_order_value'] ?? 0) }}</h3>
                            <small class="text-muted">Avg Order Value</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-1">{{ $summary['total_items'] ?? 0 }}</h3>
                            <small class="text-muted">Items Sold</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Sales Chart -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Sales Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top Selling Products</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($topProducts) && $topProducts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Sold</th>
                                            <th>Revenue</th>
                                            <th>Avg Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topProducts as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->images && $product->images->count() > 0)
                                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                                                        class="rounded me-2"
                                                        style="width: 30px; height: 30px; object-fit: cover;"
                                                        alt="Product">
                                                    @else
                                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 30px; height: 30px;">
                                                        <i class="fas fa-image text-muted small"></i>
                                                    </div>
                                                    @endif
                                                    <div>
                                                        <small class="fw-bold">{{ $product->name }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary">{{ $product->total_sold ?? 0 }}</span>
                                            </td>
                                            <td><strong>Rp {{ number_format($product->total_revenue ?? 0) }}</strong>
                                            </td>
                                            <td>Rp {{ number_format($product->avg_price ?? 0) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-3">
                                <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No sales data available for the selected period</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Sales by Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Orders by Status</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Payment Methods</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($paymentMethods) && count($paymentMethods) > 0)
                            @foreach($paymentMethods as $method)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $method['name'] }}</span>
                                <div>
                                    <span class="badge bg-light text-dark">{{ $method['count'] }}</span>
                                    <small class="text-muted">({{ $method['percentage'] }}%)</small>
                                </div>
                            </div>
                            <div class="progress mb-3" style="height: 4px;">
                                <div class="progress-bar" style="width: {{ $method['percentage'] }}%"></div>
                            </div>
                            @endforeach
                            @else
                            <p class="text-muted small mb-0">No payment data available</p>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Recent Orders</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($recentOrders) && $recentOrders->count() > 0)
                            @foreach($recentOrders as $order)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <small class="fw-bold">#{{ $order->order_number }}</small><br>
                                    <small class="text-muted">{{ $order->created_at->format('M d, H:i') }}</small>
                                </div>
                                <div class="text-end">
                                    <small class="fw-bold">Rp {{ number_format($order->total) }}</small><br>
                                    <span
                                        class="badge bg-{{ $order->status === 'completed' ? 'success' : 'warning' }} badge-sm">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                            <a href="{{ route('seller.orders.index') }}"
                                class="btn btn-sm btn-outline-primary w-100 mt-2">
                                View All Orders
                            </a>
                            @else
                            <p class="text-muted small mb-0">No recent orders</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Trend Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels'] ?? []),
        datasets: [{
            label: 'Revenue',
            data: @json($chartData['revenue'] ?? []),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'Orders',
            data: @json($chartData['orders'] ?? []),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($statusData['labels'] ?? []),
        datasets: [{
            data: @json($statusData['data'] ?? []),
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#17a2b8',
                '#dc3545',
                '#6c757d'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Quick period selection
document.getElementById('period').addEventListener('change', function() {
    if (this.value) {
        const form = this.closest('form');
        form.submit();
    }
});

// Export functions
function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.open(`{{ route('seller.reports.sales') }}?${params.toString()}`, '_blank');
}
</script>
@endsection