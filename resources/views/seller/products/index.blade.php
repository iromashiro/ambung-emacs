@extends('layouts.seller')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Products</h1>
                    <p class="text-muted">Manage your product inventory</p>
                </div>
                <div>
                    <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Product
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('seller.products.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Search products..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    @if(isset($categories))
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="sort">
                                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest
                                    </option>
                                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest
                                    </option>
                                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name
                                        A-Z</option>
                                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>
                                        Name Z-A</option>
                                    <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>
                                        Price Low</option>
                                    <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>
                                        Price High</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary mb-1">{{ $stats['total'] ?? 0 }}</h3>
                            <small class="text-muted">Total Products</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success mb-1">{{ $stats['active'] ?? 0 }}</h3>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-1">{{ $stats['low_stock'] ?? 0 }}</h3>
                            <small class="text-muted">Low Stock</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger mb-1">{{ $stats['out_of_stock'] ?? 0 }}</h3>
                            <small class="text-muted">Out of Stock</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-body">
                    @if(isset($products) && $products->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->images && $product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                                                class="rounded me-3"
                                                style="width: 50px; height: 50px; object-fit: cover;"
                                                alt="Product Image">
                                            @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $product->name }}</h6>
                                                <small class="text-muted">{{ $product->sku ?? 'No SKU' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-light text-dark">{{ $product->category->name ?? 'No Category' }}</span>
                                    </td>
                                    <td>
                                        <strong>Rp {{ number_format($product->price) }}</strong>
                                    </td>
                                    <td>
                                        @if($product->stock <= 0) <span class="badge bg-danger">Out of Stock</span>
                                            @elseif($product->stock <= 10) <span class="badge bg-warning">
                                                {{ $product->stock }} left</span>
                                                @else
                                                <span class="badge bg-success">{{ $product->stock }}</span>
                                                @endif
                                    </td>
                                    <td>
                                        @if($product->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                        @elseif($product->status === 'inactive')
                                        <span class="badge bg-secondary">Inactive</span>
                                        @else
                                        <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $product->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('seller.products.show', $product) }}">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('seller.products.edit', $product) }}">
                                                        <i class="fas fa-edit me-1"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form
                                                        action="{{ route('seller.products.status.update', $product) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status"
                                                            value="{{ $product->status === 'active' ? 'inactive' : 'active' }}">
                                                        <button type="submit" class="dropdown-item">
                                                            @if($product->status === 'active')
                                                            <i class="fas fa-eye-slash me-1"></i> Deactivate
                                                            @else
                                                            <i class="fas fa-eye me-1"></i> Activate
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('seller.products.destroy', $product) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($products, 'links'))
                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-box fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Products Found</h5>
                        <p class="text-muted">You haven't added any products yet.</p>
                        <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add Your First Product
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection