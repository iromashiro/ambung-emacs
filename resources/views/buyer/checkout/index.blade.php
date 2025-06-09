@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container" x-data="checkoutPage()">
    <h1 class="h3 mb-4">Checkout</h1>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Shipping Address -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0">Shipping Address</h5>
                        @if(count($addresses) > 0)
                            <button class="btn btn-sm btn-outline-primary ms-auto" 
                                    data-bs-toggle="modal" data-bs-target="#addressModal">
                                Change Address
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(count($addresses) > 0)
                        <div class="mb-3">
                            <div class="d-flex align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $selectedAddress->recipient_name }}</h6>
                                    <p class="mb-1">{{ $selectedAddress->phone }}</p>
                                    <p class="mb-1">
                                        {{ $selectedAddress->address }}, 
                                        {{ $selectedAddress->city }}, 
                                        {{ $selectedAddress->province }}, 
                                        {{ $selectedAddress->postal_code }}
                                    </p>
                                </div>
                                @if($selectedAddress->is_default)
                                    <span class="badge bg-primary ms-2">Default</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <form id="addressForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="recipient_name" class="form-label">Recipient Name</label>
                                    <input type="text" class="form-control" id="recipient_name" name="recipient_name" 
                                           x-model="shippingAddress.recipient_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           x-model="shippingAddress.phone" required>
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              x-model="shippingAddress.address" required></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           x-model="shippingAddress.city" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="province" class="form-label">Province</label>
                                    <input type="text" class="form-control" id="province" name="province" 
                                           x-model="shippingAddress.province" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           x-model="shippingAddress.postal_code" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="save_address" 
                                               x-model="shippingAddress.save_address">
                                        <label class="form-check-label" for="save_address">
                                            Save this address for future orders
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    @foreach($cartItemsByStore as $storeId => $items)
                        @php
                            $store = \App\Models\Store::find($storeId);
                        @endphp
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $store->logo_url ?? asset('images/stores/default.jpg') }}" 
                                     class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;" 
                                     alt="{{ $store->name }}">
                                <h6 class="mb-0">{{ $store->name }}</h6>
                            </div>
                            
                            @foreach($items as $item)
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <img src="{{ $item->product->image_url ?? asset('images/products/default.jpg') }}" 
                                         class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;" 
                                         alt="{{ $item->product->name }}">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                        <p class="text-muted small mb-1">Quantity: {{ $item->quantity }}</p>
                                        <div>
                                            @if($item->product->discount_percentage > 0)
                                                <span class="text-danger">Rp {{ number_format($item->product->discounted_price, 0, ',', '.') }}</span>
                                                <span class="text-muted text-decoration-line-through small ms-1">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-danger">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-danger fw-bold">
                                            Rp {{ number_format($item->product->discount_percentage > 0 ? 
                                                $item->product->discounted_price * $item->quantity : 
                                                $item->product->price * $item->quantity, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted">Shipping Fee:</span>
                                </div>
                                <div class="fw-bold">
                                    Rp {{ number_format($shippingFeePerStore, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked disabled>
                        <label class="form-check-label" for="cod">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded me-3">
                                    <i class="fas fa-money-bill-wave text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Cash on Delivery (COD)</h6>
                                    <p class="text-muted small mb-0">Pay when you receive your order</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Order Notes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Notes (Optional)</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control" rows="3" placeholder="Add notes for your order..." 
                              x-model="orderNotes"></textarea>
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
                        <span>Subtotal ({{ count($cartItems) }} items)</span>
                        <span class="fw-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping Fee</span>
                        <span class="fw-bold">Rp {{ number_format($totalShippingFee, 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-danger">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    
                    <button class="btn btn-primary w-100 py-2" 
                            @click="placeOrder"
                            :disabled="isProcessing">
                        <span x-show="isProcessing">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Processing...
                        </span>
                        <span x-show="!isProcessing">Place Order</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Address Selection Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Select Shipping Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        @foreach($addresses as $address)
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <input class="form-check-input me-3" type="radio" name="selected_address" 
                                           value="{{ $address->id }}" {{ $address->id === $selectedAddress->id ? 'checked' : '' }}>
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-1">{{ $address->recipient_name }}</h6>
                                            @if($address->is_default)
                                                <span class="badge bg-primary ms-2">Default</span>
                                            @endif
                                        </div>
                                        <p class="mb-1">{{ $address->phone }}</p>
                                        <p class="mb-1">
                                            {{ $address->address }}, 
                                            {{ $address->city }}, 
                                            {{ $address->province }}, 
                                            {{ $address->postal_code }}
                                        </p>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    
                    <div class="d-grid mt-3">
                        <button class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#newAddressForm">
                            <i class="fas fa-plus me-1"></i> Add New Address
                        </button>
                    </div>
                    
                    <div class="collapse mt-3" id="newAddressForm">
                        <div class="card card-body">
                            <form id="modalAddressForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="modal_recipient_name" class="form-label">Recipient Name</label>
                                        <input type="text" class="form-control" id="modal_recipient_name" name="recipient_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modal_phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="modal_phone" name="phone" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="modal_address" class="form-label">Address</label>
                                        <textarea class="form-control" id="modal_address" name="address" rows="3" required></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modal_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="modal_city" name="city" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modal_province" class="form-label">Province</label>
                                        <input type="text" class="form-control" id="modal_province" name="province" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modal_postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="modal_postal_code" name="postal_code" required>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="modal_is_default" name="is_default">
                                            <label class="form-check-label" for="modal_is_default">
                                                Set as default address
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100">Save Address</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="updateSelectedAddress">Use This Address</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function checkoutPage() {
        return {
            shippingAddress: {
                recipient_name: '{{ auth()->user()->name ?? '' }}',
                phone: '{{ auth()->user()->phone ?? '' }}',
                address: '',
                city: '',
                province: '',
                postal_code: '',
                save_address: true
            },
            orderNotes: '',
            isProcessing: false,
            
            placeOrder() {
                // Validate address if no saved address is selected
                @if(count($addresses) === 0)
                    if (!this.validateAddress()) {
                        return;
                    }
                @endif
                
                this.isProcessing = true;
                
                // Prepare order data
                const orderData = {
                    cart_items: @json($cartItemIds),
                    payment_method: 'cod',
                    order_notes: this.orderNotes,
                    _token: '{{ csrf_token() }}'
                };
                
                // Add address data if no saved address
                @if(count($addresses) === 0)
                    orderData.shipping_address = this.shippingAddress;
                @else
                    orderData.address_id = {{ $selectedAddress->id }};
                @endif
                
                // Submit order
                fetch('{{ route('orders.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || 'Failed to place order');
                        this.isProcessing = false;
                    }
                })
                .catch(error => {
                    console.error('Error placing order:', error);
                    alert('An error occurred while placing your order');
                    this.isProcessing = false;
                });
            },
            
            validateAddress() {
                const requiredFields = ['recipient_name', 'phone', 'address', 'city', 'province', 'postal_code'];
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!this.shippingAddress[field]) {
                        isValid = false;
                        document.getElementById(field).classList.add('is-invalid');
                    } else {
                        document.getElementById(field).classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    alert('Please fill in all required address fields');
                }
                
                return isValid;
            },
            
            updateSelectedAddress() {
                const selectedAddressId = document.querySelector('input[name="selected_address"]:checked').value;
                
                fetch('{{ route('checkout.update-address') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ address_id: selectedAddressId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to update address');
                    }
                })
                .catch(error => {
                    console.error('Error updating address:', error);
                    alert('An error occurred while updating the address');
                });
            }
        };
    }
    
    // Handle new address form submission
    document.addEventListener('DOMContentLoaded', function() {
        const modalAddressForm = document.getElementById('modalAddressForm');
        if (modalAddressForm) {
            modalAddressForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(modalAddressForm);
                
                fetch('{{ route('addresses.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to add address');
                    }
                })
                .catch(error => {
                    console.error('Error adding address:', error);
                    alert('An error occurred while adding the address');
                });
            });
        }
    });
</script>
@endsection