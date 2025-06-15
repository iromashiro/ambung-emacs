@extends('layouts.seller')

@section('title', 'Seller Dashboard')

@section('content')
<div class="container-fluid py-4">
    @if(!$store)
    <!-- No Store State -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-store fa-4x text-primary mb-3"></i>
                        <h2 class="h3">Welcome to Ambung Emac Seller Center</h2>
                        <p class="text-muted lead">You haven't set up your store yet. Create your store to start
                            selling.</p>
                    </div>
                    <a href="{{ route('seller.stores.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i> Create Your Store
                    </a>
                </div>
            </div>
        </div>
    </div>

    @elseif($store->status === 'pending')
    <!-- Pending Approval State -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="fas fa-clock fa-3x text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="alert-heading mb-2">Store Approval Pending</h4>
                        <p class="mb-2">Your store is currently under review by our team. This usually takes 1-2
                            business days.</p>
                        <p class="mb-0">We'll notify you once your store is approved and you can start selling.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @elseif($store->status === 'rejected')
    <!-- Rejected State -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="alert-heading mb-2">Store Application Rejected</h4>
                        <p class="mb-2">Unfortunately, your store application was not approved.</p>
                        @if($store->rejection_reason)
                        <p class="mb-3"><strong>Reason:</strong> {{ $store->rejection_reason }}</p>
                        @endif
                        <div>
                            <a href="{{ route('seller.stores.edit', $store) }}" class="btn btn-outline-danger">
                                <i class="fas fa-edit me-2"></i>Update Store Information
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Approved Store - Full Dashboard -->

    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Welcome back, {{ auth()->user()->name }}!</h1>
                    <p class="text-muted mb-0">Here's what's happening with your store today.</p>
                </div>
                <div class="text-end">
                    <div class="text-muted small">{{ now()->format('l, F j, Y') }}</div>
                    <div class="text-primary fw-bold">{{ $store->name }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Products -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-shopping-bag text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Total Products</div>
                            <div class="h4 mb-0">{{ number_format($stats['total_products'] ?? 0) }}</div>
                            @if(($stats['product_growth'] ?? 0) != 0)
                            <div class="small">
                                @if($stats['product_growth'] > 0)
                                <span class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>{{ $stats['product_growth'] }}%
                                </span>
                                @else
                                <span class="text-danger">
                                    <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['product_growth']) }}%
                                </span>
                                @endif
                                <span class="text-muted">vs last month</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-shopping-cart text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Total Orders</div>
                            <div class="h4 mb-0">{{ number_format($stats['total_orders'] ?? 0) }}</div>
                            @if(($stats['order_growth'] ?? 0) != 0)
                            <div class="small">
                                @if($stats['order_growth'] > 0)
                                <span class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>{{ $stats['order_growth'] }}%
                                </span>
                                @else
                                <span class="text-danger">
                                    <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['order_growth']) }}%
                                </span>
                                @endif
                                <span class="text-muted">vs last month</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-money-bill-wave text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Total Revenue</div>
                            <div class="h4 mb-0">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                            @if(($stats['revenue_growth'] ?? 0) != 0)
                            <div class="small">
                                @if($stats['revenue_growth'] > 0)
                                <span class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>{{ $stats['revenue_growth'] }}%
                                </span>
                                @else
                                <span class="text-danger">
                                    <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['revenue_growth']) }}%
                                </span>
                                @endif
                                <span class="text-muted">vs last month</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Store Rating -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-star text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Store Rating</div>
                            <div class="h4 mb-0">{{ number_format($stats['average_rating'] ?? 0, 1) }}</div>
                            <div class="small">
                                <div class="text-warning me-2">
                                    @for($i = 1; $i <= 5; $i++) @if($i <=round($stats['average_rating'] ?? 0)) <i
                                        class="fas fa-star"></i>
                                        @else
                                        <i class="far fa-star"></i>
                                        @endif
                                        @endfor
                                </div>
                                <span class="text-muted">{{ $stats['total_reviews'] ?? 0 }} reviews</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Sales Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Sales Overview</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="salesPeriod" id="week" checked>
                            <label class="btn btn-outline-secondary" for="week">Week</label>

                            <input type="radio" class="btn-check" name="salesPeriod" id="month">
                            <label class="btn btn-outline-secondary" for="month">Month</label>

                            <input type="radio" class="btn-check" name="salesPeriod" id="year">
                            <label class="btn btn-outline-secondary" for="year">Year</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Orders</h5>
                        <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i>View All Orders
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders && $recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Order</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Products</th>
                                    <th class="border-0">Total</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td class="fw-bold">#{{ $order->id }}</td>
                                    <td>
                                        <div>{{ $order->user->name }}</div>
                                        <small class="text-muted">{{ $order->user->email }}</small>
                                    </td>
                                    <td>
                                        @if($order->items && $order->items->count() > 0)
                                        @php
                                        $firstItem = $order->items->first();
                                        $totalItems = $order->items->count();
                                        @endphp
                                        <div>{{ $firstItem->product->name ?? 'Product' }}</div>
                                        @if($totalItems > 1)
                                        <small class="text-muted">+{{ $totalItems - 1 }} more items</small>
                                        @endif
                                        @else
                                        <span class="text-muted">No items</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">Rp {{ number_format($order->total_amount ?: 0, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if($order->status === 'new')
                                        <span class="badge bg-primary">New</span>
                                        @elseif($order->status === 'accepted')
                                        <span class="badge bg-info">Accepted</span>
                                        @elseif($order->status === 'dispatched')
                                        <span class="badge bg-warning">Dispatched</span>
                                        @elseif($order->status === 'delivered')
                                        <span class="badge bg-success">Delivered</span>
                                        @elseif($order->status === 'canceled')
                                        <span class="badge bg-danger">Canceled</span>
                                        @else
                                        <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $order->created_at->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('seller.orders.show', $order) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No orders yet</h6>
                        <p class="text-muted mb-0">Orders will appear here once customers start purchasing your
                            products.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Low Stock Alert</h5>
                        <a href="{{ route('seller.products.index') }}?filter=low_stock"
                            class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($lowStockProducts && $lowStockProducts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($lowStockProducts as $product)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <img src="{{ $product->image_url ?? asset('images/products/default.jpg') }}"
                                        class="rounded" style="width: 48px; height: 48px; object-fit: cover;"
                                        alt="{{ $product->name }}">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ Str::limit($product->name, 30) }}</h6>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-danger me-2">{{ $product->stock }} left</span>
                                        <small class="text-muted">Rp
                                            {{ number_format($product->price, 0, ',', '.') }}</small>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('seller.products.edit', $product) }}"
                                        class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h6 class="text-muted">All products well stocked</h6>
                        <p class="text-muted mb-0">No products are running low on inventory.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@if($store && $store->status === 'approved')
