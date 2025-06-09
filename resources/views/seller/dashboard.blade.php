@extends('layouts.seller')

@section('title', 'Seller Dashboard')

@section('content')
<div class="container py-4">
    @if(!$store)
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-store fa-4x text-primary mb-3"></i>
                    <h2>Welcome to Ambung Emac Seller Center</h2>
                    <p class="text-muted">You haven't set up your store yet. Create your store to start selling.</p>
                </div>
                <a href="{{ route('seller.stores.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i> Create Your Store
                </a>
            </div>
        </div>
    @elseif($store->status === 'pending')
        <div class="alert alert-warning">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <div>
                    <h4 class="alert-heading">Store Approval Pending</h4>
                    <p>Your store is currently under review by our team. This usually takes 1-2 business days.</p>
                    <p class="mb-0">We'll notify you once your store is approved.</p>
                </div>
            </div>
        </div>
    @elseif($store->status === 'rejected')
        <div class="alert alert-danger">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-exclamation-circle fa-2x"></i>
                </div>
                <div>
                    <h4 class="alert-heading">Store Application Rejected</h4>
                    <p>Unfortunately, your store application was not approved.</p>
                    <p><strong>Reason:</strong> {{ $store->rejection_reason }}</p>
                    <hr>
                    <p class="mb-0">
                        <a href="{{ route('seller.stores.edit', $store) }}" class="btn btn-outline-danger">
                            Update Store Information
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Dashboard Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-shopping-bag text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Total Products</h6>
                                <h3 class="mb-0">{{ $stats['total_products'] ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            @if(($stats['product_growth'] ?? 0) > 0)
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-arrow-up me-1"></i>{{ $stats['product_growth'] }}%
                                </span>
                            @elseif(($stats['product_growth'] ?? 0) < 0)
                                <span class="badge bg-danger me-2">
                                    <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['product_growth']) }}%
                                </span>
                            @else
                                <span class="badge bg-secondary me-2">0%</span>
                            @endif
                            <span class="text-muted small">vs last month</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-shopping-cart text-success"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Total Orders</h6>
                                <h3 class="mb-0">{{ $stats['total_orders'] ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            @if(($stats['order_growth'] ?? 0) > 0)
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-arrow-up me-1"></i>{{ $stats['order_growth'] }}%
                                </span>
                            @elseif(($stats['order_growth'] ?? 0) < 0)
                                <span class="badge bg-danger me-2">
                                    <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['order_growth']) }}%
                                </span>
                            @else
                                <span class="badge bg-secondary me-2">0%</span>
                            @endif
                            <span class="text-muted small">vs last month</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-money-bill-wave text-warning"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Revenue</h6>
                                <h3 class="mb-0">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            @if(($stats['revenue_growth'] ?? 0) > 0)
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-arrow-up me-1"></i>{{ $stats['revenue_growth'] }}%
                                </span>
                            @elseif(($stats['revenue_growth'] ?? 0) < 0)
                                <span class="badge bg-danger me-2">
                                    <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['revenue_growth']) }}%
                                </span>
                            @else
                                <span class="badge bg-secondary me-2">0%</span>
                            @endif
                            <span class="text-muted small">vs last month</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-star text-info"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Store Rating</h6>
                                <h3 class="mb-0">{{ number_format($stats['average_rating'] ?? 0, 1) }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="text-warning me-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($stats['average_rating'] ?? 0))
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="text-muted small">{{ $stats['total_reviews'] ?? 0 }} reviews</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <!-- Sales Chart -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Sales Overview</h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active">Week</button>
                                <button type="button" class="btn btn-outline-secondary">Month</button>
                                <button type="button" class="btn btn-outline-secondary">Year</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Order Status -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="orderStatusChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->user->name }}</td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                            <td>
                                                @if($order->status === 'new')
                                                    <span class="badge bg-info">New</span>
                                                @elseif($order->status === 'processing')
                                                    <span class="badge bg-warning">Processing</span>
                                                @elseif($order->status === 'shipped')
                                                    <span class="badge bg-primary">Shipped</span>
                                                @elseif($order->status === 'delivered')
                                                    <span class="badge bg-success">Delivered</span>
                                                @elseif($order->status === 'cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('seller.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No orders yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Products -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Low Stock Products</h5>
                            <a href="{{ route('seller.products.index', ['filter' => 'low_stock']) }}" class="btn btn-sm btn-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($lowStockProducts as $product)
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->image_url ?? asset('images/products/default.jpg') }}" 
                                             class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" 
                                             alt="{{ $product->name }}">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">{{ $product->name }}</h6>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-danger me-2">{{ $product->stock }} left</span>
                                                <span class="text-muted small">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-sm btn-outline-warning">
                                            Update
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center py-4">
                                    <p class="mb-0 text-muted">No low stock products</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@if($store && $store->status === 'approved')
    @section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales',
                    data: {{ json_encode($salesData ?? [0, 0, 0, 0, 0, 0, 0]) }},
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                }
            }
        });
        
        // Order Status Chart
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        const orderStatusChart = new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['New', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
                datasets: [{
                    data: {{ json_encode($orderStatusData ?? [0, 0, 0, 0, 0]) }},
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderWidth: 1
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
    </script>
    @endsection
@endif