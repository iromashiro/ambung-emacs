@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container" x-data="cartPage()">
    <h1 class="h3 mb-4">Shopping Cart</h1>
    
    @if(count($cartItems) > 0)
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll" 
                                       x-model="selectAll" @click="toggleSelectAll">
                                <label class="form-check-label" for="selectAll">
                                    Select All
                                </label>
                            </div>
                            <button class="btn btn-link text-danger ms-auto p-0" 
                                    x-show="selectedItems.length > 0"
                                    @click="removeSelected">
                                <i class="fas fa-trash-alt me-1"></i> Delete
                            </button>
                        </div>
                        
                        <hr>
                        
                        <!-- Cart Items by Store -->
                        @foreach($cartItemsByStore as $storeId => $items)
                            @php
                                $store = \App\Models\Store::find($storeId);
                            @endphp
                            
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input store-checkbox" type="checkbox" 
                                               id="store-{{ $storeId }}" 
                                               x-model="storeSelected[{{ $storeId }}]"
                                               @click="toggleStore({{ $storeId }})">
                                        <label class="form-check-label" for="store-{{ $storeId }}">
                                            <img src="{{ $store->logo_url ?? asset('images/stores/default.jpg') }}" 
                                                 class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;" 
                                                 alt="{{ $store->name }}">
                                            {{ $store->name }}
                                        </label>
                                    </div>
                                </div>
                                
                                @foreach($items as $item)
                                    <div class="card mb-3 border">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="form-check">
                                                        <input class="form-check-input item-checkbox" type="checkbox" 
                                                               id="item-{{ $item->id }}" 
                                                               data-store="{{ $storeId }}"
                                                               value="{{ $item->id }}"
                                                               x-model="selectedItems">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <img src="{{ $item->product->image_url ?? asset('images/products/default.jpg') }}" 
                                                         class="img-fluid rounded" alt="{{ $item->product->name }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                                    <p class="text-muted small mb-0">
                                                        @if($item->product->discount_percentage > 0)
                                                            <span class="text-danger">Rp {{ number_format($item->product->discounted_price, 0, ',', '.') }}</span>
                                                            <span class="text-decoration-line-through ms-1">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                                        @else
                                                            <span class="text-danger">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group" x-data="{ quantity: {{ $item->quantity }} }">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                                @click="updateQuantity({{ $item->id }}, quantity - 1)"
                                                                :disabled="quantity <= 1">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" class="form-control form-control-sm text-center" 
                                                               x-model="quantity" min="1" max="{{ $item->product->stock }}"
                                                               @change="updateQuantity({{ $item->id }}, quantity)">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                                @click="updateQuantity({{ $item->id }}, quantity + 1)"
                                                                :disabled="quantity >= {{ $item->product->stock }}">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <div class="small text-muted mt-1">
                                                        Available: {{ $item->product->stock }} items
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-end">
                                                    <div class="text-danger fw-bold">
                                                        Rp {{ number_format($item->product->discount_percentage > 0 ? 
                                                            $item->product->discounted_price * $item->quantity : 
                                                            $item->product->price * $item->quantity, 0, ',', '.') }}
                                                    </div>
                                                    <button class="btn btn-link text-danger p-0 mt-2" 
                                                            @click="removeItem({{ $item->id }})">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<span x-text="selectedItems.length"></span> items)</span>
                            <span class="fw-bold" x-text="'Rp ' + formatPrice(calculateSubtotal())"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping Fee</span>
                            <span class="fw-bold" x-text="selectedItems.length > 0 ? 'Rp ' + formatPrice(shippingFee) : 'Rp 0'"></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold text-danger" x-text="'Rp ' + formatPrice(calculateTotal())"></span>
                        </div>
                        <button class="btn btn-primary w-100 py-2" 
                                :disabled="selectedItems.length === 0"
                                @click="checkout">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any products to your cart yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">
                    Continue Shopping
                </a>
            </div>
        </div>
    @endif
    
    <!-- Recently Viewed Products -->
    @if(count($recentlyViewed ?? []) > 0)
        <div class="mt-5">
            <h3 class="mb-3">Recently Viewed</h3>
            <div class="row g-3">
                @foreach($recentlyViewed as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        @include('components.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
    function cartPage() {
        return {
            selectedItems: [],
            selectAll: false,
            storeSelected: {},
            shippingFee: 10000, // Fixed shipping fee for now
            cartItems: @json($cartItems),
            
            init() {
                // Initialize store selection tracking
                @foreach($cartItemsByStore as $storeId => $items)
                    this.storeSelected[{{ $storeId }}] = false;
                @endforeach
            },
            
            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedItems = this.cartItems.map(item => item.id);
                    
                    // Select all stores
                    Object.keys(this.storeSelected).forEach(storeId => {
                        this.storeSelected[storeId] = true;
                    });
                } else {
                    this.selectedItems = [];
                    
                    // Deselect all stores
                    Object.keys(this.storeSelected).forEach(storeId => {
                        this.storeSelected[storeId] = false;
                    });
                }
            },
            
            toggleStore(storeId) {
                const storeItems = this.cartItems.filter(item => item.product.store_id === storeId).map(item => item.id);
                
                if (this.storeSelected[storeId]) {
                    // Add all store items to selection if not already there
                    storeItems.forEach(itemId => {
                        if (!this.selectedItems.includes(itemId)) {
                            this.selectedItems.push(itemId);
                        }
                    });
                } else {
                    // Remove all store items from selection
                    this.selectedItems = this.selectedItems.filter(id => !storeItems.includes(id));
                }
                
                // Update selectAll status
                this.updateSelectAllStatus();
            },
            
            updateSelectAllStatus() {
                this.selectAll = this.selectedItems.length === this.cartItems.length;
            },
            
            calculateSubtotal() {
                return this.cartItems
                    .filter(item => this.selectedItems.includes(item.id))
                    .reduce((total, item) => {
                        const price = item.product.discount_percentage > 0 
                            ? item.product.discounted_price 
                            : item.product.price;
                        return total + (price * item.quantity);
                    }, 0);
            },
            
            calculateTotal() {
                return this.selectedItems.length > 0 
                    ? this.calculateSubtotal() + this.shippingFee 
                    : 0;
            },
            
            formatPrice(price) {
                return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },
            
            updateQuantity(itemId, quantity) {
                if (quantity < 1) return;
                
                const item = this.cartItems.find(item => item.id === itemId);
                if (!item) return;
                
                const maxStock = item.product.stock;
                if (quantity > maxStock) {
                    quantity = maxStock;
                }
                
                // Update via AJAX
                fetch(`/cart/update/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ quantity })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update local item quantity
                        item.quantity = quantity;
                    } else {
                        alert(data.message || 'Failed to update quantity');
                    }
                })
                .catch(error => {
                    console.error('Error updating quantity:', error);
                    alert('An error occurred while updating the quantity');
                });
            },
            
            removeItem(itemId) {
                if (!confirm('Are you sure you want to remove this item from your cart?')) return;
                
                fetch(`/cart/remove/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove from selected items if present
                        this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                        
                        // Remove from cart items
                        const index = this.cartItems.findIndex(item => item.id === itemId);
                        if (index !== -1) {
                            this.cartItems.splice(index, 1);
                        }
                        
                        // Reload page to reflect changes
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to remove item');
                    }
                })
                .catch(error => {
                    console.error('Error removing item:', error);
                    alert('An error occurred while removing the item');
                });
            },
            
            removeSelected() {
                if (this.selectedItems.length === 0) return;
                if (!confirm('Are you sure you want to remove the selected items from your cart?')) return;
                
                fetch('/cart/remove-multiple', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ items: this.selectedItems })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to reflect changes
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to remove items');
                    }
                })
                .catch(error => {
                    console.error('Error removing items:', error);
                    alert('An error occurred while removing the items');
                });
            },
            
            checkout() {
                if (this.selectedItems.length === 0) return;
                
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('checkout.index') }}';
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);
                
                // Add selected items
                this.selectedItems.forEach(itemId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'cart_items[]';
                    input.value = itemId;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        };
    }
</script>
@endsection