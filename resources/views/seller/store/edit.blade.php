@extends('layouts.seller')

@section('title', 'Edit Store')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Edit Store</h1>
                    <p class="text-muted">Update your store information</p>
                </div>
                <div>
                    <a href="{{ route('seller.store.show') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Store
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Store Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('seller.store.update') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Store Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $store->name) }}" required>
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                id="phone" name="phone" value="{{ old('phone', $store->phone) }}"
                                                required>
                                            @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Store Description <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="4"
                                        required>{{ old('description', $store->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Store Address <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                        name="address" rows="3"
                                        required>{{ old('address', $store->address) }}</textarea>
                                    @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Store Email</label>
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="email"
                                                name="email" value="{{ old('email', $store->email) }}">
                                            @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Images -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Current Logo</label>
                                        <div class="border rounded p-3 text-center">
                                            @if($store->logo)
                                            <img src="{{ asset('storage/' . $store->logo) }}" class="img-fluid rounded"
                                                style="max-height: 100px;" alt="Current Logo">
                                            @else
                                            <div class="text-muted">
                                                <i class="fas fa-image fa-2x mb-2"></i>
                                                <p class="mb-0">No logo uploaded</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Current Banner</label>
                                        <div class="border rounded p-3 text-center">
                                            @if($store->banner)
                                            <img src="{{ asset('storage/' . $store->banner) }}"
                                                class="img-fluid rounded" style="max-height: 100px;"
                                                alt="Current Banner">
                                            @else
                                            <div class="text-muted">
                                                <i class="fas fa-image fa-2x mb-2"></i>
                                                <p class="mb-0">No banner uploaded</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload New Images -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Update Logo</label>
                                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                                id="logo" name="logo" accept="image/*">
                                            @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Max 2MB, JPG/PNG format. Leave empty to keep
                                                current logo.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="banner" class="form-label">Update Banner</label>
                                            <input type="file"
                                                class="form-control @error('banner') is-invalid @enderror" id="banner"
                                                name="banner" accept="image/*">
                                            @error('banner')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Max 5MB, JPG/PNG format. Leave empty to keep
                                                current banner.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('seller.store.show') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Store
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Update Guidelines</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-1"></i> Important Notes</h6>
                                <ul class="mb-0 small">
                                    <li>Changes may require admin review</li>
                                    <li>Store name must remain unique</li>
                                    <li>Keep contact information up to date</li>
                                    <li>High-quality images improve customer trust</li>
                                </ul>
                            </div>

                            @if($store->status === 'rejected')
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-1"></i> Store Rejected</h6>
                                <p class="mb-2 small">Your store was rejected. Please update the information and
                                    resubmit.</p>
                                @if($store->rejection_reason)
                                <p class="mb-0 small"><strong>Reason:</strong> {{ $store->rejection_reason }}</p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Store Preview -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Store Preview</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                @if($store->logo)
                                <img src="{{ asset('storage/' . $store->logo) }}" class="img-fluid rounded-circle mb-2"
                                    style="width: 60px; height: 60px; object-fit: cover;" alt="Store Logo">
                                @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-2"
                                    style="width: 60px; height: 60px; margin: 0 auto;">
                                    <i class="fas fa-store text-muted"></i>
                                </div>
                                @endif
                                <h6 class="mb-1">{{ $store->name }}</h6>
                                <small class="text-muted">{{ Str::limit($store->description, 50) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection