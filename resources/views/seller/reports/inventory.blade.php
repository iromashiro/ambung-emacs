@extends('layouts.seller')

@section('title', 'Inventory Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Inventory Report</h1>
                    <p class="text-muted">Monitor your stock levels and inventory value</p>
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
                    <form method="GET" action="{{ route('seller.reports.inventory') }}">
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
                                <label for="stock_level" class="form-label">Stock Level</label>
                                <select class="form-select" id="stock_level" name="stock_level">
                                    <option value="">All Levels</option>
                                    <option value="out_of_stock"
                                        {{ request('stock_level') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock
                                    </option>
                                    <option value="low_stock"
                                        {{ request('stock_level') === 'low_stock' ? 'selected' : '' }}>Low Stock (≤10)
                                    </option>
                                    <option value="medium_stock"
                                        {{ request('stock_level') === 'medium_stock' ? 'selected' : '' }}>Medium Stock
                                        (11-50)</option>
                                    <option value="high_stock"
                                        {{ request('stock_level') === 'high_stock' ? 'selected' : '' }}>High Stock (>50)
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sort_by" class="form-label">Sort By</label>
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="stock_asc"
                                        {{ request('sort_by') === 'stock_asc' ? 'selected' : '' }}>Stock (Low to High)
                                    </option>
                                    <option value="stock_desc"
                                        {{ request('sort_by') === 'stock_desc' ? 'selected' : '' }}>Stock (High to Low)
                                    </option>
                                    <option value="value_desc"
                                        {{ request('sort_by') === 'value_desc' ? 'selected' : '' }}>Value (High to Low)
                                    </option>
                                    <option value="name_asc" {{ request('sort_by') === 'name_asc' ? 'selected' : '' }}>
                                        Name (A-Z)</option>
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
                            <h3 class="text-primary mb-1">Rp {{ number_format($summary['total_inventory_value'] ?? 0) }}
                            </h3>
                            <small class="text-muted">Total Inventory Value</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info mb-1">{{ $summary['total_items'] ?? 0 }}</h3>
                            <small class="text-muted">Total Items in Stock</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-1">{{ $summary['low_stock_items'] ?? 0 }}</h3>
                            <small class="text-muted">Low Stock Items</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger mb-1">{{ $summary['out_of_stock_items'] ?? 0 }}</h3>
                            <small class="text-muted">Out of Stock</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Inventory Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Inventory Details</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($products) && $products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Stock</th>
                                            <th>Unit Price</th>
                                            <th>Total Value</th>
                                            <th>Status</th>
                                            <th>Actions</th>
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
                                                            class="text-muted">{{ Str::limit($product->description, 30) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $product->sku ?? 'N/A' }}</code>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-light text-dark">{{ $product->category->name ?? 'No Category' }}</span>
                                            </td>
                                            <td>
                                                @if($product->stock <= 0) <span class="badge bg-danger">0</span>
                                                    @elseif($product->stock <= 10) <span class="badge bg-warning">
                                                        {{ $product->stock }}</span>
                                                        @elseif($product->stock <= 50) <span class="badge bg-info">
                                                            {{ $product->stock }}</span>
                                                            @else
                                                            <span class="badge bg-success">{{ $product->stock }}</span>
                                                            @endif
                                            </td>
                                            <td>Rp {{ number_format($product->price) }}</td>
                                            <td><strong>Rp
                                                    {{ number_format($product->price * $product->stock) }}</strong></td>
                                            <td>
                                                @if($product->stock <= 0) <span class="badge bg-danger">Out of
                                                    Stock</span>
                                                    @elseif($product->stock <= 10) <span class="badge bg-warning">Low
                                                        Stock</span>
                                                        @else
                                                        <span class="badge bg-success">In Stock</span>
                                                        @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('seller.products.edit', $product) }}">
                                                                <i class="fas fa-edit me-1"></i> Update Stock
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('seller.products.show', $product) }}">
                                                                <i class="fas fa-eye me-1"></i> View Product
                                                            </a>
                                                        </li>
                                                        @if($product->stock <= 10) <li>
                                                            <hr class="dropdown-divider">
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-warning" href="#"
                                                                    onclick="restockAlert('{{ $product->name }}')">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                                    Restock Alert
                                                                </a>
                                                            </li>
                                                            @endif
                                                    </ul>
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
                                <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Inventory Data</h5>
                                <p class="text-muted">No products match your current filters.</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stock Movement Chart -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Stock Movement Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="stockChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Critical Stock Alerts -->
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i> Critical Stock Alerts
                            </h6>
                        </div>
                        <div class="card-body">
                            @if(isset($criticalStock) && $criticalStock->count() > 0)
                            @foreach($criticalStock as $product)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                <div>
                                    <h6 class="mb-0 text-danger">{{ $product->name }}</h6>
                                    <small class="text-muted">SKU: {{ $product->sku ?? 'N/A' }}</small>
                                </div>
                                <div class="text-end">
                                    @if($product->stock <= 0) <span class="badge bg-danger">OUT</span>
                                        @else
                                        <span class="badge bg-warning">{{ $product->stock }}</span>
                                        @endif
                                </div>
                            </div>
                            @endforeach
                            <a href="{{ route('seller.products.index', ['stock' => 'low']) }}"
                                class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-cog me-1"></i> Manage Critical Stock
                            </a>
                            @else
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                <p class="text-muted mb-0">No critical stock issues</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Inventory Value by Category -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Value by Category</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryValueChart" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Stock Level Distribution -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Stock Level Distribution</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($stockDistribution))
                            @foreach($stockDistribution as $level)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-{{ $level['color'] }}">{{ $level['label'] }}</span>
                                <div>
                                    <span class="badge bg-{{ $level['color'] }}">{{ $level['count'] }}</span>
                                    <small class="text-muted">({{ $level['percentage'] }}%)</small>
                                </div>
                            </div>
                            <div class="progress mb-3" style="height: 4px;">
                                <div class="progress-bar bg-{{ $level['color'] }}"
                                    style="width: {{ $level['percentage'] }}%"></div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('seller.products.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add New Product
                                </a>
                                <a href="{{ route('seller.products.index', ['stock' => 'low']) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-exclamation-triangle me-1"></i> View Low Stock
                                </a>
                                <a href="{{ route('seller.products.index', ['stock' => 'out']) }}"
                                    class="btn btn-danger btn-sm">
                                    <i class="fas fa-times-circle me-1"></i> View Out of Stock
                                </a>
                                <button class="btn btn-outline-secondary btn-sm" onclick="bulkUpdateStock()">
                                    <i class="fas fa-edit me-1"></i> Bulk Update Stock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Stock Movement Chart
const stockCtx = document.getElementById('stockChart').getContext('2d');
const stockChart = new Chart(stockCtx, {
    type: 'line',
    data: {
        labels: @json($stockMovement['labels'] ?? []),
        datasets: [{
            label: 'Stock In',
            data: @json($stockMovement['stock_in'] ?? []),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'Stock Out',
            data: @json($stockMovement['stock_out'] ?? []),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Category Value Chart
const categoryValueCtx = document.getElementById('categoryValueChart').getContext('2d');
const categoryValueChart = new Chart(categoryValueCtx, {
    type: 'doughnut',
    data: {
        labels: @json($categoryValue['labels'] ?? []),
        datasets: [{
            data: @json($categoryValue['data'] ?? []),
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
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

// Functions
function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.open(`{{ route('seller.reports.inventory') }}?${params.toString()}`, '_blank');
}

function restockAlert(productName) {
    alert(`Restock alert set for: ${productName}\n\nYou will be notified when this product needs restocking.`);
}

function bulkUpdateStock() {
    alert('Bulk stock update feature coming soon!');
}
</script>
@endsection