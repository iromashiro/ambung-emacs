@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}"
                    class="text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6 mb-4">
            <div class="card border-0">
                <!-- Main Product Image -->
                <div class="text-center mb-3">
                    @if($product->images)
                    <img src="{{ Storage::url($product->images[0]->path) }}" alt="{{ $product->name }}"
                        class="img-fluid rounded shadow-sm"
                        style="height: 300px; width: 400px; max-height: 400px; object-fit: cover;">
                    @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded"
                        style="height: 400px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                    @endif
                </div>

                <!-- Additional Images (if any) -->
                @if($product->images && count($product->images) > 0)
                <div class="row g-2">
                    @foreach($product->images as $image)
                    <div class="col-3">
                        <img src="{{ Storage::url($image->path) }}" alt="{{ $product->name }}"
                            class="img-fluid rounded shadow-sm cursor-pointer" onclick="changeMainImage(this.src)">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <div class="product-details">
                <!-- Store Info -->
                <div class="mb-3">
                    <a href="" class="text-decoration-none">
                        <span class="badge bg-primary">
                            <i class="fas fa-store me-1"></i>{{ $product->seller->store->name }}
                        </span>
                    </a>
                </div>

                <!-- Product Name -->
                <h1 class="h2 mb-3">{{ $product->name }}</h1>

                <!-- Product Price -->
                <div class="mb-3">
                    <span class="h3 text-primary fw-bold">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                    @if($product->original_price && $product->original_price > $product->price)
                    <span class="text-muted text-decoration-line-through ms-2">
                        Rp {{ number_format($product->original_price, 0, ',', '.') }}
                    </span>
                    <span class="badge bg-danger ms-2">
                        {{ round((($product->original_price - $product->price) / $product->original_price) * 100) }}%
                        OFF
                    </span>
                    @endif
                </div>

                <!-- Product Rating & Reviews -->
                <div class="mb-3">
                    <div class="d-flex align-items-center">
                        <div class="text-warning me-2">
                            @for($i = 1; $i <= 5; $i++) @if($i <=floor($product->average_rating ?? 0))
                                <i class="fas fa-star"></i>
                                @elseif($i <= ($product->average_rating ?? 0))
                                    <i class="fas fa-star-half-alt"></i>
                                    @else
                                    <i class="far fa-star"></i>
                                    @endif
                                    @endfor
                        </div>
                        <span class="text-muted">
                            ({{ $product->reviews_count ?? 0 }} reviews)
                        </span>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="mb-3">
                    @if($product->stock > 0)
                    <span class="badge bg-success">
                        <i class="fas fa-check me-1"></i>In Stock ({{ $product->stock }} available)
                    </span>
                    @else
                    <span class="badge bg-danger">
                        <i class="fas fa-times me-1"></i>Out of Stock
                    </span>
                    @endif
                </div>

                <!-- Product Description -->
                @if($product->description)
                <div class="mb-4">
                    <h5>Description</h5>
                    <div class="text-muted">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
                @endif

                <!-- Add to Cart Form -->
                @if($product->stock > 0)
                <form action="{{ route('cart.add') }}" method="POST" class="mb-4" x-data="{ quantity: 1 }">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="row g-3 align-items-end">
                        <div class="col-auto">
                            <label for="quantity" class="form-label">Quantity</label>
                            <div class="input-group" style="width: 120px;">
                                <button type="button" class="btn btn-outline-secondary"
                                    @click="quantity = Math.max(1, quantity - 1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="quantity" x-model="quantity"
                                    min="1" max="{{ $product->stock }}" value="1">
                                <button type="button" class="btn btn-outline-secondary"
                                    @click="quantity = Math.min({{ $product->stock }}, quantity + 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </form>
                @endif

                <!-- Action Buttons -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <button class="btn btn-outline-danger w-100" onclick="toggleWishlist({{ $product->id }})">
                            <i class="far fa-heart me-1"></i>Add to Wishlist
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-primary w-100" onclick="shareProduct()">
                            <i class="fas fa-share-alt me-1"></i>Share
                        </button>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Product Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4"><strong>Category:</strong></div>
                            <div class="col-sm-8">{{ $product->category->name ?? 'Uncategorized' }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4"><strong>SKU:</strong></div>
                            <div class="col-sm-8">{{ $product->sku ?? 'N/A' }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4"><strong>Weight:</strong></div>
                            <div class="col-sm-8">{{ $product->weight ?? 'N/A' }} gr</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4"><strong>Condition:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge bg-success">{{ $product->condition ?? 'New' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products - SECTION YANG DIPERBAIKI -->
    @if($relatedProducts && $relatedProducts->count() > 0)
    <div class="mt-5">
        <h3 class="mb-4">Related Products</h3>
        <div class="row g-4">
            @foreach($relatedProducts as $relatedProduct)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        @if($relatedProduct->image)
                        <img src="{{ Storage::url($relatedProduct->image) }}" class="card-img-top"
                            alt="{{ $relatedProduct->name }}" style="height: 200px; object-fit: cover;">
                        {{$relatedProduct->image}}
                        @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                        @endif

                        @if($relatedProduct->original_price && $relatedProduct->original_price > $relatedProduct->price)
                        <span class="position-absolute top-0 start-0 badge bg-danger m-2">
                            SALE
                        </span>
                        @endif
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">
                            <a href="{{ route('products.show', $relatedProduct->slug) }}"
                                class="text-decoration-none text-dark">
                                {{ Str::limit($relatedProduct->name, 50) }}
                            </a>
                        </h6>

                        <!-- FIXED: Pengecekan relasi store yang lebih aman -->
                        <div class="text-muted small mb-2">
                            @if($relatedProduct->seller && $relatedProduct->seller->store)
                            {{ $relatedProduct->seller->store->name }}
                            @else
                            <span class="text-muted">Store not available</span>
                            @endif
                        </div>

                        <div class="mt-auto">
                            <div class="fw-bold text-primary">
                                Rp {{ number_format($relatedProduct->price, 0, ',', '.') }}
                            </div>
                            @if($relatedProduct->original_price && $relatedProduct->original_price >
                            $relatedProduct->price)
                            <div class="text-muted text-decoration-line-through small">
                                Rp {{ number_format($relatedProduct->original_price, 0, ',', '.') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer bg-transparent">
                        <a href="{{ route('products.show', $relatedProduct->slug) }}"
                            class="btn btn-outline-primary btn-sm w-100">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Change main product image
function changeMainImage(src) {
    document.querySelector('.img-fluid').src = src;
}

// Toggle wishlist
function toggleWishlist(productId) {
    // Add your wishlist logic here
    fetch(`/wishlist/toggle/${productId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button state
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            if (data.added) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.remove('btn-outline-danger');
                button.classList.add('btn-danger');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('btn-danger');
                button.classList.add('btn-outline-danger');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Share product
function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $product->name }}',
            text: 'Check out this product: {{ $product->name }}',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Product link copied to clipboard!');
        });
    }
}
</script>
@endpush
@endsection