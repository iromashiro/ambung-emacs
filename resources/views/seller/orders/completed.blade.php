@extends('layouts.seller')

@section('title', 'Completed Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Completed Orders</h1>
                    <p class="text-muted">Successfully delivered orders</p>
                </div>
                <div>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> All Orders
                    </a>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success mb-1">{{ $stats['total_completed'] ?? 0 }}</h3>
                            <small class="text-muted">Total Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary mb-1">Rp {{ number_format($stats['total_revenue'] ?? 0) }}</h3>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info mb-1">{{ $stats['this_month'] ?? 0 }}</h3>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-1">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</h3>
                            <small class="text-muted">Avg Rating</small>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($orders) && $orders->count() > 0)
            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Completed Date</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>#{{ $order->order_number }}</strong><br>
                                            <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->user->name ?? $order->customer_name ?? 'Guest' }}</strong><br>
                                            <small
                                                class="text-muted">{{ $order->user->email ?? $order->customer_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $order->items->sum('quantity') ?? 0 }}
                                            items</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">Rp {{ number_format($order->total) }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $order->completed_at ? $order->completed_at->format('M d, Y') : $order->updated_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        @if(isset($order->rating) && $order->rating > 0)
                                        <div class="d-flex align-items-center">
                                            <span class="me-1">{{ $order->rating }}</span>
                                            @for($i = 1; $i <= 5; $i++) <i
                                                class="fas fa-star {{ $i <= $order->rating ? 'text-warning' : 'text-muted' }}"
                                                style="font-size: 12px;"></i>
                                                @endfor
                                        </div>
                                        @else
                                        <small class="text-muted">No rating</small>
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
                                                        href="{{ route('seller.orders.show', $order) }}">
                                                        <i class="fas fa-eye me-1"></i> View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="mailto:{{ $order->user->email ?? $order->customer_email }}">
                                                        <i class="fas fa-envelope me-1"></i> Contact Customer
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="printOrder('{{ $order->id }}')">
                                                        <i class="fas fa-print me-1"></i> Print Invoice
                                                    </a>
                                                </li>
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
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Completed Orders</h5>
                    <p class="text-muted">You don't have any completed orders yet.</p>
                    <a href="{{ route('seller.orders.processing') }}" class="btn btn-primary">
                        <i class="fas fa-cog me-1"></i> Check Processing Orders
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    function printOrder(orderId) {
    // Open order details in new window for printing
    window.open(`/seller/orders/${orderId}?print=1`, '_blank');
}
</script>
@endsection