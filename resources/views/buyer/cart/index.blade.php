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
                            <input class="form-check-input" type="checkbox" id="selectAll" x-model="selectAll"
                                @change="toggleSelectAll()">
                            <label class="form-check-label" for="selectAll">
                                Select All (<span x-text="cartItems.length"></span> items)
                            </label>
                        </div>
                        <button class="btn btn-link text-danger ms-auto p-0" x-show="selectedItems.length > 0"
                            @click="removeSelected">
                            <i class="fas fa-trash-alt me-1"></i> Delete (<span x-text="selectedItems.length"></span>)
                        </button>
                    </div>

                    <hr>

                    <!-- Cart Items by Store - FIXED VERSION -->
                    @foreach($cartItemsByStore as $storeId => $items)
                    @php
                    $store = $items[0]->product->seller->store ?? null;
                    @endphp

                    @if($store)
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input store-checkbox" type="checkbox"
                                    :id="'store-' + {{ $storeId }}" @change="toggleStore({{ $storeId }})"
                                    :checked="isStoreSelected({{ $storeId }})">
                                <label class="form-check-label" :for="'store-' + {{ $storeId }}">
                                    @if($store->logo)
                                    <img src="{{ Storage::url($store->logo) }}" class="rounded-circle me-2"
                                        style="width: 24px; height: 24px; object-fit: cover;" alt="{{ $store->name }}">
                                    @else
                                    <i class="fas fa-store me-2"></i>
                                    @endif
                                    {{ $store->name }} ({{ count($items) }} items)
                                </label>
                            </div>
                        </div>

                        @foreach($items as $item)
                        @if($item->product && $item->product->seller && $item->product->seller->store)
                        <div class="card mb-3 border">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input item-checkbox" type="checkbox"
                                                :id="'item-' + {{ $item->id }}" data-store="{{ $storeId }}"
                                                value="{{ $item->id }}" @change="updateSelectAllStatus()"
                                                x-model="selectedItems">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        @if($item->product->image)
                                        <img src="{{ Storage::url($item->product->image) }}" class="img-fluid rounded"
                                            alt="{{ $item->product->name }}">
                                        @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                            style="height: 80px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                        <p class="text-muted small mb-0">
                                            @if($item->product->original_price && $item->product->original_price >
                                            $item->product->price)
                                            <span class="text-danger">Rp
                                                {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                            <span class="text-decoration-line-through ms-1">Rp
                                                {{ number_format($item->product->original_price, 0, ',', '.') }}</span>
                                            @else
                                            <span class="text-danger">Rp
                                                {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                            @endif
                                        </p>
                                        @if($item->product->stock <= 5) <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Only {{ $item->product->stock }} left!
                                            </small>
                                            @endif
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                                @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) - 1)"
                                                :disabled="getItemQuantity({{ $item->id }}) <= 1">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control form-control-sm text-center"
                                                :value="getItemQuantity({{ $item->id }})" min="1"
                                                max="{{ $item->product->stock }}"
                                                @input="updateQuantity({{ $item->id }}, parseInt($event.target.value) || 1)">
                                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                                @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) + 1)"
                                                :disabled="getItemQuantity({{ $item->id }}) >= {{ $item->product->stock }}">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            Available: {{ $item->product->stock }} items
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <div class="text-danger fw-bold">
                                            Rp <span
                                                x-text="formatPrice(getItemPrice({{ $item->id }}) * getItemQuantity({{ $item->id }}))"></span>
                                        </div>
                                        <button class="btn btn-link text-danger p-0 mt-2"
                                            @click="removeItem({{ $item->id }})">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Handle invalid cart items -->
                        <div class="card mb-3 border border-danger">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            This product is no longer available
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-link text-danger p-0"
                                            @click="removeItem({{ $item->id }})">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif

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
                        <span class="fw-bold">Rp <span x-text="formatPrice(calculateSubtotal())"></span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping Fee</span>
                        <span class="fw-bold" x-text="selectedItems.length > 0 ? 'Free' : 'Rp 0'"></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-danger">Rp <span x-text="formatPrice(calculateTotal())"></span></span>
                    </div>
                    <button class="btn btn-primary w-100 py-2" :disabled="selectedItems.length === 0" @click="checkout">
                        Proceed to Checkout (<span x-text="selectedItems.length"></span> items)
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
</div>
@endsection

