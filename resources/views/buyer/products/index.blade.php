@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container" x-data="productsPage()">
    <div class="row g-4">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" action="{{ route('products.index') }}" method="GET">
                        <!-- Category Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Categories</h6>
                            <div class="overflow-auto" style="max-height: 200px;">
                                @foreach($categories as $category)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                        id="category{{ $category->id }}" value="{{ $category->id }}"
                                        {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                        @change="submitForm()">
                                    <label class="form-check-label" for="category{{ $category->id }}">
                                        {{ $category->name }} ({{ $category->products_count }})
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
                                    <input type="number" class="form-control" name="min_price" placeholder="Min"
                                        value="{{ request('min_price') }}" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" placeholder="Max"
                                        value="{{ request('max_price') }}" min="0">
                                </div>
                            </div>
                            <div class="d-grid mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    @click="submitForm()">Apply</button>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Rating</h6>
                            @for($i = 5; $i >= 1; $i--)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="ratings[]" id="rating{{ $i }}"
                                    value="{{ $i }}" {{ in_array($i, request('ratings', [])) ? 'checked' : '' }}
                                    @change="submitForm()">
                                <label class="form-check-label" for="rating{{ $i }}">
                                    <div class="text-warning">
                                        @for($j = 1; $j <= 5; $j++) <i class="fas fa-star{{ $j <= $i ? '' : '-o' }}">
                                            </i>
                                            @endfor
                                    </div>
                                </label>
                            </div>
                            @endfor
                        </div>

                        <!-- Location Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Location</h6>
                            <select class="form-select" name="location" @change="submitForm()">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                <option value="{{ $location }}"
                                    {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Other Filters -->
                        <div class="mb-4">
                            <h6 class="mb-3">Other Filters</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="free_shipping" id="freeShipping"
                                    value="1" {{ request('free_shipping') ? 'checked' : '' }} @change="submitForm()">
                                <label class="form-check-label" for="freeShipping">
                                    Free Shipping
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="has_discount" id="hasDiscount"
                                    value="1" {{ request('has_discount') ? 'checked' : '' }} @change="submitForm()">
                                <label class="form-check-label" for="hasDiscount">
                                    Discounted Items
                                </label>
                            </div>
                        </div>

                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">

                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-secondary" @click="resetFilters">
                                <i class="fas fa-redo me-1"></i> Reset Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Search and Sort -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <form action="{{ route('products.index') }}" method="GET" class="d-flex">
                                <input type="hidden" name="categories"
                                    value="{{ implode(',', request('categories', [])) }}">
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                                <input type="hidden" name="ratings" value="{{ implode(',', request('ratings', [])) }}">
                                <input type="hidden" name="location" value="{{ request('location') }}">
                                <input type="hidden" name="free_shipping" value="{{ request('free_shipping') }}">
                                <input type="hidden" name="has_discount" value="{{ request('has_discount') }}">
                                <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">

                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                        placeholder="Search products..." value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-md-end">
                                <select class="form-select" name="sort" onchange="updateSort(this.value)">
                                    <option value="newest"
                                        {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Newest</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                                        Price: Low to High</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                        Price: High to Low</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest
                                        Rating</option>
                                    <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>
                                        Popularity</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of
                    {{ $products->total() }} products</p>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary {{ $viewMode == 'grid' ? 'active' : '' }}"
                        onclick="setViewMode('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary {{ $viewMode == 'list' ? 'active' : '' }}"
                        onclick="setViewMode('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            @if($products->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3>No Products Found</h3>
                    <p class="text-muted mb-4">We couldn't find any products matching your criteria.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Clear Filters</a>
                </div>
            </div>
            @else
            @if($viewMode == 'grid')
            <div class="row g-3">
                @foreach($products as $product)
                <div class="col-md-6 col-lg-4">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>
            @else
            <div class="row g-3">
                @foreach($products as $product)
                <div class="col-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <a href="{{ route('products.show', $product) }}">
                                    <img src="{{ $product->image_url ?? asset('images/product-default.jpg') }}"
                                        class="img-fluid rounded-start h-100" alt="{{ $product->name }}"
                                        style="object-fit: cover;">
                                </a>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body d-flex flex-column h-100">
                                    <div class="mb-auto">
                                        <p class="card-text small text-muted mb-1">{{ $product->store->name }}</p>
                                        <h5 class="card-title">
                                            <a href="{{ route('products.show', $product) }}"
                                                class="text-decoration-none text-dark">{{ $product->name }}</a>
                                        </h5>

                                        <div class="mb-2">
                                            @if($product->discount_percentage > 0)
                                            <span class="text-danger fw-bold">Rp
                                                {{ number_format($product->final_price, 0, ',', '.') }}</span>
                                            <span class="text-muted text-decoration-line-through small ms-1">Rp
                                                {{ number_format($product->price, 0, ',', '.') }}</span>
                                            <span
                                                class="badge bg-danger ms-2">-{{ $product->discount_percentage }}%</span>
                                            @else
                                            <span class="text-danger fw-bold">Rp
                                                {{ number_format($product->price, 0, ',', '.') }}</span>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center mb-2">
                                            <div class="text-warning small">
                                                @for($i = 1; $i <= 5; $i++) <i
                                                    class="fas fa-star{{ $i <= $product->rating ? '' : '-o' }}"></i>
                                                    @endfor
                                            </div>
                                            <span class="ms-1 small text-muted">({{ $product->reviews_count }})</span>
                                            <span class="mx-2 text-muted">|</span>
                                            <span class="small text-muted">{{ $product->sold_count }} Sold</span>
                                        </div>

                                        <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <p class="card-text small text-muted mb-0">
                                            <i class="fas fa-map-marker-alt me-1"></i> {{ $product->store->city }}
                                        </p>

                                        <form action="{{ route('cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="mt-4 d-flex justify-content-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function productsPage() {
        return {
            submitForm() {
                document.getElementById('filterForm').submit();
            },

            resetFilters() {
                window.location.href = "{{ route('products.index') }}";
            }
        };
    }

    function updateSort(value) {
        const url = new URL(window.location);
        url.searchParams.set('sort', value);
        window.location.href = url.toString();
    }

    function setViewMode(mode) {
        const url = new URL(window.location);
        url.searchParams.set('view', mode);
        window.location.href = url.toString();
    }
</script>
@endpush
@endsection