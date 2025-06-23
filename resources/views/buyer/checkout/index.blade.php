@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container" x-data="checkoutPage()">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="h3 mb-4">Checkout</h1>

            {{-- Show warnings if any --}}
            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('checkout.process') }}" @submit="isSubmitting = true"
                id="checkoutForm">
                @csrf

                <!-- Hidden cart items - FIXED version -->
                @foreach($selectedCartItemIds as $cartItemId)
                <input type="hidden" name="cart_items[]" value="{{ $cartItemId }}">
                @endforeach

                <div class="row g-4">
                    <!-- Order Summary -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                @if(isset($itemsByStore) && count($itemsByStore) > 0)
                                @foreach($itemsByStore as $storeName => $items)
                                <div class="mb-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-store me-2"></i>{{ $storeName }}
                                    </h6>

                                    @foreach($items as $item)
                                    <div class="d-flex align-items-center mb-3 p-3 border rounded">
                                        <div class="me-3">
                                            @if($item->product && $item->product->image)
                                            <img src="{{ Storage::url($item->product->image) }}" class="rounded"
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
                                            <h6 class="mb-1">{{ $item->product->name ?? 'Product no longer available' }}
                                            </h6>
                                            <div class="text-muted small">
                                                Rp {{ number_format($item->product->price ?? 0, 0, ',', '.') }} x
                                                {{ $item->quantity }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary">
                                                Rp
                                                {{ number_format(($item->product->price ?? 0) * $item->quantity, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if(!$loop->last)
                                <hr>
                                @endif
                                @endforeach
                                @else
                                <div class="text-center py-4">
                                    <p class="text-muted">No items found for checkout</p>
                                    <a href="{{ route('cart.index') }}" class="btn btn-primary">Back to Cart</a>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Only show shipping form if we have items --}}
                        @if(isset($itemsByStore) && count($itemsByStore) > 0)
                        <!-- Shipping Information -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Shipping Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="shipping_address" class="form-label">Shipping Address *</label>
                                        <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                            id="shipping_address" name="shipping_address" rows="3"
                                            placeholder="Enter your complete shipping address"
                                            required>{{ old('shipping_address', $user->address ?? '') }}</textarea>
                                        @error('shipping_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" placeholder="Enter your phone number"
                                            value="{{ old('phone', $user->phone ?? '') }}" required>
                                        @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="payment_method" class="form-label">Payment Method *</label>
                                        <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method" required>
                                            <option value="cod" {{ old('payment_method') === 'cod' ? 'selected' : '' }}>
                                                Cash on Delivery (COD)
                                            </option>
                                        </select>
                                        @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="notes" class="form-label">Order Notes (Optional)</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes"
                                            name="notes" rows="2"
                                            placeholder="Any special instructions for your order">{{ old('notes') }}</textarea>
                                        @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Price Summary -->
                    @if(isset($itemsByStore) && count($itemsByStore) > 0)
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Payment Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal ({{ count($cartItems ?? []) }} items)</span>
                                    <span class="fw-bold">Rp {{ number_format($subtotal ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Shipping Fee</span>
                                    <span class="fw-bold">Free</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="fw-bold">Total</span>
                                    <span class="fw-bold text-danger">Rp
                                        {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3" :disabled="isSubmitting">
                                    <span x-show="!isSubmitting">
                                        <i class="fas fa-shopping-cart me-2"></i>Place Order
                                    </span>
                                    <span x-show="isSubmitting">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Processing...
                                    </span>
                                </button>

                                <div class="text-center mt-3">
                                    <a href="{{ route('cart.index') }}" class="btn btn-link">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function checkoutPage() {
        return {
            isSubmitting: false,

            init() {
                console.log('Checkout page initialized');

                // Check if form has valid cart items
                const cartItems = document.querySelectorAll('input[name="cart_items[]"]');
                console.log('Cart items in form:', cartItems.length);

                if (cartItems.length === 0) {
                    console.warn('No cart items found in form');
                }
            }
        }
    }

    // Prevent back button issues
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // Prevent multiple form submissions
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton.disabled) {
            e.preventDefault();
            return false;
        }
        submitButton.disabled = true;
    });
</script>
@endsection