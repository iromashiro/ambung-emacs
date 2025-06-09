@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if(config('app.debug'))
    <!-- Debug Information -->
    <div class="alert alert-info">
        <h6>Debug Info:</h6>
        <ul class="mb-0">
            <li>Cart Items: {{ $cart->count() }}</li>
            <li>User ID: {{ auth()->id() }}</li>
            <li>Summary Total: {{ $summary['total'] ?? 'N/A' }}</li>
            <li>Route: {{ route('checkout.process') }}</li>
        </ul>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                @csrf

                <!-- Debug hidden fields -->
                @if(config('app.debug'))
                <input type="hidden" name="debug" value="1">
                <input type="hidden" name="cart_count" value="{{ $cart->count() }}">
                @endif

                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Shipping Address <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                id="shipping_address" name="shipping_address" rows="3"
                                required>{{ old('shipping_address', $defaultAddress->address_line1 ?? '') }}</textarea>
                            @error('shipping_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ old('phone', auth()->user()->phone) }}" required>
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                rows="2" placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Cash on Delivery (COD)</strong><br>
                            Pay when your order is delivered to your address. Our delivery partner will collect the
                            payment.
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg" id="placeOrderBtn">
                        <i class="fas fa-check me-2"></i> Place Order
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    @if($cart->isEmpty())
                    <div class="alert alert-warning">
                        Your cart is empty!
                    </div>
                    @else
                    @foreach($cart as $item)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">{{ $item->product->name ?? 'Product Not Found' }}</h6>
                            <small class="text-muted">Qty: {{ $item->quantity }}</small>
                            @if(config('app.debug'))
                            <small class="text-info d-block">Product ID: {{ $item->product_id }}</small>
                            @endif
                        </div>
                        <span>Rp {{ number_format(($item->product->price ?? 0) * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                    @endforeach

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($summary['subtotal'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span class="text-success">Free</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong>Rp {{ number_format($summary['total'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('placeOrderBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            console.log('Form submission started');

            // Disable button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';

            // Debug: Log form data
            const formData = new FormData(form);
            console.log('Form data:', Object.fromEntries(formData));

            // Re-enable button after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i> Place Order';
            }, 10000);
        });
    }
});
</script>
@endpush
@endsection