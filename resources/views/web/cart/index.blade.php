{{-- resources/views/web/cart/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Shopping Cart</h1>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if($cart && $cart->count() > 0)
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cart Items ({{ $summary['items_count'] }})</h5>
                </div>
                <div class="card-body">
                    @foreach($cart as $item)
                    <div class="cart-item mb-3 pb-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                @if($item->product->image_path)
                                <img src="{{ Storage::url($item->product->image_path) }}"
                                    alt="{{ $item->product->name }}" class="img-fluid rounded">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                    style="height: 80px;">
                                    <span class="text-muted">No Image</span>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1">{{ $item->product->name }}</h6>
                                <small class="text-muted">{{ $item->product->seller->store->name}}</small>
                            </div>
                            <div class="col-md-2">
                                <strong>Rp {{ number_format($item->product->price, 0, ',', '.') }}</strong>
                            </div>
                            <div class="col-md-2">
                                <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                    class="cart-update-form">
                                    @csrf
                                    @method('PATCH')
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm quantity-minus"
                                            {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                        <input type="number"
                                            class="form-control form-control-sm text-center cart-quantity-input"
                                            name="quantity" value="{{ $item->quantity }}" min="1"
                                            max="{{ $item->product->stock }}">
                                        <button type="button" class="btn btn-outline-secondary btn-sm quantity-plus"
                                            {{ $item->quantity >= $item->product->stock ? 'disabled' : '' }}>+</button>
                                    </div>
                                    <div class="text-center mt-1">
                                        <small class="text-muted">Stock: {{ $item->product->stock }}</small>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2 text-end">
                                <div class="mb-2">
                                    <strong>Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
                                </div>
                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Remove this item from cart?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card cart-summary">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span class="text-success">Free (COD)</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong>Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
        <h3>Your cart is empty</h3>
        <p class="text-muted">Add some products to your cart to get started!</p>
        <a href="{{ route('products.index') }}" class="btn btn-primary">Continue Shopping</a>
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Get all forms
        const forms = document.querySelectorAll('.cart-update-form');

        forms.forEach(form => {
            const minusBtn = form.querySelector('.quantity-minus');
            const plusBtn = form.querySelector('.quantity-plus');
            const input = form.querySelector('.cart-quantity-input');

            // Function to update button states
            function updateButtonStates() {
                const value = parseInt(input.value);
                const min = parseInt(input.getAttribute('min'));
                const max = parseInt(input.getAttribute('max'));

                minusBtn.disabled = value <= min;
                plusBtn.disabled = value >= max;
            }

            // Minus button click handler
            minusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value);
                const minValue = parseInt(input.getAttribute('min'));

                if (currentValue > minValue) {
                    input.value = currentValue - 1;
                    updateButtonStates();
                    submitWithLoading(form);
                }
            });

            // Plus button click handler
            plusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.getAttribute('max'));

                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    updateButtonStates();
                    submitWithLoading(form);
                }
            });

            // Direct input change handler
            input.addEventListener('change', function() {
                const value = parseInt(this.value);
                const min = parseInt(this.getAttribute('min'));
                const max = parseInt(this.getAttribute('max'));

                // Validate input bounds
                if (value < min) this.value = min;
                if (value > max) this.value = max;

                updateButtonStates();
                submitWithLoading(form);
            });

            // Initialize button states
            updateButtonStates();
        });

        // Function to show loading state and submit form
        function submitWithLoading(form) {
            // Add loading state
            const buttons = form.querySelectorAll('button');
            const originalContent = {};

            buttons.forEach(btn => {
                originalContent[btn.className] = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            });

            // Submit form
            form.submit();
        }
    });
    </script>
</div>
@endsection