@push('styles')
<style>
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Prepare data with proper fallbacks
    let salesData = [];
    let orderStatusData = [];

    try {
        @if(isset($salesData) && is_array($salesData))
            salesData = @json($salesData);
        @else
            salesData = [0, 0, 0, 0, 0, 0, 0];
        @endif

        @if(isset($orderStatusData) && is_array($orderStatusData))
            orderStatusData = @json($orderStatusData);
        @else
            orderStatusData = [0, 0, 0, 0, 0];
        @endif
    } catch (e) {
        console.error('Error parsing chart data:', e);
        salesData = [0, 0, 0, 0, 0, 0, 0];
        orderStatusData = [0, 0, 0, 0, 0];
    }

    // Validate data arrays
    if (!Array.isArray(salesData) || salesData.length !== 7) {
        salesData = [0, 0, 0, 0, 0, 0, 0];
    }

    if (!Array.isArray(orderStatusData) || orderStatusData.length !== 5) {
        orderStatusData = [0, 0, 0, 0, 0];
    }

    console.log('Sales Data:', salesData);
    console.log('Order Status Data:', orderStatusData);

    // Sales Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        try {
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Sales (Rp)',
                        data: salesData,
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw || 0;
                                    return 'Sales: Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (value === 0) return 'Rp 0';
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                        notation: 'compact',
                                        compactDisplay: 'short'
                                    }).format(value);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
            console.log('Sales chart created successfully');
        } catch (e) {
            console.error('Error creating sales chart:', e);
        }
    } else {
        console.error('Sales chart canvas not found');
    }

    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart');
    if (orderStatusCtx) {
        try {
            const orderStatusChart = new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['New', 'Accepted', 'Dispatched', 'Delivered', 'Canceled'],
                    datasets: [{
                        data: orderStatusData,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',   // New - Blue
                            'rgba(23, 162, 184, 0.8)',   // Accepted - Cyan
                            'rgba(255, 193, 7, 0.8)',    // Dispatched - Yellow
                            'rgba(40, 167, 69, 0.8)',    // Delivered - Green
                            'rgba(220, 53, 69, 0.8)'     // Canceled - Red
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(23, 162, 184, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => (a || 0) + (b || 0), 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} orders (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
            console.log('Order status chart created successfully');
        } catch (e) {
            console.error('Error creating order status chart:', e);
        }
    } else {
        console.error('Order status chart canvas not found');
    }

    // Period selector functionality
    const periodRadios = document.querySelectorAll('input[name="salesPeriod"]');
    if (periodRadios.length > 0) {
        periodRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                console.log('Period changed to:', this.id);
                // Future implementation for dynamic data loading
                loadSalesDataByPeriod(this.id);
            });
        });
    }
});

// Function to load sales data by period (future implementation)
function loadSalesDataByPeriod(period) {
    console.log('Loading sales data for period:', period);

    // Show loading state
    const salesChart = Chart.getChart('salesChart');
    if (salesChart) {
        // You can implement loading state here
        console.log('Chart found, ready for data update');
    }

    // Future AJAX implementation
    /*
    fetch(`/seller/dashboard/sales-data?period=${period}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (salesChart && data.salesData) {
            salesChart.data.datasets[0].data = data.salesData;
            salesChart.update();
        }
    })
    .catch(error => {
        console.error('Error loading sales data:', error);
    });
    */
}
</script>
@endpush
@endif