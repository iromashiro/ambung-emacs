@extends('layouts.seller')

@section('title', 'Manage Products')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manage Products</h1>
        <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Product
        </a>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('seller.products.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search products..."
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock
                        </option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of
                            Stock</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Products Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(count($products) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Sales</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $product->image_url ?? asset('images/product-default.jpg') }}"
                                        alt="{{ $product->name }}" class="me-2 rounded"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td>
                                @if($product->discount_percentage > 0)
                                <span class="text-danger fw-bold">Rp
                                    {{ number_format($product->final_price, 0, ',', '.') }}</span>
                                <br>
                                <small class="text-muted text-decoration-line-through">Rp
                                    {{ number_format($product->price, 0, ',', '.') }}</small>
                                @else
                                <span class="fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->stock <= 5 && $product->stock > 0)
                                    <span class="text-warning fw-bold">{{ $product->stock }}</span>
                                    @elseif($product->stock == 0)
                                    <span class="text-danger fw-bold">Out of stock</span>
                                    @else
                                    <span>{{ $product->stock }}</span>
                                    @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $product->sold_count }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('seller.products.edit', $product) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info"
                                        target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $product->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete
                                                    <strong>{{ $product->name }}</strong>?
                                                </p>
                                                <p class="text-danger mb-0">This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('seller.products.destroy', $product) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h3>No Products Found</h3>
                <p class="text-muted mb-4">You haven't added any products yet or no products match your filter criteria.
                </p>
                <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Product
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection