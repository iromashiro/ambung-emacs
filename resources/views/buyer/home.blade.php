@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container">
    <!-- Hero Banner -->
    <div id="heroCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner rounded shadow">
            <div class="carousel-item active">
                <img src="{{ asset('images/banners/banner1.jpg') }}" class="d-block w-100" alt="Promo Banner">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Welcome to Ambung Emac</h2>
                    <p>Your one-stop shop for local UMKM products</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/banners/banner2.jpg') }}" class="d-block w-100" alt="Promo Banner">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Support Local Businesses</h2>
                    <p>Discover unique products from local entrepreneurs</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/banners/banner3.jpg') }}" class="d-block w-100" alt="Promo Banner">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Easy Cash on Delivery</h2>
                    <p>Pay when you receive your order</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    
    <!-- Featured Categories -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Featured Categories</h3>
            <a href="{{ route('categories.index') }}" class="text-decoration-none">View All</a>
        </div>
        <div class="row g-3">
            @foreach(\App\Models\Category::take(6)->get() as $category)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('products.index', ['category' => $category->id]) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <img src="{{ $category->image_url ?? asset('images/categories/default.jpg') }}" 
                                     class="img-fluid mb-3" style="height: 80px; object-fit: contain;" alt="{{ $category->name }}">
                                <h6 class="card-title text-dark">{{ $category->name }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Featured Products -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Featured Products</h3>
            <a href="{{ route('products.index') }}" class="text-decoration-none">View All</a>
        </div>
        <div class="row g-3">
            @foreach(\App\Models\Product::with('store')->where('featured', true)->take(8)->get() as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- New Arrivals -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">New Arrivals</h3>
            <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="text-decoration-none">View All</a>
        </div>
        <div class="row g-3">
            @foreach(\App\Models\Product::with('store')->latest()->take(8)->get() as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Popular Stores -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Popular Stores</h3>
            <a href="{{ route('stores.index') }}" class="text-decoration-none">View All</a>
        </div>
        <div class="row g-3">
            @foreach(\App\Models\Store::where('status', 'approved')->take(6)->get() as $store)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('stores.show', $store) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <img src="{{ $store->logo_url ?? asset('images/stores/default.jpg') }}" 
                                     class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;" alt="{{ $store->name }}">
                                <h6 class="card-title text-dark">{{ $store->name }}</h6>
                                <p class="card-text small text-muted">{{ $store->products_count ?? 0 }} Products</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Become a Seller Banner -->
    <div class="card bg-primary text-white mb-5">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>Become a Seller on Ambung Emac</h3>
                    <p class="mb-md-0">Start selling your products to thousands of customers today. Easy setup, no initial costs.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('seller.register') }}" class="btn btn-light">Start Selling</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection