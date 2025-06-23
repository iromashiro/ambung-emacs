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

                    <!-- Cart Items by Store -->
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
                                            <!-- FIXED: Add @change event to trigger calculation -->
                                            <input class="form-check-input item-checkbox" type="checkbox"
                                                :id="'item-' + {{ $item->id }}" data-store="{{ $storeId }}"
                                                value="{{ $item->id }}"
                                                @change="updateSelectAllStatus(); updateTotals()"
                                                x-model="selectedItems">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        @if($item->product->images && $item->product->images->count() > 0)
                                        <img src="{{ Storage::url($item->product->images->first()->path) }}"
                                            class="img-fluid rounded" alt="{{ $item->product->name }}">
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
                                            <span class="text-danger">Rp
                                                {{ number_format($item->product->price, 0, ',', '.') }}</span>
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
                    <!-- FIXED: Use computed values with proper reactivity -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal (<span x-text="selectedItems.length"></span> items)</span>
                        <span class="fw-bold">Rp <span x-text="subtotalFormatted"></span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping Fee</span>
                        <span class="fw-bold" x-text="selectedItems.length > 0 ? 'Free' : 'Rp 0'"></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-danger">Rp <span x-text="totalFormatted"></span></span>
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

{{-- resources/views/buyer/cart/index.blade.php - BAGIAN SCRIPT YANG DIPERBAIKI --}}
{{-- resources/views/buyer/cart/index.blade.php - BAGIAN SCRIPT YANG DIPERBAIKI --}}

@section('scripts')
<script>
    function cartPage() {
        return {
            selectedItems: [],
            selectAll: false,
            shippingFee: 0,

            // Cart items data - FIXED: Pastikan data structure benar
            cartItems: [
                @foreach($cartItems as $item)
                @if($item->product)
                {
                    id: {{ $item->id }},
                    quantity: {{ $item->quantity }},
                    product: {
                        id: {{ $item->product->id }},
                        name: "{{ addslashes($item->product->name) }}",
                        price: {{ $item->product->price }},
                        stock: {{ $item->product->stock }},
                        @if($item->product->seller && $item->product->seller->store)
                        seller: {
                            store: {
                                id: {{ $item->product->seller->store->id }},
                                name: "{{ addslashes($item->product->seller->store->name) }}"
                            }
                        }
                        @else
                        seller: null
                        @endif
                    }
                }@if(!$loop->last),@endif
                @endif
                @endforeach
            ],

            init() {
                console.log('Cart initialized with items:', this.cartItems);
                // FIXED: Auto-select semua items saat load
                this.selectedItems = this.cartItems.map(item => item.id);
                this.updateSelectAllStatus();
                console.log('Initial selected items:', this.selectedItems);
                console.log('Initial subtotal:', this.subtotal);
            },

            // FIXED: Computed properties dengan debugging
            get subtotal() {
                let total = 0;
                console.log('Calculating subtotal for selected items:', this.selectedItems);

                this.selectedItems.forEach(itemId => {
                    const item = this.cartItems.find(cartItem => cartItem.id === itemId);
                    if (item && item.product && item.product.price) {
                        const itemTotal = item.product.price * item.quantity;
                        console.log(`Item ${item.id}: ${item.product.price} x ${item.quantity} = ${itemTotal}`);
                        total += itemTotal;
                    }
                });

                console.log('Total subtotal:', total);
                return total;
            },

            get subtotalFormatted() {
                return this.formatPrice(this.subtotal);
            },

            get total() {
                return this.subtotal + (this.selectedItems.length > 0 ? this.shippingFee : 0);
            },

            get totalFormatted() {
                return this.formatPrice(this.total);
            },

            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedItems = [...this.cartItems.map(item => item.id)];
                } else {
                    this.selectedItems = [];
                }
                console.log('Select all toggled. Selected items:', this.selectedItems);
                console.log('New subtotal:', this.subtotal);
            },

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
                    // Remove all store items
                    this.selectedItems = this.selectedItems.filter(id => !storeItems.includes(id));
                } else {
                    // Add all store items
                    storeItems.forEach(itemId => {
                        if (!this.selectedItems.includes(itemId)) {
                            this.selectedItems.push(itemId);
                        }
                    });
                }

                this.updateSelectAllStatus();
                console.log('Store toggled. Selected items:', this.selectedItems);
                console.log('New subtotal:', this.subtotal);
            },

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
                this.selectAll = this.cartItems.length > 0 &&
                                this.cartItems.every(item => this.selectedItems.includes(item.id));
            },

            getItemQuantity(itemId) {
                const item = this.cartItems.find(item => item.id === itemId);
                return item ? item.quantity : 1;
            },

            getItemPrice(itemId) {
                const item = this.cartItems.find(item => item.id === itemId);
                return item && item.product ? item.product.price : 0;
            },

            // FIXED: Format price dengan benar
            formatPrice(price) {
                const numPrice = typeof price === 'number' ? price : parseFloat(price) || 0;
                return new Intl.NumberFormat('id-ID').format(numPrice);
            },

            updateQuantity(itemId, quantity) {
                if (quantity < 1) return;

                const item = this.cartItems.find(item => item.id === itemId);
                if (!item || !item.product) return;

                const maxStock = item.product.stock;
                if (quantity > maxStock) {
                    quantity = maxStock;
                    alert(`Maximum quantity is ${maxStock}`);
                }

                // Update local quantity immediately
                const oldQuantity = item.quantity;
                item.quantity = quantity;
                console.log(`Updated quantity for item ${itemId} from ${oldQuantity} to ${quantity}`);
                console.log('New subtotal after quantity update:', this.subtotal);

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
                        alert(data.message || 'Failed to update quantity');
                        // Revert local change
                        item.quantity = oldQuantity;
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error updating quantity:', error);
                    alert('An error occurred while updating the quantity');
                    // Revert local change
                    item.quantity = oldQuantity;
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
                        this.updateSelectAllStatus();

                        // Reload page after short delay to sync with server
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

                console.log('Proceeding to checkout with items:', this.selectedItems);
                console.log('Total amount:', this.total);

                const form = document.createElement('form');
                form.method = 'GET';
                form.action = '{{ route("checkout.index") }}';

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