@section('scripts')
<script>
    function cartPage() {
        return {
            selectedItems: [],
            selectAll: false,
            shippingFee: 0,

            // FIXED: Properly structured cart items data
            cartItems: [
                @foreach($cartItems as $item)
                {
                    id: {{ $item->id }},
                    quantity: {{ $item->quantity }},
                    product: {
                        id: {{ $item->product->id ?? 0 }},
                        name: "{{ $item->product->name ?? 'Unknown Product' }}",
                        price: {{ $item->product->price ?? 0 }},
                        stock: {{ $item->product->stock ?? 0 }},
                        @if($item->product && $item->product->seller && $item->product->seller->store)
                        seller: {
                            store: {
                                id: {{ $item->product->seller->store->id }},
                                name: "{{ $item->product->seller->store->name }}"
                            }
                        }
                        @else
                        seller: null
                        @endif
                    }
                }@if(!$loop->last),@endif
                @endforeach
            ],

            init() {
                console.log('Cart initialized with items:', this.cartItems);
            },

            // FIXED: Select All toggle
            toggleSelectAll() {
                if (this.selectAll) {
                    // Select all valid items
                    this.selectedItems = this.cartItems
                        .filter(item => item.product && item.product.id > 0)
                        .map(item => item.id);
                } else {
                    // Deselect all
                    this.selectedItems = [];
                }
                console.log('Select all toggled. Selected items:', this.selectedItems);
            },

            // FIXED: Store toggle
            toggleStore(storeId) {
                const storeItems = this.cartItems
                    .filter(item => {
                        return item.product &&
                               item.product.seller &&
                               item.product.seller.store &&
                               item.product.seller.store.id === storeId;
                    })
                    .map(item => item.id);

                const allStoreItemsSelected = storeItems.every(itemId =>
                    this.selectedItems.includes(itemId)
                );

                if (allStoreItemsSelected) {
                    // Remove all store items from selection
                    this.selectedItems = this.selectedItems.filter(id => !storeItems.includes(id));
                } else {
                    // Add all store items to selection
                    storeItems.forEach(itemId => {
                        if (!this.selectedItems.includes(itemId)) {
                            this.selectedItems.push(itemId);
                        }
                    });
                }

                this.updateSelectAllStatus();
                console.log('Store toggled. Selected items:', this.selectedItems);
            },

            // FIXED: Check if store is selected
            isStoreSelected(storeId) {
                const storeItems = this.cartItems
                    .filter(item => {
                        return item.product &&
                               item.product.seller &&
                               item.product.seller.store &&
                               item.product.seller.store.id === storeId;
                    })
                    .map(item => item.id);

                return storeItems.length > 0 &&
                       storeItems.every(itemId => this.selectedItems.includes(itemId));
            },

            updateSelectAllStatus() {
                const validItems = this.cartItems.filter(item => item.product && item.product.id > 0);
                this.selectAll = validItems.length > 0 &&
                                validItems.every(item => this.selectedItems.includes(item.id));
            },

            // FIXED: Get current quantity
            getItemQuantity(itemId) {
                const item = this.cartItems.find(item => item.id === itemId);
                return item ? item.quantity : 1;
            },

            // FIXED: Get item price
            getItemPrice(itemId) {
                const item = this.cartItems.find(item => item.id === itemId);
                return item && item.product ? item.product.price : 0;
            },

            // FIXED: Calculate subtotal with precision
            calculateSubtotal() {
                const total = this.cartItems
                    .filter(item => this.selectedItems.includes(item.id))
                    .reduce((sum, item) => {
                        if (item.product && item.product.price) {
                            return sum + (item.product.price * item.quantity);
                        }
                        return sum;
                    }, 0);

                return Math.round(total); // Ensure integer result
            },

            calculateTotal() {
                return this.calculateSubtotal() + (this.selectedItems.length > 0 ? this.shippingFee : 0);
            },

            // FIXED: Format price properly
            formatPrice(price) {
                // Ensure it's a number and format with dots
                const numPrice = typeof price === 'number' ? price : parseInt(price) || 0;
                return numPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },

            // FIXED: Update quantity
            updateQuantity(itemId, quantity) {
                if (quantity < 1) return;

                const item = this.cartItems.find(item => item.id === itemId);
                if (!item || !item.product) return;

                const maxStock = item.product.stock;
                if (quantity > maxStock) {
                    quantity = maxStock;
                    alert(`Maximum quantity is ${maxStock}`);
                }

                // Update local quantity immediately for UI responsiveness
                item.quantity = quantity;

                // Update via AJAX
                fetch('/cart/update', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_item_id: itemId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // Revert on failure
                        alert(data.message || 'Failed to update quantity');
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error updating quantity:', error);
                    alert('An error occurred while updating the quantity');
                    location.reload();
                });
            },

            removeItem(itemId) {
                if (!confirm('Are you sure you want to remove this item from your cart?')) return;

                fetch('/cart/remove', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_item_id: itemId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove from selected items
                        this.selectedItems = this.selectedItems.filter(id => id !== itemId);

                        // Remove from cart items
                        const index = this.cartItems.findIndex(item => item.id === itemId);
                        if (index !== -1) {
                            this.cartItems.splice(index, 1);
                        }

                        // Update UI
                        this.updateSelectAllStatus();

                        // Reload page to reflect changes
                        setTimeout(() => window.location.reload(), 500);
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
                if (!confirm(`Are you sure you want to remove ${this.selectedItems.length} selected items from your cart?`)) return;

                fetch('/cart/remove-multiple', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_item_ids: this.selectedItems
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
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
            if (this.selectedItems.length === 0) {
            alert('Please select items to checkout');
            return;
            }

            // Create a form and submit it to checkout form
            const form = document.createElement('form');
            form.method = 'GET'; // Use GET to go to checkout form
            form.action = '{{ route("checkout.index") }}';

            // FIXED: Send as cart_items[] (not cart_item_ids[])
            this.selectedItems.forEach(itemId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cart_items[]'; // Changed from cart_item_ids[]
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