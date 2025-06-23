{{-- resources/views/seller/orders/new.blade.php --}}

@extends('layouts.seller')

@section('title', 'New Orders')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">New Orders</h1>
            <p class="text-muted">Orders waiting for your confirmation</p>
        </div>
        <div>
            <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to All Orders
            </a>
        </div>
    </div>

    @if($orders->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        @php
                        // PERBAIKAN: Hitung total hanya untuk items dari seller ini
                        $sellerItems = $order->items->filter(function($item) {
                        return $item->product && $item->product->seller_id === auth()->user()->id;
                        });
                        $sellerTotal = $sellerItems->sum(function($item) {
                        return $item->price * $item->quantity;
                        });
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('seller.orders.show', $order->id) }}" class="text-decoration-none">
                                    #{{ $order->id }}
                                </a>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $order->user->name ?? 'Unknown' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $order->user->email ?? 'No email' }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    @foreach($sellerItems as $item)
                                    <div class="small">
                                        {{ $item->product->name ?? 'Unknown Product' }} ({{ $item->quantity }}x)
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($sellerTotal, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <div>
                                    {{ $order->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('seller.orders.show', $order->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('seller.orders.status.update', $order->id) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('Accept this order?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('seller.orders.status.update', $order->id) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="canceled">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Cancel this order?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links() }}
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4>No New Orders</h4>
            <p class="text-muted">You don't have any new orders at the moment.</p>
            <a href="{{ route('seller.orders.index') }}" class="btn btn-primary">
                View All Orders
            </a>
        </div>
    </div>
    @endif
</div>
@endsection