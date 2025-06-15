@extends('layouts.seller')

@section('title', 'Canceled Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Canceled Orders</h1>
                    <p class="text-muted">Orders that have been canceled</p>
                </div>
                <div>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> All Orders
                    </a>
                </div>
            </div>

            @if(isset($orders) && $orders->count() > 0)
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
                                    <th>Canceled Date</th>
                                    <th>Reason</th>
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
                                        <strong class="text-danger">Rp {{ number_format($order->total) }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $order->canceled_at ? $order->canceled_at->format('M d, Y H:i') : $order->updated_at->format('M d, Y H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($order->cancellation_reason)
                                        <small class="text-muted">{{ $order->cancellation_reason }}</small>
                                        @else
                                        <small class="text-muted">No reason provided</small>
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
                    <i class="fas fa-times-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Canceled Orders</h5>
                    <p class="text-muted">You don't have any canceled orders.</p>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-1"></i> View All Orders
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection