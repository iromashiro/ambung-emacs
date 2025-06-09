<div class="card h-100 product-card border-0 shadow-sm" x-data="{ showQuickAdd: false }">
    <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
        <div class="position-relative">
            <img src="{{ $product->image_url ?? asset('images/products/default.jpg') }}" 
                 class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $product->name }}">
            
            @if($product->discount_percentage > 0)
                <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 m-2 rounded-pill">
                    -{{ $product->discount_percentage }}%
                </div>
            @endif
        </div>
    </a>
    
    <div class="card-body d-flex flex-column" 
         @mouseenter="showQuickAdd = true" 
         @mouseleave="showQuickAdd = false">
        <a href="{{ route('stores.show', $product->store) }}" class="text-decoration-none">
            <p class="card-text small text-muted mb-1">{{ $product->store->name }}</p>
        </a>
        
        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
            <h6 class="card-title text-dark mb-1 text-truncate">{{ $product->name }}</h6>
        </a>
        
        <div class="mb-2">
            @if($product->discount_percentage > 0)
                <span class="text-danger fw-bold">Rp {{ number_format($product->discounted_price, 0, ',', '.') }}</span>
                <span class="text-muted text-decoration-line-through small">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            @else
                <span class="text-danger fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            @endif
        </div>
        
        <div class="d-flex align-items-center mb-2">
            <div class="text-warning me-1">
                <i class="fas fa-star"></i>
            </div>
            <span class="small">{{ number_format($product->average_rating, 1) }} ({{ $product->reviews_count ?? 0 }})</span>
        </div>
        
        <div x-show="showQuickAdd" x-transition class="mt-auto">
            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                </button>
            </form>
        </div>
    </div>
</div>