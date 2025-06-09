@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">Order #{{ $order->id }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Order Status</h5>
                        <span class="badge bg-{{ $order->status_color }}">{{ $order->status_label }}</span>
                    </div>

                    <div class="position-relative mt-4">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $order->progress_percentage }}%"></div>
                        </div>

                        <div class="position-absolute top-0 start-0 translate-middle">
                            <div class="rounded-circle {{ $order->progress >= 1 ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center"
                                style="width: 30px; height: 30px;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>

                        <div class="position-absolute top-0 start-25 translate-middle">
                            <div class="rounded-circle {{ $order->progress >= 2 ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center"
                                style="width: 30px; height: 30px;">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>

                        <div class="position-absolute top-0 start-50 translate-middle">
                            <div class="rounded-circle {{ $order->progress >= 3 ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center"
                                style="width: 30px; height: 30px;">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                        </div>

                        <div class="position-absolute top-0 start-75 translate-middle">
                            <div class="rounded-circle {{ $order->progress >= 4 ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center"
                                style="width: 30px; height: 30px;">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>

                        <div class="position-absolute top-0 start-100 translate-middle">
                            <div class="rounded-circle {{ $order->progress >= 5 ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center"
                                style="width: 30px; height: 30px;">
                                <i class="fas fa-check-double"></i>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 text-center">
                        <div>
                            <div class="small text-muted">Order Placed</div>
                            <div class="small">{{ $order->created_at->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="small text-muted">Processing</div>
                            <div class="small">
                                {{ $order->processing_at ? $order->processing_at->format('d M Y') : '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-muted">Shipped</div>
                            <div class="small">{{ $order->shipped_at ? $order->shipped_at->format('d M Y') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted">Delivered</div>
                            <div class="small">{{ $order->delivered_at ? $order->delivered_at->format('d M Y') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted">Completed</div>
                            <div class="small">{{ $order->completed_at ? $order->completed_at->format('d M Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    @if($order->status === 'delivered' && !$order->is_completed)
                    <div class="d-grid mt-4">
                        <form action="{{ route('orders.complete', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle me-1"></i> Confirm Order Received
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($order->status === 'new' || $order->status === 'processing')
                    <div class="d-grid mt-4">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#cancelOrderModal">
                            <i class="fas fa-times-circle me-1"></i> Cancel Order
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-store me-2"></i>
                        <h5 class="mb-0">{{ $order->store->name }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    @foreach($order->items as $item)
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <img src="{{ $item->product->image_url ?? asset('images/product-default.jpg') }}"
                                alt="{{ $item->product->name }}" class="img-thumbnail"
                                style="width: 80px; height: 80px; object-fit: cover;">
                        </div>

                        <div class="col">
                            <h6 class="mb-1">{{ $item->product->name }}</h6>
                            <p class="text-muted mb-0">{{ $item->quantity }} x Rp
                                {{ number_format($item->price, 0, ',', '.') }}</p>
                        </div>

                        <div class="col-auto">
                            <span class="fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if(!$loop->last)
                    <hr>
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Shipping Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Recipient</h6>
                            <p class="mb-1">{{ $order->shipping_address->recipient_name }}</p>
                            <p class="mb-1">{{ $order->shipping_address->phone }}</p>
                            <p class="mb-0">
                                {{ $order->shipping_address->address }}, {{ $order->shipping_address->city }},
                                {{ $order->shipping_address->province }}, {{ $order->shipping_address->postal_code }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6>Shipping Method</h6>
                            <p class="mb-1">Regular Shipping</p>
                            <p class="mb-0">Estimated delivery: 2-3 days</p>

                            @if($order->tracking_number)
                            <h6 class="mt-3">Tracking Number</h6>
                            <p class="mb-0">{{ $order->tracking_number }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Order History</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($order->history as $history)
                        <li class="list-group-item px-0">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-{{ $history->status_color }} text-white d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ $history->status_icon }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">{{ $history->status_label }}</h6>
                                        <small
                                            class="text-muted">{{ $history->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <p class="mb-0">{{ $history->notes }}</p>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order ID</span>
                        <span class="fw-bold">#{{ $order->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order Date</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method</span>
                        <span>Cash on Delivery (COD)</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal ({{ $order->items_count }} items)</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping Fee</span>
                        <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>

                    @if($order->is_completed && !$order->is_reviewed)
                    <div class="d-grid">
                        <a href="{{ route('reviews.create', ['order' => $order->id]) }}" class="btn btn-primary">
                            <i class="fas fa-star me-1"></i> Write a Review
                        </a>
                    </div>
                    @endif

                    @if($order->status === 'delivered' || $order->status === 'completed')
                    <div class="d-grid mt-2">
                        <a href="{{ route('orders.invoice', $order) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-file-invoice me-1"></i> Download Invoice
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Need Help -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('contact') }}" class="btn btn-outline-primary">
                            <i class="fas fa-headset me-1"></i> Contact Support
                        </a>
                        <a href="{{ route('faq') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-question-circle me-1"></i> FAQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('orders.cancel', $order) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this order?</p>

                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">Reason for cancellation</label>
                            <select class="form-select" id="cancel_reason" name="cancel_reason" required>
                                <option value="">Select a reason</option>
                                <option value="Changed my mind">Changed my mind</option>
                                <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                                <option value="Ordered by mistake">Ordered by mistake</option>
                                <option value="Shipping takes too long">Shipping takes too long</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3" id="otherReasonContainer" style="display: none;">
                            <label for="other_reason" class="form-label">Please specify</label>
                            <textarea class="form-control" id="other_reason" name="other_reason" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cancelReasonSelect = document.getElementById('cancel_reason');
        const otherReasonContainer = document.getElementById('otherReasonContainer');

        cancelReasonSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                otherReasonContainer.style.display = 'block';
            } else {
                otherReasonContainer.style.display = 'none';
            }
        });
    });
</script>
@endpush
@endsection