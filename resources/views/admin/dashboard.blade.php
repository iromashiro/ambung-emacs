@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-0">Total Users</h6>
                            <h3 class="mb-0">{{ $stats['total_users'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        @if(($stats['user_growth'] ?? 0) > 0)
                            <span class="badge bg-success me-2">
                                <i class="fas fa-arrow-up me-1"></i>{{ $stats['user_growth'] }}%
                            </span>
                        @elseif(($stats['user_growth'] ?? 0) < 0)
                            <span class="badge bg-danger me-2">
                                <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['user_growth']) }}%
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
                            <i class="fas fa-store text-success"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-0">Total Stores</h6>
                            <h3 class="mb-0">{{ $stats['total_stores'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        @if(($stats['store_growth'] ?? 0) > 0)
                            <span class="badge bg-success me-2">
                                <i class="fas fa-arrow-up me-1"></i>{{ $stats['store_growth'] }}%
                            </span>
                        @elseif(($stats['store_growth'] ?? 0) < 0)
                            <span class="badge bg-danger me-2">
                                <i class="fas fa-arrow-down me-1"></i>{{ abs($stats['store_growth']) }}%
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
                            <i class="fas fa-shopping-cart text-warning"></i>
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
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-money-bill-wave text-info"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-0">Total Revenue</h6>
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
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Revenue Overview</h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active">Week</button>
                            <button type="button" class="btn btn-outline-secondary">Month</button>
                            <button type="button" class="btn btn-outline-secondary">Year</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- User Registration -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">User Registration</h5>
                </div>
                <div class="card-body">
                    <canvas id="userRegistrationChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Pending Store Approvals -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pending Store Approvals</h5>
                        <a href="{{ route('admin.stores.pending') }}" class="btn btn-sm btn-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Store Name</th>
                                    <th>Owner</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingStores as $store)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $store->logo_url ?? asset('images/stores/default.jpg') }}" 
                                                     class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;" 
                                                     alt="{{ $store->name }}">
                                                <span>{{ $store->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $store->user->name }}</td>
                                        <td>{{ $store->created_at->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.stores.review', $store) }}" class="btn btn-sm btn-outline-primary">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No pending store approvals</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">
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
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No recent orders</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Top Selling Products -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Selling Products</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Store</th>
                                    <th>Price</th>
                                    <th>Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $product->image_url ?? asset('images/products/default.jpg') }}" 
                                                     class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" 
                                                     alt="{{ $product->name }}">
                                                <span>{{ $product->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $product->store->name }}</td>
                                        <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                        <td>{{ $product->sold_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Stores -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Performing Stores</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Store</th>
                                    <th>Products</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topStores as $store)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $store->logo_url ?? asset('images/stores/default.jpg') }}" 
                                                     class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;" 
                                                     alt="{{ $store->name }}">
                                                <span>{{ $store->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $store->products_count }}</td>
                                        <td>{{ $store->orders_count }}</td>
                                        <td>Rp {{ number_format($store->revenue, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Revenue',
                data: {{ json_encode($revenueData ?? [0, 0, 0, 0, 0, 0, 0]) }},
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                tension: 0.3
            }, {
                label: 'Orders',
                data: {{ json_encode($orderData ?? [0, 0, 0, 0, 0, 0, 0]) }},
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                borderColor: 'rgba(0, 123, 255, 1)',
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
                            if (context.dataset.label === 'Revenue') {
                                return 'Revenue: Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            } else {
                                return 'Orders: ' + context.raw;
                            }
                        }
                    }
                }
            }
        }
    });
    
    // User Registration Chart
    const userRegistrationCtx = document.getElementById('userRegistrationChart').getContext('2d');
    const userRegistrationChart = new Chart(userRegistrationCtx, {
        type: 'doughnut',
        data: {
            labels: ['Buyers', 'Sellers', 'Admins'],
            datasets: [{
                data: {{ json_encode($userRoleData ?? [0, 0, 0]) }},
                backgroundColor: [
                    'rgba(0, 123, 255, 0.7)',
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