@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Order #{{ $order->id }}</h1>
        <span
            class="badge bg-{{ $order->status === 'new' ? 'primary' : ($order->status === 'delivered' ? 'success' : ($order->status === 'canceled' ? 'danger' : 'warning')) }} fs-6">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    @foreach($order->items as $item)
                    <div class="d-flex align-items-center mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                        <div class="me-3">
                            @if($item->product && $item->product->image)
                            <img src="{{ Storage::url($item->product->image) }}" class="rounded"
                                style="width: 60px; height: 60px; object-fit: cover;" alt="{{ $item->product->name }}">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $item->product->name ?? 'Product no longer available' }}</h6>
                            <div class="text-muted small">
                                Rp {{ number_format($item->price, 0, ',', '.') }} x {{ $item->quantity }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">
                                Rp {{ number_format($item->total, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Address:</strong></p>
                    <p class="mb-2">{{ $order->shipping_address }}</p>
                    <p class="mb-2"><strong>Phone:</strong> {{ $order->phone }}</p>
                    @if($order->notes)
                    <p class="mb-0"><strong>Notes:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-primary me-2"></i>
                        <span>Order placed on {{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>

                    @if($order->status === 'accepted')
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-success me-2"></i>
                        <span>Order accepted</span>
                    </div>
                    @endif

                    @if($order->status === 'dispatched')
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-info me-2"></i>
                        <span>Order dispatched</span>
                    </div>
                    @endif

                    @if($order->status === 'delivered')
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-success me-2"></i>
                        <span>Order delivered</span>
                    </div>
                    @endif

                    @if($order->status === 'canceled')
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-danger me-2"></i>
                        <span>Order canceled</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('buyer.orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Orders
        </a>

        @if($order->canBeCanceled())
        <form action="{{ route('buyer.orders.cancel', $order->id) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('Are you sure you want to cancel this order?')">
                Cancel Order
            </button>
        </form>
        @endif

        @if($order->status === 'dispatched')
        <form action="{{ route('buyer.orders.confirm', $order->id) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success"
                onclick="return confirm('Confirm that you have received this order?')">
                Confirm Delivery
            </button>
        </form>
        @endif
    </div>
</div>
@endsection