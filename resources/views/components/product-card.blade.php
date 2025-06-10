<div class="card h-100 product-card border-0 shadow-sm" x-data="{
    showQuickAdd: false,
    isLoading: false,
    addToCart() {
        console.log('Add to cart clicked'); // Debug log
        this.isLoading = true;

        // Prepare form data
        const formData = new FormData();
        formData.append('product_id', '{{ $product->id }}');
        formData.append('quantity', '1');
        formData.append('_token', document.querySelector('meta[name=csrf-token]').getAttribute('content'));

        console.log('Sending request to:', '{{ route('cart.add') }}'); // Debug log

        // Send AJAX request
        fetch('{{ route('cart.add') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            console.log('Response headers:', response.headers); // Debug log

            // Check response status first
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType); // Debug log

            if (!contentType || !contentType.includes('application/json')) {
                // Get response text for debugging
                return response.text().then(text => {
                    console.log('Non-JSON response:', text); // Debug log
                    throw new Error('Server returned non-JSON response');
                });
            }

            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
            this.isLoading = false;

            if (data && data.success) {
                // Show success message
                this.showSuccessMessage(data.message || 'Product added to cart successfully!');

                // Update cart count in header if exists
                this.updateCartCount(data.cart_count || 0);
            } else {
                this.showErrorMessage(data.message || 'Failed to add product to cart');
            }
        })
        .catch(error => {
            console.error('Error details:', error); // Debug log
            this.isLoading = false;

            // Better error handling
            if (error.message && error.message.includes('non-JSON')) {
                // User might need to login or there's a redirect
                console.log('Checking if login is required...');
                this.checkLoginRequired();
            } else if (error.message && error.message.includes('HTTP error')) {
                this.showErrorMessage('Server error occurred. Please try again.');
            } else {
                this.showErrorMessage('Network error. Please check your connection and try again.');
            }
        });
    },

    checkLoginRequired() {
        // Check if user is logged in by making a simple request
        fetch('/cart/count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // User is logged in, show generic error
                this.showErrorMessage('An error occurred while adding to cart. Please try again.');
            }
        })
        .catch(() => {
            // Might need login
            this.showLoginRequired();
        });
    },

    showSuccessMessage(message) {
        // Create and show success toast
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.innerHTML = `
            <div class='d-flex'>
                <div class='toast-body'><i class='fas fa-check-circle me-2'></i>${message}</div>
                <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast'></button>
            </div>
        `;
        document.body.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        });
    },

    showErrorMessage(message) {
        // Create and show error toast
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.innerHTML = `
            <div class='d-flex'>
                <div class='toast-body'><i class='fas fa-exclamation-triangle me-2'></i>${message}</div>
                <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast'></button>
            </div>
        `;
        document.body.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        });
    },

    showLoginRequired() {
        // Show login modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'loginRequiredModal';
        modal.innerHTML = `
            <div class='modal-dialog modal-dialog-centered'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'><i class='fas fa-sign-in-alt me-2'></i>Login Required</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                    </div>
                    <div class='modal-body text-center'>
                        <i class='fas fa-shopping-cart fa-3x text-primary mb-3'></i>
                        <p class='mb-3'>Please login to add products to your cart and enjoy a personalized shopping experience.</p>
                    </div>
                    <div class='modal-footer justify-content-center'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                        <a href='{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}' class='btn btn-primary'>
                            <i class='fas fa-sign-in-alt me-1'></i>Login Now
                        </a>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Remove modal after it's hidden
        modal.addEventListener('hidden.bs.modal', () => {
            if (document.body.contains(modal)) {
                document.body.removeChild(modal);
            }
        });
    },

    updateCartCount(count) {
        console.log('Updating cart count to:', count); // Debug log

        // Update cart count in header
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;

            // Add animation effect
            element.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                element.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        });

        // Update cart badge visibility
        if (count > 0) {
            cartCountElements.forEach(element => {
                element.style.display = 'inline-block';
            });
        }
    }
}">
    <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
        <div class="position-relative">
            @if($product->image)
            <img src="{{ Storage::url($product->image) }}" class="card-img-top"
                style="height: 200px; object-fit: cover;" alt="{{ $product->name }}">
            @else
            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="fas fa-image fa-2x text-muted"></i>
            </div>
            @endif

            @if($product->original_price && $product->original_price > $product->price)
            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 m-2 rounded-pill">
                -{{ round((($product->original_price - $product->price) / $product->original_price) * 100) }}%
            </div>
            @endif
        </div>
    </a>

    <div class="card-body d-flex flex-column" @mouseenter="showQuickAdd = true" @mouseleave="showQuickAdd = false">

        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
            <h6 class="card-title text-dark mb-2">{{ Str::limit($product->name, 50) }}</h6>
        </a>

        <div class="text-muted small mb-2">
            @if($product->seller && $product->seller->store)
            {{ $product->seller->store->name }}
            @else
            <span class="text-muted">Store not available</span>
            @endif
        </div>

        <div class="mb-2">
            <div class="fw-bold text-primary">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </div>
            @if($product->original_price && $product->original_price > $product->price)
            <div class="text-muted text-decoration-line-through small">
                Rp {{ number_format($product->original_price, 0, ',', '.') }}
            </div>
            @endif
        </div>

        <!-- Stock status -->
        @if($product->stock > 0)
        <div class="small text-success mb-2">
            <i class="fas fa-check-circle me-1"></i>Stock: {{ $product->stock }}
        </div>
        @else
        <div class="small text-danger mb-2">
            <i class="fas fa-times-circle me-1"></i>Out of Stock
        </div>
        @endif

        <div x-show="showQuickAdd && {{ $product->stock > 0 ? 'true' : 'false' }}" x-transition class="mt-auto">
            <button type="button" @click="addToCart()" :disabled="isLoading" class="btn btn-primary w-100">
                <span x-show="!isLoading">
                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                </span>
                <span x-show="isLoading">
                    <i class="fas fa-spinner fa-spin me-1"></i> Adding...
                </span>
            </button>
        </div>

        <div x-show="showQuickAdd && {{ $product->stock <= 0 ? 'true' : 'false' }}" x-transition class="mt-auto">
            <button type="button" class="btn btn-secondary w-100" disabled>
                <i class="fas fa-times me-1"></i> Out of Stock
            </button>
        </div>
    </div>
</div>