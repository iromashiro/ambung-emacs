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
                        <!-- Category Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Categories</h6>
                            <div class="overflow-auto" style="max-height: 200px;">
                                @foreach($categories as $category)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                        id="category{{ $category->id }}" value="{{ $category->id }}"
                                        {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
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
                                    @click="submitForm">Apply</button>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-4">
                            <h6 class="mb-3">Sort By</h6>
                            <select class="form-select" name="sort" @change="submitForm">
                                <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>
                                    Newest</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price:
                                    Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                    Price: High to Low</option>
                                <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>
                                    Popularity</option>
                            </select>
                        </div>

                        <input type="hidden" name="search" value="{{ request('search') }}">

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
                                @foreach(request('categories', []) as $category)
                                <input type="hidden" name="categories[]" value="{{ $category }}">
                                @endforeach
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
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
                                <select class="form-select" onchange="updateSort(this.value)" style="width: auto;">
                                    <option value="newest"
                                        {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Newest</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                                        Price: Low to High</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                        Price: High to Low</option>
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
                <p class="mb-0">
                    @if($products->total() > 0)
                    Showing {{ $products->firstItem() }} - {{ $products->lastItem() }} of {{ $products->total() }}
                    products
                    @else
                    No products found
                    @endif
                </p>

                <div class="btn-group" role="group">
                    <button type="button"
                        class="btn btn-outline-secondary {{ request('view', 'grid') == 'grid' ? 'active' : '' }}"
                        onclick="setViewMode('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button"
                        class="btn btn-outline-secondary {{ request('view') == 'list' ? 'active' : '' }}"
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
            @if(request('view', 'grid') == 'grid')
            <div class="row g-3">
                @foreach($products as $product)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <a href="{{ route('products.show', $product->slug ?? $product->id) }}">
                            @if($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" class="card-img-top"
                                alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                            @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                            @endif
                        </a>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text small text-muted mb-1">
                                {{ $product->seller->store->name ?? 'Unknown Store' }}
                            </p>
                            <h5 class="card-title">
                                <a href="{{ route('products.show', $product->slug ?? $product->id) }}"
                                    class="text-decoration-none text-dark">{{ $product->name }}</a>
                            </h5>

                            <div class="mb-2">
                                <span class="text-danger fw-bold">Rp
                                    {{ number_format($product->price, 0, ',', '.') }}</span>
                            </div>

                            <div class="mt-auto">
                                <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm w-100"
                                        {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-cart-plus me-1"></i>
                                        {{ $product->stock <= 0 ? 'Out of Stock' : 'Add to Cart' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <!-- List View -->
            <div class="row g-3">
                @foreach($products as $product)
                <div class="col-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <a href="{{ route('products.show', $product->slug ?? $product->id) }}">
                                    @if($product->image_path)
                                    <img src="{{ Storage::url($product->image_path) }}"
                                        class="img-fluid rounded-start h-100" alt="{{ $product->name }}"
                                        style="object-fit: cover; min-height: 200px;">
                                    @else
                                    <div class="bg-light d-flex align-items-center justify-content-center h-100"
                                        style="min-height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                    @endif
                                </a>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body d-flex flex-column h-100">
                                    <div class="mb-auto">
                                        <p class="card-text small text-muted mb-1">
                                            {{ $product->seller->store->name ?? 'Unknown Store' }}
                                        </p>
                                        <h5 class="card-title">
                                            <a href="{{ route('products.show', $product->slug ?? $product->id) }}"
                                                class="text-decoration-none text-dark">{{ $product->name }}</a>
                                        </h5>

                                        <div class="mb-2">
                                            <span class="text-danger fw-bold">Rp
                                                {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>

                                        <p class="card-text">{{ Str::limit($product->description, 100) }}</p>

                                        @if($product->stock <= 0) <span class="badge bg-danger">Out of Stock</span>
                                            @else
                                            <span class="badge bg-success">{{ $product->stock }} in stock</span>
                                            @endif
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <p class="card-text small text-muted mb-0">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $product->seller->store->city ?? 'Unknown Location' }}
                                        </p>

                                        <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-primary btn-sm"
                                                {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-cart-plus me-1"></i>
                                                {{ $product->stock <= 0 ? 'Out of Stock' : 'Add to Cart' }}
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

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Alpine.js component for filters
    function filterComponent() {
        return {
            submitForm() {
                document.getElementById('filterForm').submit();
            },

            resetFilters() {
                window.location.href = "{{ route('products.index') }}";
            }
        };
    }

    // Global functions for sorting and view mode
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
@endsection