@extends('layouts.seller')

@section('title', 'Processing Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Processing Orders</h1>
                    <p class="text-muted">Orders currently being processed</p>
                </div>
                <div>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> All Orders
                    </a>
                </div>
            </div>

            @if(isset($orders) && $orders->count() > 0)
            <div class="row">
                @foreach($orders as $order)
                <div class="col-lg-6 mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary bg-opacity-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">#{{ $order->order_number }}</h6>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">Processing</span>
                                    <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Customer Info -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $order->user->name ?? $order->customer_name ?? 'Guest' }}</h6>
                                    <small
                                        class="text-muted">{{ $order->user->email ?? $order->customer_email }}</small>
                                </div>
                            </div>

                            <!-- Processing Progress -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="fw-bold">Processing Progress</small>
                                    <small class="text-muted">75%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 75%"></div>
                                </div>
                                <small class="text-muted">Ready for shipping</small>
                            </div>

                            <!-- Order Summary -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Items</small>
                                    <div class="fw-bold">{{ $order->items->sum('quantity') ?? 0 }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Total</small>
                                    <div class="fw-bold text-primary">Rp {{ number_format($order->total) }}</div>
                                </div>
                            </div>

                            <!-- Quick Items View -->
                            <div class="mb-3">
                                <small class="fw-bold text-muted">ITEMS</small>
                                @foreach($order->items->take(2) as $item)
                                <div class="d-flex align-items-center mt-2">
                                    @if($item->product && $item->product->images && $item->product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}"
                                        class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;"
                                        alt="Product">
                                    @else
                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                        style="width: 30px; height: 30px;">
                                        <i class="fas fa-image text-muted small"></i>
                                    </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <small>{{ $item->product->name ?? 'Product' }} ({{ $item->quantity }}x)</small>
                                    </div>
                                </div>
                                @endforeach
                                @if($order->items->count() > 2)
                                <small class="text-muted">+{{ $order->items->count() - 2 }} more</small>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST"
                                    class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="shipped">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-shipping-fast me-1"></i> Mark as Shipped
                                    </button>
                                </form>
                                <a href="{{ route('seller.orders.show', $order) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(method_exists($orders, 'links'))
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
            @endif
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Processing Orders</h5>
                    <p class="text-muted">You don't have any orders currently being processed.</p>
                    <a href="{{ route('seller.orders.new') }}" class="btn btn-primary">
                        <i class="fas fa-bell me-1"></i> Check New Orders
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection