@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container">
    <div class="row g-4">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body" x-data="filterComponent()">
                    <form id="filterForm" action="{{ route('products.index') }}" method="GET">
                        <!-- Preserve other parameters -->
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <!-- Category Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Categories</h6>
                            <div class="overflow-auto" style="max-height: 200px;">
                                @foreach($categories as $category)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                        id="category{{ $category->id }}" value="{{ $category->id }}"
                                        {{ in_array($category->id, $selectedCategories ?? []) ? 'checked' : '' }}
                                        @change="submitForm">
                                    <label class="form-check-label" for="category{{ $category->id }}">
                                        {{ $category->name }} ({{ $category->products_count ?? 0 }})
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Price Range</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="min_price"
                                        placeholder="Min" value="{{ request('min_price') }}" @change="submitForm">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="max_price"
                                        placeholder="Max" value="{{ request('max_price') }}" @change="submitForm">
                                </div>
                            </div>
                            @if(isset($priceRange))
                            <small class="text-muted d-block mt-2">
                                Rp {{ number_format($priceRange['min'], 0, ',', '.') }} -
                                Rp {{ number_format($priceRange['max'], 0, ',', '.') }}
                            </small>
                            @endif
                        </div>

                        <!-- Sort Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Sort By</h6>
                            <select class="form-select form-select-sm" name="sort" @change="submitForm">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest
                                </option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price:
                                    Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                    Price: High to Low</option>
                                <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>Most
                                    Popular</option>
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        @if(request()->anyFilled(['categories', 'category', 'min_price', 'max_price', 'sort',
                        'search']))
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    @if(request('search'))
                    Search Results for "{{ request('search') }}"
                    @else
                    All Products
                    @endif
                </h4>
                <div class="text-muted">
                    {{ $products->total() }} products found
                </div>
            </div>

            <!-- Selected Filters -->
            @if($selectedCategories && count($selectedCategories) > 0)
            <div class="mb-3">
                <span class="text-muted me-2">Active filters:</span>
                @foreach($categories->whereIn('id', $selectedCategories) as $cat)
                <span class="badge bg-primary me-1">
                    {{ $cat->name }}
                    <a href="{{ route('products.index', array_merge(request()->except('categories'), ['categories' => array_diff($selectedCategories, [$cat->id])])) }}"
                        class="text-white ms-1">×</a>
                </span>
                @endforeach
            </div>
            @endif

            <!-- Products Grid -->
            <div class="row g-4">
                @forelse($products as $product)
                <div class="col-md-4 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none">
                            @if($product->images->isNotEmpty())
                            <img src="{{ Storage::url($product->images->first()->image_url) }}" class="card-img-top"
                                alt="{{ $product->name }}">
                            @else
                            <img src="{{ asset('images/no-image.png') }}" class="card-img-top"
                                alt="{{ $product->name }}">
                            @endif
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <a href="{{ route('products.show', $product->slug) }}"
                                    class="text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                            </h6>
                            <p class="text-muted small mb-2">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0 text-primary">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </span>
                                    @if($product->stock > 0)
                                    <span class="badge bg-success">In Stock</span>
                                    @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </div>
                                @auth
                                @if(auth()->user()->role === 'buyer')
                                <button class="btn btn-primary btn-sm w-100 mt-3"
                                    onclick="addToCart({{ $product->id }})"
                                    {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                </button>
                                @endif
                                @else
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm w-100 mt-3">
                                    <i class="fas fa-sign-in-alt me-1"></i> Login to Buy
                                </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5>No products found</h5>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            View All Products
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-5">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function filterComponent() {
    return {
        submitForm() {
            document.getElementById('filterForm').submit();
        }
    }
}

function addToCart(productId) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type'   : 'application/json',
            'Accept'         : 'application/json',     // <– penting
            'X-Requested-With': 'XMLHttpRequest',      // <– penting
            'X-CSRF-TOKEN'   : document
                                .querySelector('meta[name="csrf-token"]')
                                .content
        },
        credentials: 'same-origin',                    // bawa session cookie
        body: JSON.stringify({
            product_id: productId,
            quantity  : 1
        })
    })
    .then(async (response) => {
        if (!response.ok) {          // 401 / 419 / 422, dll.
            // Coba ambil text agar mudah di-debug
            const text = await response.text();
            console.error('Non-200 response', response.status, text);
            throw new Error(`HTTP ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Server malah balas HTML ⇒ kemungkinan redirect/login
            const text = await response.text();
            console.error('HTML received:', text.substring(0, 200));
            throw new Error('NON_JSON_RESPONSE');
        }

        return response.json();
    })
    .then(({ success, message, cart_count }) => {
        if (success) {
            updateCartCount(cart_count);
            alert(message ?? 'Produk masuk keranjang!');
        } else {
            alert(message ?? 'Gagal menambah produk');
        }
    })
    .catch((err) => {
        if (err.message === 'NON_JSON_RESPONSE') {
            // Arahkan user login atau tampilkan modal
            window.location.href = '{{ route('login') }}';
        } else {
            alert('Terjadi kesalahan, coba lagi');
        }
    });
}


function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-count');
            if (cartBadge && data.count !== undefined) {
                cartBadge.textContent = data.count;
            }
        });
}
</script>
@endsection

@section('styles')
<style>
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .product-card img {
        height: 200px;
        object-fit: cover;
    }
</style>
@endsection