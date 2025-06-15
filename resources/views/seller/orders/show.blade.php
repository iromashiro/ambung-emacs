@extends('layouts.seller')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Order #{{ $order->order_number }}</h1>
                    <p class="text-muted">Order details and management</p>
                </div>
                <div>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Items</h5>
                        </div>
                        <div class="card-body">
                            @if($order->items && $order->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->product && $item->product->images &&
                                                    $item->product->images->count() > 0)
                                                    <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}"
                                                        class="rounded me-3"
                                                        style="width: 50px; height: 50px; object-fit: cover;"
                                                        alt="Product Image">
                                                    @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                        style="width: 50px; height: 50px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-1">
                                                            {{ $item->product->name ?? 'Product Not Found' }}</h6>
                                                        <small class="text-muted">SKU:
                                                            {{ $item->product->sku ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rp {{ number_format($item->price) }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td><strong>Rp {{ number_format($item->price * $item->quantity) }}</strong>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Subtotal:</th>
                                            <th>Rp {{ number_format($order->subtotal ?? 0) }}</th>
                                        </tr>
                                        @if($order->shipping_cost > 0)
                                        <tr>
                                            <th colspan="3" class="text-end">Shipping:</th>
                                            <th>Rp {{ number_format($order->shipping_cost) }}</th>
                                        </tr>
                                        @endif
                                        @if($order->tax > 0)
                                        <tr>
                                            <th colspan="3" class="text-end">Tax:</th>
                                            <th>Rp {{ number_format($order->tax) }}</th>
                                        </tr>
                                        @endif
                                        @if($order->discount > 0)
                                        <tr>
                                            <th colspan="3" class="text-end">Discount:</th>
                                            <th class="text-danger">-Rp {{ number_format($order->discount) }}</th>
                                        </tr>
                                        @endif
                                        <tr class="table-active">
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th class="text-primary">Rp {{ number_format($order->total) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-box fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No items in this order</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Customer Details</h6>
                                    <p class="mb-1"><strong>Name:</strong>
                                        {{ $order->user->name ?? $order->customer_name ?? 'Guest' }}</p>
                                    <p class="mb-1"><strong>Email:</strong>
                                        {{ $order->user->email ?? $order->customer_email ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Phone:</strong> {{ $order->customer_phone ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Shipping Address</h6>
                                    <p class="mb-0">
                                        {{ $order->shipping_address ?? 'No shipping address provided' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Timeline</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item completed">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Placed</h6>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>

                                @if(in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered',
                                'completed']))
                                <div class="timeline-item completed">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Confirmed</h6>
                                        <small
                                            class="text-muted">{{ $order->confirmed_at ? $order->confirmed_at->format('M d, Y H:i') : 'N/A' }}</small>
                                    </div>
                                </div>
                                @endif

                                @if(in_array($order->status, ['processing', 'shipped', 'delivered', 'completed']))
                                <div
                                    class="timeline-item {{ $order->status === 'processing' ? 'active' : 'completed' }}">
                                    <div
                                        class="timeline-marker bg-{{ $order->status === 'processing' ? 'warning' : 'success' }}">
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Processing</h6>
                                        <small
                                            class="text-muted">{{ $order->processing_at ? $order->processing_at->format('M d, Y H:i') : 'In progress...' }}</small>
                                    </div>
                                </div>
                                @endif

                                @if(in_array($order->status, ['shipped', 'delivered', 'completed']))
                                <div class="timeline-item {{ $order->status === 'shipped' ? 'active' : 'completed' }}">
                                    <div
                                        class="timeline-marker bg-{{ $order->status === 'shipped' ? 'info' : 'success' }}">
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Shipped</h6>
                                        <small
                                            class="text-muted">{{ $order->shipped_at ? $order->shipped_at->format('M d, Y H:i') : 'In transit...' }}</small>
                                    </div>
                                </div>
                                @endif

                                @if(in_array($order->status, ['delivered', 'completed']))
                                <div class="timeline-item completed">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Delivered</h6>
                                        <small
                                            class="text-muted">{{ $order->delivered_at ? $order->delivered_at->format('M d, Y H:i') : 'N/A' }}</small>
                                    </div>
                                </div>
                                @endif

                                @if($order->status === 'canceled')
                                <div class="timeline-item completed">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Canceled</h6>
                                        <small
                                            class="text-muted">{{ $order->canceled_at ? $order->canceled_at->format('M d, Y H:i') : 'N/A' }}</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Order Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Order Status</h6>
                        </div>
                        <div class="card-body">
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
                            <div class="text-center mb-3">
                                <span class="badge bg-{{ $color }} fs-6 px-3 py-2">{{ ucfirst($order->status) }}</span>
                            </div>

                            @if($order->status !== 'canceled' && $order->status !== 'completed')
                            <div class="d-grid gap-2">
                                @if($order->status === 'pending')
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i> Confirm Order
                                    </button>
                                </form>
                                @elseif($order->status === 'confirmed')
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cog me-1"></i> Start Processing
                                    </button>
                                </form>
                                @elseif($order->status === 'processing')
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="shipped">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-shipping-fast me-1"></i> Mark as Shipped
                                    </button>
                                </form>
                                @elseif($order->status === 'shipped')
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-truck me-1"></i> Mark as Delivered
                                    </button>
                                </form>
                                @endif

                                @if(in_array($order->status, ['pending', 'confirmed']))
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="canceled">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-times me-1"></i> Cancel Order
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Order Summary</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td>Order Number:</td>
                                    <td class="fw-bold">#{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td>Order Date:</td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Payment Method:</td>
                                    <td>{{ $order->payment_method ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Payment Status:</td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                        @elseif($order->payment_status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @else
                                        <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Total Items:</td>
                                    <td>{{ $order->items->sum('quantity') ?? 0 }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td class="fw-bold">Total Amount:</td>
                                    <td class="fw-bold text-primary">Rp {{ number_format($order->total) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($order->notes)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Order Notes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print Order
                                </button>
                                <a href="mailto:{{ $order->user->email ?? $order->customer_email }}"
                                    class="btn btn-outline-secondary">
                                    <i class="fas fa-envelope me-1"></i> Contact Customer
                                </a>
                            </div>
                        </div>
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
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
    }

    .timeline-item.completed .timeline-marker {
        background-color: var(--bs-success) !important;
    }

    .timeline-item.active .timeline-marker {
        background-color: var(--bs-warning) !important;
    }

    @media print {

        .btn,
        .card-header,
        .timeline::before,
        .timeline-marker {
            display: none !important;
        }
    }
</style>
@endsection