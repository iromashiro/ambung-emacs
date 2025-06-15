@extends('layouts.seller')

@section('title', 'Products Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Products Report</h1>
                    <p class="text-muted">Analyze your product performance and trends</p>
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

            <!-- Filter Options -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('seller.reports.products') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @if(isset($categories))
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sort_by" class="form-label">Sort By</label>
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="sales" {{ request('sort_by') === 'sales' ? 'selected' : '' }}>Sales
                                        Volume</option>
                                    <option value="revenue" {{ request('sort_by') === 'revenue' ? 'selected' : '' }}>
                                        Revenue</option>
                                    <option value="views" {{ request('sort_by') === 'views' ? 'selected' : '' }}>Views
                                    </option>
                                    <option value="stock" {{ request('sort_by') === 'stock' ? 'selected' : '' }}>Stock
                                        Level</option>
                                    <option value="created" {{ request('sort_by') === 'created' ? 'selected' : '' }}>
                                        Date Created</option>
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
                            <h3 class="text-primary mb-1">{{ $summary['total_products'] ?? 0 }}</h3>
                            <small class="text-muted">Total Products</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success mb-1">{{ $summary['active_products'] ?? 0 }}</h3>
                            <small class="text-muted">Active Products</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-1">{{ $summary['low_stock'] ?? 0 }}</h3>
                            <small class="text-muted">Low Stock</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger mb-1">{{ $summary['out_of_stock'] ?? 0 }}</h3>
                            <small class="text-muted">Out of Stock</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Products Performance Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Product Performance</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($products) && $products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Stock</th>
                                            <th>Views</th>
                                            <th>Sales</th>
                                            <th>Revenue</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->images && $product->images->count() > 0)
                                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                                                        class="rounded me-3"
                                                        style="width: 40px; height: 40px; object-fit: cover;"
                                                        alt="Product">
                                                    @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                                        <small
                                                            class="text-muted">{{ $product->sku ?? 'No SKU' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-light text-dark">{{ $product->category->name ?? 'No Category' }}</span>
                                            </td>
                                            <td>
                                                @if($product->stock <= 0) <span class="badge bg-danger">Out of
                                                    Stock</span>
                                                    @elseif($product->stock <= 10) <span class="badge bg-warning">
                                                        {{ $product->stock }}</span>
                                                        @else
                                                        <span class="badge bg-success">{{ $product->stock }}</span>
                                                        @endif
                                            </td>
                                            <td>{{ $product->views ?? 0 }}</td>
                                            <td>{{ $product->total_sales ?? 0 }}</td>
                                            <td><strong>Rp {{ number_format($product->total_revenue ?? 0) }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                $performance = $product->performance_score ?? 0;
                                                $color = $performance >= 80 ? 'success' : ($performance >= 60 ?
                                                'warning' : 'danger');
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-{{ $color }}"
                                                            style="width: {{ $performance }}%"></div>
                                                    </div>
                                                    <small class="text-{{ $color }}">{{ $performance }}%</small>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if(method_exists($products, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $products->links() }}
                            </div>
                            @endif
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Products Found</h5>
                                <p class="text-muted">No products match your current filters.</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Category Performance -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Performance by Category</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Top Performers -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Top Performers</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($topPerformers) && $topPerformers->count() > 0)
                            @foreach($topPerformers as $index => $product)
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 30px; height: 30px;">
                                    <span class="text-white fw-bold small">{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">{{ $product->total_sales ?? 0 }} sold</small>
                                </div>
                                <div class="text-end">
                                    <small class="fw-bold">Rp {{ number_format($product->total_revenue ?? 0) }}</small>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <p class="text-muted small mb-0">No sales data available</p>
                            @endif
                        </div>
                    </div>

                    <!-- Stock Alerts -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Stock Alerts</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($stockAlerts) && $stockAlerts->count() > 0)
                            @foreach($stockAlerts as $product)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <small class="fw-bold">{{ $product->name }}</small><br>
                                    <small class="text-muted">SKU: {{ $product->sku ?? 'N/A' }}</small>
                                </div>
                                <div class="text-end">
                                    @if($product->stock <= 0) <span class="badge bg-danger">Out</span>
                                        @else
                                        <span class="badge bg-warning">{{ $product->stock }}</span>
                                        @endif
                                </div>
                            </div>
                            @endforeach
                            <a href="{{ route('seller.products.index', ['stock' => 'low']) }}"
                                class="btn btn-sm btn-outline-warning w-100 mt-2">
                                Manage Stock
                            </a>
                            @else
                            <p class="text-muted small mb-0">All products have sufficient stock</p>
                            @endif
                        </div>
                    </div>

                    <!-- Product Status Distribution -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Product Status</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Category Performance Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: @json($categoryData['labels'] ?? []),
        datasets: [{
            label: 'Revenue',
            data: @json($categoryData['revenue'] ?? []),
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Products',
            data: @json($categoryData['products'] ?? []),
            backgroundColor: 'rgba(255, 99, 132, 0.8)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
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

// Product Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($statusData['labels'] ?? []),
        datasets: [{
            data: @json($statusData['data'] ?? []),
            backgroundColor: [
                '#28a745',
                '#6c757d',
                '#ffc107'
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

// Export functions
function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.open(`{{ route('seller.reports.products') }}?${params.toString()}`, '_blank');
}
</script>
@endsection