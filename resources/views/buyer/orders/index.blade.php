@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Filter Orders</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('orders.index') }}">
                        <div class="mb-3">
                            <label for="status" class="form-label">Order Status</label>
                            <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                <option value="">All Orders</option>
                                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}" onchange="this.form.submit()">
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}" onchange="this.form.submit()">
                        </div>
                        
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                            Clear Filters
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Orders</h2>
                <div class="text-muted">
                    {{ $orders->total() }} orders found
                </div>
            </div>
            
            @forelse($orders as $order)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">Order #{{ $order->id }}</h6>
                                <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                @if($order->status === 'new')
                                    <span class="badge bg-info">New</span>
                                @elseif($order->status === 'processing')
                                    <span class="badge bg-warning">Processing</span>
                                @elseif($order->status === 'shipped')
                                    <span class="badge bg-primary">Shipped</span>
                                @elseif($order->status === 'delivered')
                                    <span class="badge bg-success">Delivered</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($order->items->groupBy('product.store_id') as $storeId => $items)
                            @php
                                $store = \App\Models\Store::find($storeId);
                            @endphp
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="{{ $store->logo_url ?? asset('images/stores/default.jpg') }}" 
                                         class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;" 
                                         alt="{{ $store->name }}">
                                    <h6 class="mb-0">{{ $store->name }}</h6>
                                </div>
                                
                                @foreach($items as $item)
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="{{ $item->product->image_url ?? asset('images/products/default.jpg') }}" 
                                             class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;" 
                                             alt="{{ $item->product->name }}">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $item->product->name }}</h6>
                                            <p class="text-muted small mb-0">
                                                Qty: {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">
                                                Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                        
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div>
                                <strong>Total: Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm me-2">
                                    View Details
                                </a>
                                
                                @if($order->status === 'new')
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="cancelOrder({{ $order->id }})">
                                        Cancel Order
                                    </button>
                                @elseif($order->status === 'shipped')
                                    <button class="btn btn-success btn-sm" 
                                            onclick="confirmDelivery({{ $order->id }})">
                                        Confirm Delivery
                                    </button>
                                @elseif($order->status === 'delivered')
                                    <a href="{{ route('orders.review', $order) }}" class="btn btn-warning btn-sm">
                                        Write Review
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h3>No orders found</h3>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            Start Shopping
                        </a>
                    </div>
                </div>
            @endforelse
            
            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order?</p>
                <div class="mb-3">
                    <label for="cancellation_reason" class="form-label">Reason for cancellation</label>
                    <select class="form-select" id="cancellation_reason" required>
                        <option value="">Select a reason</option>
                        <option value="changed_mind">Changed my mind</option>
                        <option value="found_better_price">Found better price elsewhere</option>
                        <option value="ordered_by_mistake">Ordered by mistake</option>
                        <option value="delivery_too_long">Delivery taking too long</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3" id="other_reason_container" style="display: none;">
                    <label for="other_reason" class="form-label">Please specify</label>
                    <textarea class="form-control" id="other_reason" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Order</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancelOrder()">Cancel Order</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let orderToCancel = null;
    
    function cancelOrder(orderId) {
        orderToCancel = orderId;
        const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
        modal.show();
    }
    
    function confirmCancelOrder() {
        const reason = document.getElementById('cancellation_reason').value;
        const otherReason = document.getElementById('other_reason').value;
        
        if (!reason) {
            alert('Please select a reason for cancellation');
            return;
        }
        
        if (reason === 'other' && !otherReason) {
            alert('Please specify the reason for cancellation');
            return;
        }
        
        const finalReason = reason === 'other' ? otherReason : reason;
        
        fetch(`/orders/${orderToCancel}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason: finalReason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to cancel order');
            }
        })
        .catch(error => {
            console.error('Error cancelling order:', error);
            alert('An error occurred while cancelling the order');
        });
    }
    
    function confirmDelivery(orderId) {
        if (confirm('Confirm that you have received this order?')) {
            fetch(`/orders/${orderId}/confirm-delivery`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to confirm delivery');
                }
            })
            .catch(error => {
                console.error('Error confirming delivery:', error);
                alert('An error occurred while confirming delivery');
            });
        }
    }
    
    // Show/hide other reason textarea
    document.getElementById('cancellation_reason').addEventListener('change', function() {
        const otherReasonContainer = document.getElementById('other_reason_container');
        if (this.value === 'other') {
            otherReasonContainer.style.display = 'block';
        } else {
            otherReasonContainer.style.display = 'none';
        }
    });
</script>
@endsection