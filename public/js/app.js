// Global JavaScript utilities for Ambung Emac

// CSRF Token setup for AJAX requests
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Global utility functions
window.utils = {
    // Format currency
    formatCurrency: function(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },

    // Format date
    formatDate: function(date, format = 'DD MMM YYYY') {
        return dayjs(date).format(format);
    },

    // Show toast notification
    showToast: function(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    },

    // Create toast container if it doesn't exist
    createToastContainer: function() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    },

    // Confirm dialog
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    // Debounce function
    debounce: function(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    },

    // Throttle function
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // Copy to clipboard
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(function() {
            utils.showToast('Copied to clipboard!', 'success');
        }, function(err) {
            console.error('Could not copy text: ', err);
            utils.showToast('Failed to copy to clipboard', 'danger');
        });
    },

    // Validate email
    isValidEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Validate phone number (Indonesian format)
    isValidPhone: function(phone) {
        const re = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
        return re.test(phone.replace(/\s/g, ''));
    },

    // Format phone number
    formatPhone: function(phone) {
        return phone.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
    },

    // Lazy load images
    lazyLoadImages: function() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    },

    // Auto-resize textarea
    autoResizeTextarea: function(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize lazy loading
    utils.lazyLoadImages();

    // Initialize auto-resize textareas
    document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
        textarea.addEventListener('input', function() {
            utils.autoResizeTextarea(this);
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Handle form submissions with loading states
    document.querySelectorAll('form[data-loading]').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
            }
        });
    });

    // Handle AJAX forms
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const url = this.action;
            const method = this.method || 'POST';

            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    utils.showToast(data.message || 'Operation completed successfully', 'success');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    utils.showToast(data.message || 'An error occurred', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                utils.showToast('An error occurred. Please try again.', 'danger');
            });
        });
    });
});

// Shopping cart functionality
window.cart = {
    add: function(productId, quantity = 1) {
        return fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                utils.showToast('Product added to cart!', 'success');
                this.updateCartCount(data.cart_count);
            } else {
                utils.showToast(data.message || 'Failed to add product to cart', 'danger');
            }
            return data;
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            utils.showToast('An error occurred. Please try again.', 'danger');
        });
    },

    remove: function(cartItemId) {
        return fetch('/cart/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                cart_item_id: cartItemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                utils.showToast('Product removed from cart', 'success');
                this.updateCartCount(data.cart_count);
            } else {
                utils.showToast(data.message || 'Failed to remove product from cart', 'danger');
            }
            return data;
        });
    },

    update: function(cartItemId, quantity) {
        return fetch('/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                cart_item_id: cartItemId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                utils.showToast('Cart updated', 'success');
                this.updateCartCount(data.cart_count);
            } else {
                utils.showToast(data.message || 'Failed to update cart', 'danger');
            }
            return data;
        });
    },

    updateCartCount: function(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'inline' : 'none';
        });
    }
};

// Search functionality
window.search = {
    init: function() {
        const searchInput = document.querySelector('#search-input');
        const searchResults = document.querySelector('#search-results');

        if (searchInput && searchResults) {
            const debouncedSearch = utils.debounce(this.performSearch.bind(this), 300);

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                if (query.length >= 2) {
                    debouncedSearch(query, searchResults);
                } else {
                    searchResults.innerHTML = '';
                    searchResults.style.display = 'none';
                }
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }
    },

    performSearch: function(query, resultsContainer) {
        fetch(`/api/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                this.displayResults(data.results, resultsContainer);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    },

    displayResults: function(results, container) {
        if (results.length === 0) {
            container.innerHTML = '<div class="p-3 text-muted">No results found</div>';
        } else {
            const html = results.map(result => `
                <a href="${result.url}" class="d-flex align-items-center p-3 text-decoration-none border-bottom">
                    <img src="${result.image}" alt="${result.name}" class="me-3" style="width: 40px; height: 40px; object-fit: cover;">
                    <div>
                        <div class="fw-medium">${result.name}</div>
                        <div class="text-muted small">${result.category}</div>
                    </div>
                    <div class="ms-auto text-primary">${result.price}</div>
                </a>
            `).join('');

            container.innerHTML = html;
        }

        container.style.display = 'block';
    }
};

// Initialize search on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    search.init();
});

// Export for use in other scripts
window.AmbungEmac = {
    utils: utils,
    cart: cart,
    search: search
};
