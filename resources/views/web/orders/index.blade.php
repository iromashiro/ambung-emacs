{{-- resources/views/web/orders/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                    <a href="{{ route('profile.addresses') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt me-2"></i> My Addresses
                    </a>
                    <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action active">
                        <i class="fas fa-shopping-bag me-2"></i> My Orders
                    </a>
                    @if(auth()->user()->hasRole('seller'))
                    <a href="{{ route('seller.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-store me-2"></i> Seller Dashboard
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Orders</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Order Status Filter -->
                    <div class="mb-4">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link {{ !$status ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                    All Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}"
                                    href="{{ route('orders.index', ['status' => 'pending']) }}">
                                    Pending
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'processing' ? 'active' : '' }}"
                                    href="{{ route('orders.index', ['status' => 'processing']) }}">
                                    Processing
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'dispatched' ? 'active' : '' }}"
                                    href="{{ route('orders.index', ['status' => 'dispatched']) }}">
                                    Dispatched
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'delivered' ? 'active' : '' }}"
                                    href="{{ route('orders.index', ['status' => 'delivered']) }}">
                                    Delivered
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'canceled' ? 'active' : '' }}"
                                    href="{{ route('orders.index', ['status' => 'canceled']) }}">
                                    Canceled
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items_count ?? $order->items->count() }} item(s)</td>
                                    <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status_color }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                        <h5>No orders found</h5>
                        <p class="text-muted">
                            @if($status)
                            You don't have any {{ $status }} orders.
                            @else
                            You haven't placed any orders yet.
                            @endif
                        </p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary mt-2">
                            Start Shopping
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection