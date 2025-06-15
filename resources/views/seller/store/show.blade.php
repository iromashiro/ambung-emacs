@extends('layouts.seller')

@section('title', 'My Store')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Store</h1>
                    <p class="text-muted">Manage your store information and settings</p>
                </div>
                <div>
                    <a href="{{ route('seller.store.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Store
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Store Banner -->
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            @if($store->banner)
                            <img src="{{ asset('storage/' . $store->banner) }}" class="img-fluid w-100"
                                style="height: 200px; object-fit: cover;" alt="Store Banner">
                            @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p>No banner uploaded</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Store Information -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    @if($store->logo)
                                    <img src="{{ asset('storage/' . $store->logo) }}" class="img-fluid rounded-circle"
                                        style="width: 100px; height: 100px; object-fit: cover;" alt="Store Logo">
                                    @else
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 100px; height: 100px; margin: 0 auto;">
                                        <i class="fas fa-store fa-2x text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <h4 class="mb-2">{{ $store->name }}</h4>
                                    <p class="text-muted mb-3">{{ $store->description }}</p>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <i class="fas fa-phone text-primary me-2"></i>
                                                <strong>Phone:</strong> {{ $store->phone }}
                                            </div>
                                            @if($store->email)
                                            <div class="mb-2">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                <strong>Email:</strong> {{ $store->email }}
                                            </div>
                                            @endif
                                            <div class="mb-2">
                                                <i class="fas fa-calendar text-primary me-2"></i>
                                                <strong>Joined:</strong> {{ $store->created_at->format('M d, Y') }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                <strong>Address:</strong>
                                            </div>
                                            <p class="text-muted ms-4">{{ $store->address }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Store Statistics -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Store Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="border-end">
                                        <h3 class="text-primary mb-1">{{ $stats['total_products'] ?? 0 }}</h3>
                                        <small class="text-muted">Total Products</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border-end">
                                        <h3 class="text-success mb-1">{{ $stats['total_orders'] ?? 0 }}</h3>
                                        <small class="text-muted">Total Orders</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border-end">
                                        <h3 class="text-info mb-1">{{ number_format($stats['total_revenue'] ?? 0) }}
                                        </h3>
                                        <small class="text-muted">Total Revenue</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h3 class="text-warning mb-1">{{ number_format($stats['average_rating'] ?? 0, 1) }}
                                    </h3>
                                    <small class="text-muted">Average Rating</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Store Status -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Store Status</h6>
                        </div>
                        <div class="card-body">
                            @if($store->status === 'approved')
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Active</strong><br>
                                <small>Your store is active and visible to customers</small>
                            </div>
                            @elseif($store->status === 'pending')
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Under Review</strong><br>
                                <small>Your store is being reviewed by admin</small>
                            </div>
                            @elseif($store->status === 'rejected')
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong>Rejected</strong><br>
                                <small>Please update your store information</small>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($store->status === 'approved')
                                <a href="{{ route('seller.products.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add Product
                                </a>
                                <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-shopping-bag me-1"></i> View Orders
                                </a>
                                <a href="{{ route('seller.reports.sales') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-chart-line me-1"></i> Sales Report
                                </a>
                                @endif
                                <a href="{{ route('seller.store.edit') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-edit me-1"></i> Edit Store
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Recent Activity</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($recentActivities) && $recentActivities->count() > 0)
                            @foreach($recentActivities as $activity)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>
                                <small>{{ $activity->description }}</small>
                            </div>
                            @endforeach
                            @else
                            <p class="text-muted small mb-0">No recent activity</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection