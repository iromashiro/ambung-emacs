{{-- resources/views/web/orders/show.blade.php --}}
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
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Order Information</h6>
                            <p class="mb-1">
                                <strong>Order Date:</strong> {{ $order->created_at->format('F d, Y h:i A') }}
                            </p>
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $order->status_color }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </p>
                            <p class="mb-1">
                                <strong>Payment Method:</strong> Cash on Delivery (COD)
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Shipping Address</h6>
                            <p class="mb-0">{{ $order->shipping_address }}</p>
                            <p class="mb-0">Phone: {{ $order->phone }}</p>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product && $item->product->image_path)
                                            <img src="{{ Storage::url($item->product->image_path) }}"
                                                alt="{{ $item->product_name }}" width="50" class="me-3">
                                            @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-3"
                                                style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                <small class="text-muted">
                                                    {{ $item->product ? $item->product->store->name : 'Unknown Store' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                    <td class="text-end"><strong>Rp
                                            {{ number_format($order->total, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($order->notes)
                    <div class="mt-4">
                        <h6 class="fw-bold">Order Notes</h6>
                        <p class="mb-0">{{ $order->notes }}</p>
                    </div>
                    @endif

                    <div class="mt-4">
                        <h6 class="fw-bold">Order Timeline</h6>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Placed</h6>
                                    <small>{{ $order->created_at->format('F d, Y h:i A') }}</small>
                                </div>
                            </div>

                            @if($order->status != 'pending' && $order->status != 'canceled')
                            <div class="timeline-item">
                                <div
                                    class="timeline-marker {{ $order->status != 'pending' ? 'bg-success' : 'bg-secondary' }}">
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Processing</h6>
                                    <small>{{ $order->updated_at->format('F d, Y h:i A') }}</small>
                                </div>
                            </div>
                            @endif

                            @if($order->status == 'dispatched' || $order->status == 'delivered')
                            <div class="timeline-item">
                                <div
                                    class="timeline-marker {{ $order->status == 'dispatched' || $order->status == 'delivered' ? 'bg-success' : 'bg-secondary' }}">
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Dispatched</h6>
                                    <small>{{ $order->updated_at->format('F d, Y h:i A') }}</small>
                                </div>
                            </div>
                            @endif

                            @if($order->status == 'delivered')
                            <div class="timeline-item">
                                <div
                                    class="timeline-marker {{ $order->status == 'delivered' ? 'bg-success' : 'bg-secondary' }}">
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Delivered</h6>
                                    <small>{{ $order->updated_at->format('F d, Y h:i A') }}</small>
                                </div>
                            </div>
                            @endif

                            @if($order->status == 'canceled')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Canceled</h6>
                                    <small>{{ $order->updated_at->format('F d, Y h:i A') }}</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        @if($order->status == 'pending')
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="me-2">
                            @csrf
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-times me-1"></i> Cancel Order
                            </button>
                        </form>
                        @endif

                        @if($order->status == 'dispatched')
                        <form action="{{ route('orders.confirm-delivery', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Confirm Delivery
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
        margin-top: 20px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -30px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        top: 5px;
    }

    .timeline:before {
        content: '';
        position: absolute;
        left: -23px;
        width: 2px;
        height: 100%;
        background-color: #e9ecef;
    }
</style>
@endsection