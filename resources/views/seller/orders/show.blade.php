@extends('layouts.seller')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="container" x-data="orderDetail()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Order #{{ $order->id }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
            <button class="btn btn-outline-primary" onclick="printOrder()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Status Management -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Status Management</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-{{ $order->status_color }} me-3">{{ $order->status_label }}</span>
                                <span class="text-muted">Last updated:
                                    {{ $order->updated_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 text-md-end">
                            @if($order->status === 'new')
                            <button class="btn btn-success me-2" @click="updateStatus('processing')">
                                <i class="fas fa-play me-1"></i> Start Processing
                            </button>
                            <button class="btn btn-danger" @click="updateStatus('cancelled')">
                                <i class="fas fa-times me-1"></i> Cancel Order
                            </button>
                            @elseif($order->status === 'processing')
                            <button class="btn btn-warning me-2" @click="showShippingModal()">
                                <i class="fas fa-shipping-fast me-1"></i> Mark as Shipped
                            </button>
                            <button class="btn btn-danger" @click="updateStatus('cancelled')">
                                <i class="fas fa-times me-1"></i> Cancel Order
                            </button>
                            @elseif($order->status === 'shipped')
                            <button class="btn btn-success" @click="updateStatus('delivered')">
                                <i class="fas fa-check me-1"></i> Mark as Delivered
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Order Progress -->
                    <div class="mt-4">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $order->progress_percentage }}%"></div>
                        </div>

                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-{{ $order->progress >= 1 ? 'success' : 'muted' }}">Order Placed</small>
                            <small class="text-{{ $order->progress >= 2 ? 'success' : 'muted' }}">Processing</small>
                            <small class="text-{{ $order->progress >= 3 ? 'success' : 'muted' }}">Shipped</small>
                            <small class="text-{{ $order->progress >= 4 ? 'success' : 'muted' }}">Delivered</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Items</h5>
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
                            <p class="text-muted mb-1">SKU: {{ $item->product->sku }}</p>
                            <p class="text-muted mb-0">{{ $item->quantity }} x @currency($item->price)</p>
                        </div>

                        <div class="col-auto">
                            <span class="fw-bold">@currency($item->subtotal)</span>
                        </div>
                    </div>

                    @if(!$loop->last)
                    <hr>
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Customer Details</h6>
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $order->user->avatar_url ?? asset('images/avatar-default.png') }}"
                                    alt="{{ $order->user->name }}" class="rounded-circle me-3"
                                    style="width: 50px; height: 50px;">
                                <div>
                                    <div class="fw-medium">{{ $order->user->name }}</div>
                                    <div class="text-muted">{{ $order->user->email }}</div>
                                    <div class="text-muted">{{ $order->user->phone }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6>Shipping Address</h6>
                            <p class="mb-1">{{ $order->shipping_address->recipient_name }}</p>
                            <p class="mb-1">{{ $order->shipping_address->phone }}</p>
                            <p class="mb-0">
                                {{ $order->shipping_address->address }}<br>
                                {{ $order->shipping_address->city }}, {{ $order->shipping_address->province }}<br>
                                {{ $order->shipping_address->postal_code }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Notes</h5>
                </div>
                <div class="card-body">
                    @if($order->notes)
                    <div class="alert alert-info">
                        <strong>Customer Notes:</strong><br>
                        {{ $order->notes }}
                    </div>
                    @endif

                    <form @submit.prevent="addNote()">
                        <div class="mb-3">
                            <label for="seller_notes" class="form-label">Add Seller Notes</label>
                            <textarea class="form-control" id="seller_notes" x-model="sellerNotes" rows="3"
                                placeholder="Add notes for this order..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" :disabled="!sellerNotes.trim()">
                            <i class="fas fa-plus me-1"></i> Add Note
                        </button>
                    </form>

                    @if($order->seller_notes && count($order->seller_notes) > 0)
                    <div class="mt-4">
                        <h6>Previous Notes:</h6>
                        @foreach($order->seller_notes as $note)
                        <div class="border-start border-primary ps-3 mb-2">
                            <p class="mb-1">{{ $note->content }}</p>
                            <small class="text-muted">{{ $note->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        @endforeach
                    </div>
                    @endif
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
                        <span>Order Date</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method</span>
                        <span>Cash on Delivery</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Count</span>
                        <span>{{ $order->items_count }} items</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>@currency($order->subtotal)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping Fee</span>
                        <span>@currency($order->shipping_fee)</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold">@currency($order->total)</span>
                    </div>

                    @if($order->tracking_number)
                    <div class="alert alert-info">
                        <strong>Tracking Number:</strong><br>
                        {{ $order->tracking_number }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="mailto:{{ $order->user->email }}" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-1"></i> Email Customer
                        </a>
                        <a href="tel:{{ $order->user->phone }}" class="btn btn-outline-success">
                            <i class="fas fa-phone me-1"></i> Call Customer
                        </a>
                        <button class="btn btn-outline-info" onclick="printOrder()">
                            <i class="fas fa-print me-1"></i> Print Order
                        </button>
                        <a href="{{ route('seller.orders.invoice', $order) }}" class="btn btn-outline-secondary"
                            target="_blank">
                            <i class="fas fa-file-invoice me-1"></i> View Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Modal -->
    <div class="modal fade" id="shippingModal" tabindex="-1" aria-labelledby="shippingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shippingModalLabel">Mark as Shipped</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="markAsShipped()">
                        <div class="mb-3">
                            <label for="tracking_number" class="form-label">Tracking Number (Optional)</label>
                            <input type="text" class="form-control" id="tracking_number" x-model="trackingNumber"
                                placeholder="Enter tracking number">
                        </div>

                        <div class="mb-3">
                            <label for="shipping_notes" class="form-label">Shipping Notes (Optional)</label>
                            <textarea class="form-control" id="shipping_notes" x-model="shippingNotes" rows="3"
                                placeholder="Add any shipping notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" @click="markAsShipped()">Mark as Shipped</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function orderDetail() {
        return {
            sellerNotes: '',
            trackingNumber: '',
            shippingNotes: '',

            async updateStatus(status) {
                if (!confirm(`Are you sure you want to update this order status to ${status}?`)) {
                    return;
                }

                try {
                    const response = await fetch(`{{ route('seller.orders.status', $order) }}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const error = await response.json();
                        alert(error.message || 'Failed to update order status');
                    }
                } catch (error) {
                    console.error('Error updating order status:', error);
                    alert('An error occurred. Please try again.');
                }
            },

            showShippingModal() {
                const modal = new bootstrap.Modal(document.getElementById('shippingModal'));
                modal.show();
            },

            async markAsShipped() {
                try {
                    const response = await fetch(`{{ route('seller.orders.status', $order) }}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: 'shipped',
                            tracking_number: this.trackingNumber,
                            notes: this.shippingNotes
                        })
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const error = await response.json();
                        alert(error.message || 'Failed to mark order as shipped');
                    }
                } catch (error) {
                    console.error('Error marking order as shipped:', error);
                    alert('An error occurred. Please try again.');
                }
            },

            async addNote() {
                if (!this.sellerNotes.trim()) return;

                try {
                    const response = await fetch(`{{ route('seller.orders.notes', $order) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            notes: this.sellerNotes
                        })
                    });

                    if (response.ok) {
                        this.sellerNotes = '';
                        window.location.reload();
                    } else {
                        const error = await response.json();
                        alert(error.message || 'Failed to add note');
                    }
                } catch (error) {
                    console.error('Error adding note:', error);
                    alert('An error occurred. Please try again.');
                }
            }
        };
    }

    function printOrder() {
        window.print();
    }
</script>
@endpush
@endsection