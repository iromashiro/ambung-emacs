@extends('layouts.seller')

@section('title', 'Manage Orders')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manage Orders</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportOrders()">
                <i class="fas fa-download me-1"></i> Export
            </button>
            <button class="btn btn-primary" onclick="refreshOrders()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Order Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                            <i class="fas fa-shopping-bag text-info fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">New Orders</h6>
                            <h3 class="mb-0">{{ $orderStats['new'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                            <i class="fas fa-cog text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Processing</h6>
                            <h3 class="mb-0">{{ $orderStats['processing'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                            <i class="fas fa-shipping-fast text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Shipped</h6>
                            <h3 class="mb-0">{{ $orderStats['shipped'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Delivered</h6>
                            <h3 class="mb-0">{{ $orderStats['delivered'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('seller.orders.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search orders..."
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing
                        </option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2">
                    <select class="form-select" name="sort">
                        <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Newest
                            First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="amount_high" {{ request('sort') == 'amount_high' ? 'selected' : '' }}>Highest
                            Amount</option>
                        <option value="amount_low" {{ request('sort') == 'amount_low' ? 'selected' : '' }}>Lowest Amount
                        </option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(count($orders) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
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
                            <td>
                                <a href="{{ route('seller.orders.show', $order) }}"
                                    class="text-decoration-none fw-bold">
                                    #{{ $order->id }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $order->user->avatar_url ?? asset('images/avatar-default.png') }}"
                                        alt="{{ $order->user->name }}" class="rounded-circle me-2"
                                        style="width: 32px; height: 32px;">
                                    <div>
                                        <div class="fw-medium">{{ $order->user->name }}</div>
                                        <small class="text-muted">{{ $order->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $order->created_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                            </td>
                            <td>{{ $order->items_count }} items</td>
                            <td>@currency($order->total)</td>
                            <td>
                                @if($order->status === 'new')
                                <span class="badge bg-info">New</span>
                                @elseif($order->status === 'processing')
                                <span class="badge bg-primary">Processing</span>
                                @elseif($order->status === 'shipped')
                                <span class="badge bg-warning">Shipped</span>
                                @elseif($order->status === 'delivered')
                                <span class="badge bg-success">Delivered</span>
                                @elseif($order->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('seller.orders.show', $order) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($order->status === 'new')
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                        onclick="updateOrderStatus({{ $order->id }}, 'processing')">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    @elseif($order->status === 'processing')
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                        onclick="updateOrderStatus({{ $order->id }}, 'shipped')">
                                        <i class="fas fa-shipping-fast"></i>
                                    </button>
                                    @elseif($order->status === 'shipped')
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                        onclick="updateOrderStatus({{ $order->id }}, 'delivered')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif

                                    @if(in_array($order->status, ['new', 'processing']))
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="updateOrderStatus({{ $order->id }}, 'cancelled')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $orders->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                <h3>No Orders Found</h3>
                <p class="text-muted mb-4">You haven't received any orders yet or no orders match your filter criteria.
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    async function updateOrderStatus(orderId, status) {
        if (!confirm(`Are you sure you want to update this order status to ${status}?`)) {
            return;
        }

        try {
            const response = await fetch(`/seller/orders/${orderId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to update order status');
            }
        } catch (error) {
            console.error('Error updating order status:', error);
            alert('An error occurred. Please try again.');
        }
    }

    function exportOrders() {
        const params = new URLSearchParams(window.location.search);
        window.open(`{{ route('seller.reports.export', 'orders') }}?${params.toString()}`, '_blank');
    }

    function refreshOrders() {
        window.location.reload();
    }
</script>
@endpush
@endsection