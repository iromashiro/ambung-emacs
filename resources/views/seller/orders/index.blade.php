@extends('layouts.seller')

@section('title', 'Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Orders</h1>
                    <p class="text-muted">Manage your customer orders</p>
                </div>
            </div>

            <!-- Order Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info mb-1">{{ $stats['new'] ?? 0 }}</h3>
                            <small class="text-muted">New Orders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-1">{{ $stats['processing'] ?? 0 }}</h3>
                            <small class="text-muted">Processing</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success mb-1">{{ $stats['completed'] ?? 0 }}</h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger mb-1">{{ $stats['canceled'] ?? 0 }}</h3>
                            <small class="text-muted">Canceled</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Tabs -->
            <div class="card mb-4">
                <div class="card-body">
                    <ul class="nav nav-pills mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.orders.index') && !request('status') ? 'active' : '' }}"
                                href="{{ route('seller.orders.index') }}">
                                All Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.orders.new') ? 'active' : '' }}"
                                href="{{ route('seller.orders.new') }}">
                                New
                                @if(isset($stats['new']) && $stats['new'] > 0)
                                <span class="badge bg-danger ms-1">{{ $stats['new'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.orders.processing') ? 'active' : '' }}"
                                href="{{ route('seller.orders.processing') }}">
                                Processing
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.orders.completed') ? 'active' : '' }}"
                                href="{{ route('seller.orders.completed') }}">
                                Completed
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.orders.canceled') ? 'active' : '' }}"
                                href="{{ route('seller.orders.canceled') }}">
                                Canceled
                            </a>
                        </li>
                    </ul>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('seller.orders.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Search orders..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>
                                        Confirmed</option>
                                    <option value="processing"
                                        {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>
                                        Shipped</option>
                                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>
                                        Delivered</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                    <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>
                                        Canceled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="date_from"
                                    value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    @if(isset($orders) && $orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Products</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>#{{ $order->order_number }}</strong><br>
                                            <small class="text-muted">{{ $order->payment_method ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->user->name ?? 'Guest' }}</strong><br>
                                            <small
                                                class="text-muted">{{ $order->user->email ?? $order->customer_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            @if($order->items && $order->items->count() > 0)
                                            @foreach($order->items->take(2) as $item)
                                            <small class="d-block">{{ $item->product->name ?? 'Product' }}
                                                ({{ $item->quantity }}x)</small>
                                            @endforeach
                                            @if($order->items->count() > 2)
                                            <small class="text-muted">+{{ $order->items->count() - 2 }} more</small>
                                            @endif
                                            @else
                                            <small class="text-muted">No items</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $order->formatted_total }}</strong>
                                    </td>
                                    <td>
                                        @php
                                        $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'processing' => 'primary',
                                        'shipped' => 'info',
                                        'delivered' => 'success',
                                        'completed' => 'success',
                                        'canceled' => 'danger'
                                        ];
                                        $color = $statusColors[$order->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst($order->status) }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $order->created_at->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
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
                                                        href="{{ route('seller.orders.show', $order) }}">
                                                        <i class="fas fa-eye me-1"></i> View Details
                                                    </a>
                                                </li>
                                                @if($order->status === 'pending')
                                                <li>
                                                    <form action="{{ route('seller.orders.status.update', $order) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-check me-1"></i> Confirm Order
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if(in_array($order->status, ['confirmed', 'processing']))
                                                <li>
                                                    <form action="{{ route('seller.orders.status.update', $order) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status"
                                                            value="{{ $order->status === 'confirmed' ? 'processing' : 'shipped' }}">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-arrow-right me-1"></i>
                                                            {{ $order->status === 'confirmed' ? 'Start Processing' : 'Mark as Shipped' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if($order->status === 'shipped')
                                                <li>
                                                    <form action="{{ route('seller.orders.status.update', $order) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="delivered">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-truck me-1"></i> Mark as Delivered
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if(in_array($order->status, ['pending', 'confirmed']))
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('seller.orders.status.update', $order) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="canceled">
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-times me-1"></i> Cancel Order
                                                        </button>
                                                    </form>
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
                    @if(method_exists($orders, 'links'))
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Orders Found</h5>
                        <p class="text-muted">You don't have any orders yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection