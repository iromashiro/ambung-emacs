@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h2 class="mb-3">Order Placed Successfully!</h2>
                    <p class="lead mb-4">Thank you for your order. Your order number is
                        <strong>#{{ $order->order_number }}</strong>
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Your order will be processed and delivered via Cash on Delivery (COD). You will receive updates
                        about your order status.
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-primary me-2">
                            <i class="fas fa-eye me-1"></i> View Order Details
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-shopping-bag me-1"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection