@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">My Orders</h1>
    </div>

    @if($orders->count() > 0)
    <div class="row">
        @foreach($orders as $order)
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title">Order #{{ $order->id }}</h5>
                            <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        <span
                            class="badge bg-{{ $order->status === 'new' ? 'primary' : ($order->status === 'delivered' ? 'success' : ($order->status === 'canceled' ? 'danger' : 'warning')) }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <p class="mb-2">
                                <strong>Items:</strong> {{ $order->items->count() }} items<br>
                                <strong>Total:</strong> Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </p>

                            <!-- Show first few items -->
                            @foreach($order->items->take(2) as $item)
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3">
                                    @if($item->product && $item->product->image)
                                    <img src="{{ Storage::url($item->product->image) }}" class="rounded"
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <small>
                                        {{ $item->product->name ?? 'Product no longer available' }}
                                        ({{ $item->qty_int }}x) {{-- FIXED: gunakan qty_int --}}
                                    </small>
                                </div>
                            </div>
                            @endforeach

                            @if($order->items->count() > 2)
                            <small class="text-muted">and {{ $order->items->count() - 2 }} more items...</small>
                            @endif
                        </div>

                        <div class="col-md-4 text-end">
                            <a href="{{ route('buyer.orders.show', $order->id) }}"
                                class="btn btn-outline-primary btn-sm mb-2">
                                View Details
                            </a>

                            @if($order->canBeCanceled())
                            <form action="{{ route('buyer.orders.cancel', $order->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger btn-sm mb-2"
                                    onclick="return confirm('Are you sure you want to cancel this order?')">
                                    Cancel Order
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $orders->links() }}
    @else
    <div class="text-center py-5">
        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
        <h3>No Orders Yet</h3>
        <p class="text-muted">You haven't placed any orders yet.</p>
        <a href="{{ route('products.index') }}" class="btn btn-primary">
            Start Shopping
        </a>
    </div>
    @endif
</div>
@endsection