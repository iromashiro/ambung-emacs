{{-- resources/views/seller/orders/show.blade.php - Update untuk hanya tampilkan items seller --}}

@extends('layouts.seller')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Order #{{ $order->id }}</h1>
                <span
                    class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'canceled' ? 'danger' : 'primary') }} fs-6">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Items (Your Store Only)</h5>
                        </div>
                        <div class="card-body">
                            @forelse($order->items as $item)
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="me-3">
                                    @if($item->product && $item->product->images && $item->product->images->count() > 0)
                                    <img src="{{ Storage::url($item->product->images->first()->path) }}" class="rounded"
                                        style="width: 60px; height: 60px; object-fit: cover;"
                                        alt="{{ $item->product->name }}">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                        style="width: 60px; height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $item->product->name ?? 'Product not found' }}</h6>
                                    <p class="text-muted mb-0">
                                        Rp {{ number_format($item->price, 0, ',', '.') }} x {{ $item->quantity }}
                                    </p>
                                </div>
                                <div class="text-end">
                                    <strong>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No items from your store in this order.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Order Summary (Your Items)</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($sellerSubtotal ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold">Rp {{ number_format($sellerSubtotal ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rest of the view remains the same -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection