@extends('layouts.app')

@section('title', $product->name)

@push('styles')
<style>
    .quantity-input {
        width: 60px;
        text-align: center;
        border: none;
        background: transparent;
    }

    .quantity-input:focus {
        outline: none;
        box-shadow: none;
    }

    .product-image {
        height: 400px;
        width: 100%;
        object-fit: cover;
        cursor: pointer;
    }

    .thumbnail-image {
        height: 80px;
        width: 80px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.3s;
    }

    .thumbnail-image:hover,
    .thumbnail-image.active {
        border-color: #0d6efd;
    }
</style>
@endpush

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
                    @if($product->images && $product->images->count() > 0)
                    <img id="mainImage" src="{{ Storage::url($product->images->first()->path) }}"
                        alt="{{ $product->name }}" class="img-fluid rounded shadow-sm product-image">
                    @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded product-image">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                    @endif
                </div>

                <!-- Additional Images (if any) -->
                @if($product->images && $product->images->count() > 1)
                <div class="row g-2">
                    @foreach($product->images as $index => $image)
                    <div class="col-3">
                        <img src="{{ Storage::url($image->path) }}" alt="{{ $product->name }}"
                            class="img-fluid rounded shadow-sm thumbnail-image {{ $index === 0 ? 'active' : '' }}"
                            onclick="changeMainImage('{{ Storage::url($image->path) }}', this)">
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
                    @if($product->seller && $product->seller->store)
                    <a href="#" class="text-decoration-none">
                        <span class="badge bg-primary">
                            <i class="fas fa-store me-1"></i>{{ $product->seller->store->name }}
                        </span>
                    </a>
                    @endif
                </div>

                <!-- Product Name -->
                <h1 class="h2 mb-3">{{ $product->name }}</h1>

                <!-- Product Price -->
                <div class="mb-3">
                    <span class="h3 text-primary fw-bold">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Product Rating & Reviews (Placeholder) -->
                <div class="mb-3">
                    <div class="d-flex align-items-center">
                        <div class="text-warning me-2">
                            @for($i = 1; $i <= 5; $i++) <i class="far fa-star"></i>
                                @endfor
                        </div>
                        <span class="text-muted">(0 reviews)</span>
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

                <!-- Add to Cart Form - FIXED QUANTITY CONTROLS -->
                @if($product->stock > 0)
                <div class="mb-4" x-data="{ quantity: 1, maxStock: {{ $product->stock }} }">
                    <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <label for="quantity" class="form-label">Quantity</label>
                                <div class="input-group" style="width: 140px;">
                                    <button type="button" class="btn btn-outline-secondary"
                                        @click="quantity = Math.max(1, quantity - 1)" :disabled="quantity <= 1">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control quantity-input" name="quantity"
                                        x-model="quantity" min="1" :max="maxStock"
                                        @input="quantity = Math.max(1, Math.min(maxStock, parseInt($event.target.value) || 1))">
                                    <button type="button" class="btn btn-outline-secondary"
                                        @click="quantity = Math.min(maxStock, quantity + 1)"
                                        :disabled="quantity >= maxStock">
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
                </div>
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

                <!-- Product Details - MENGGUNAKAN FIELD YANG ADA -->
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
                            <div class="col-sm-4"><strong>Product ID:</strong></div>
                            <div class="col-sm-8">#{{ $product->id }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4"><strong>Featured:</strong></div>
                            <div class="col-sm-8">
                                @if($product->is_featured)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>Featured Product
                                </span>
                                @else
                                <span class="text-muted">Regular Product</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products - FIXED IMAGE DISPLAY -->
    @if($relatedProducts && $relatedProducts->count() > 0)
    <div class="mt-5">
        <h3 class="mb-4">Related Products</h3>
        <div class="row g-4">
            @foreach($relatedProducts as $relatedProduct)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        @if($relatedProduct->images && $relatedProduct->images->count() > 0)
                        <img src="{{ Storage::url($relatedProduct->images->first()->path) }}" class="card-img-top"
                            alt="{{ $relatedProduct->name }}" style="height: 200px; object-fit: cover;"
                            onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5JbWFnZSBOb3QgRm91bmQ8L3RleHQ+PC9zdmc+';">
                        @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                        @endif

                        @if($relatedProduct->is_featured)
                        <span class="position-absolute top-0 start-0 badge bg-warning text-dark m-2">
                            <i class="fas fa-star me-1"></i>Featured
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
                            <div class="text-muted small">
                                Stock: {{ $relatedProduct->stock }}
                            </div>
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
<!-- Alpine.js for quantity controls -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    // Change main product image
function changeMainImage(src, element) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.src = src;

        // Update active thumbnail
        document.querySelectorAll('.thumbnail-image').forEach(img => {
            img.classList.remove('active');
        });
        element.classList.add('active');
    }
}

// Toggle wishlist
function toggleWishlist(productId) {
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
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Product link copied to clipboard!');
        });
    }
}

// Add to cart form submission
document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Product added to cart successfully!');

            // Update cart count if exists
            const cartCount = document.querySelector('.cart-count');
            if (cartCount && data.cartCount) {
                cartCount.textContent = data.cartCount;
            }
        } else {
            alert(data.message || 'Failed to add product to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding to cart');
    });
});
</script>
@endpush
@endsection