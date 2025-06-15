@extends('layouts.seller')

@section('title', 'Product Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Product Details</h1>
                    <p class="text-muted">{{ $product->name }}</p>
                </div>
                <div>
                    <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-1"></i> Edit Product
                    </a>
                    <a href="{{ route('seller.products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Product Images -->
                    <div class="card mb-4">
                        <div class="card-body">
                            @if($product->images && $product->images->count() > 0)
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                                        class="img-fluid rounded" id="mainImage" alt="Product Image">
                                </div>
                                <div class="col-md-6">
                                    <div class="row g-2">
                                        @foreach($product->images as $image)
                                        <div class="col-4">
                                            <img src="{{ asset('storage/' . $image->image_path) }}"
                                                class="img-fluid rounded thumbnail-image"
                                                style="height: 80px; object-fit: cover; cursor: pointer;"
                                                onclick="changeMainImage(this.src)" alt="Product Image">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Images</h5>
                                <p class="text-muted">This product doesn't have any images yet.</p>
                                <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Images
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Product Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Name:</td>
                                            <td>{{ $product->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">SKU:</td>
                                            <td>{{ $product->sku ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Category:</td>
                                            <td>{{ $product->category->name ?? 'No Category' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Price:</td>
                                            <td>
                                                <strong class="text-primary">Rp
                                                    {{ number_format($product->price) }}</strong>
                                                @if($product->compare_price)
                                                <small class="text-muted text-decoration-line-through ms-2">
                                                    Rp {{ number_format($product->compare_price) }}
                                                </small>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Stock:</td>
                                            <td>
                                                @if($product->stock <= 0) <span class="badge bg-danger">Out of
                                                    Stock</span>
                                                    @elseif($product->stock <= 10) <span class="badge bg-warning">
                                                        {{ $product->stock }} left</span>
                                                        @else
                                                        <span class="badge bg-success">{{ $product->stock }}
                                                            available</span>
                                                        @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Weight:</td>
                                            <td>{{ $product->weight }} grams</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Dimensions:</td>
                                            <td>{{ $product->dimensions ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                @if($product->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                                @elseif($product->status === 'inactive')
                                                <span class="badge bg-secondary">Inactive</span>
                                                @else
                                                <span class="badge bg-warning">Draft</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Featured:</td>
                                            <td>
                                                @if($product->is_featured)
                                                <span class="badge bg-info">Yes</span>
                                                @else
                                                <span class="badge bg-light text-dark">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Created:</td>
                                            <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-3">
                                <h6 class="fw-bold">Description:</h6>
                                <p class="text-muted">{{ $product->description }}</p>
                            </div>

                            @if($product->short_description)
                            <div class="mt-3">
                                <h6 class="fw-bold">Short Description:</h6>
                                <p class="text-muted">{{ $product->short_description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i> Edit Product
                                </a>

                                <form action="{{ route('seller.products.status.update', $product) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status"
                                        value="{{ $product->status === 'active' ? 'inactive' : 'active' }}">
                                    <button type="submit"
                                        class="btn btn-outline-{{ $product->status === 'active' ? 'warning' : 'success' }} w-100">
                                        @if($product->status === 'active')
                                        <i class="fas fa-eye-slash me-1"></i> Deactivate
                                        @else
                                        <i class="fas fa-eye me-1"></i> Activate
                                        @endif
                                    </button>
                                </form>

                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    <i class="fas fa-trash me-1"></i> Delete Product
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Statistics -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary mb-1">{{ $product->views ?? 0 }}</h4>
                                    <small class="text-muted">Views</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-1">{{ $product->orders_count ?? 0 }}</h4>
                                    <small class="text-muted">Orders</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-info mb-1">{{ $product->rating ?? 0 }}</h5>
                                    <small class="text-muted">Rating</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-warning mb-1">{{ $product->reviews_count ?? 0 }}</h5>
                                    <small class="text-muted">Reviews</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    @if($product->meta_title || $product->meta_description)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">SEO Information</h6>
                        </div>
                        <div class="card-body">
                            @if($product->meta_title)
                            <div class="mb-2">
                                <strong>Meta Title:</strong>
                                <p class="text-muted small mb-0">{{ $product->meta_title }}</p>
                            </div>
                            @endif
                            @if($product->meta_description)
                            <div class="mb-2">
                                <strong>Meta Description:</strong>
                                <p class="text-muted small mb-0">{{ $product->meta_description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Recent Orders -->
                    @if(isset($recentOrders) && $recentOrders->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Recent Orders</h6>
                        </div>
                        <div class="card-body">
                            @foreach($recentOrders as $order)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <small class="fw-bold">#{{ $order->order_number }}</small><br>
                                    <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                </div>
                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            @endforeach
                            <a href="{{ route('seller.orders.index') }}"
                                class="btn btn-sm btn-outline-primary w-100 mt-2">
                                View All Orders
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Warning:</strong> Deleting this product will also remove all associated data including
                    images and order history.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
}
</script>
@endsection