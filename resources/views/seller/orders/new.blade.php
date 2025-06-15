@extends('layouts.seller')

@section('title', 'New Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">New Orders</h1>
                    <p class="text-muted">Orders waiting for confirmation</p>
                </div>
                <div>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> All Orders
                    </a>
                </div>
            </div>

            @if(isset($orders) && $orders->count() > 0)
            <!-- Bulk Actions -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="bulkActionForm" method="POST" action="{{ route('seller.orders.bulk-action') }}">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label" for="selectAll">
                                        Select All Orders
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <select class="form-select" name="action" required>
                                        <option value="">Select Action</option>
                                        <option value="confirm">Confirm Selected</option>
                                        <option value="cancel">Cancel Selected</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders List -->
            <div class="row">
                @foreach($orders as $order)
                <div class="col-lg-6 mb-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning bg-opacity-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input order-checkbox" type="checkbox" name="order_ids[]"
                                        value="{{ $order->id }}" form="bulkActionForm">
                                    <label class="form-check-label fw-bold">
                                        #{{ $order->order_number }}
                                    </label>
                                </div>
                                <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
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

                            <!-- Order Items -->
                            <div class="mb-3">
                                <h6 class="fw-bold mb-2">Items ({{ $order->items->count() }})</h6>
                                @foreach($order->items->take(3) as $item)
                                <div class="d-flex align-items-center mb-2">
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
                                        <small class="fw-bold">{{ $item->product->name ?? 'Product' }}</small>
                                        <small class="text-muted d-block">{{ $item->quantity }}x @ Rp
                                            {{ number_format($item->price) }}</small>
                                    </div>
                                </div>
                                @endforeach
                                @if($order->items->count() > 3)
                                <small class="text-muted">+{{ $order->items->count() - 3 }} more items</small>
                                @endif
                            </div>

                            <!-- Order Total -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold text-primary fs-5">Rp {{ number_format($order->total) }}</span>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST"
                                    class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check me-1"></i> Confirm
                                    </button>
                                </form>
                                <a href="{{ route('seller.orders.show', $order) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('seller.orders.status.update', $order) }}" method="POST"
                                    onsubmit="return confirm('Cancel this order?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="canceled">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
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
                    <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No New Orders</h5>
                    <p class="text-muted">You don't have any new orders waiting for confirmation.</p>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-1"></i> View All Orders
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Update select all checkbox when individual checkboxes change
document.querySelectorAll('.order-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.order-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        const selectAllCheckbox = document.getElementById('selectAll');

        selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
    });
});
</script>
@endsection