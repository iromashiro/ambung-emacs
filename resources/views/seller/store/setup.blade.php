@extends('layouts.seller')

@section('title', 'Store Setup')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Store Setup</h1>
                    <p class="text-muted">Create your store to start selling</p>
                </div>
            </div>

            <!-- Setup Steps -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <div class="step-item active">
                                        <div class="step-number bg-primary text-white">1</div>
                                        <h6 class="mt-2">Store Information</h6>
                                        <small class="text-muted">Basic store details</small>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="step-item">
                                        <div class="step-number bg-light text-muted">2</div>
                                        <h6 class="mt-2">Admin Review</h6>
                                        <small class="text-muted">Wait for approval</small>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="step-item">
                                        <div class="step-number bg-light text-muted">3</div>
                                        <h6 class="mt-2">Start Selling</h6>
                                        <small class="text-muted">Add products & manage orders</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Store Setup Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Store Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('seller.store.setup.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Store Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name') }}" required>
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
                                                id="phone" name="phone" value="{{ old('phone') }}" required>
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
                                        required>{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Store Address <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                        name="address" rows="3" required>{{ old('address') }}</textarea>
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
                                                name="email" value="{{ old('email') }}">
                                            @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Store Logo</label>
                                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                                id="logo" name="logo" accept="image/*">
                                            @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Max 2MB, JPG/PNG format</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="banner" class="form-label">Store Banner</label>
                                    <input type="file" class="form-control @error('banner') is-invalid @enderror"
                                        id="banner" name="banner" accept="image/*">
                                    @error('banner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max 5MB, JPG/PNG format</small>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('seller.dashboard') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Create Store
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Setup Guidelines</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-1"></i> Important Notes</h6>
                                <ul class="mb-0 small">
                                    <li>Store name must be unique</li>
                                    <li>Provide accurate contact information</li>
                                    <li>Store will be reviewed by admin</li>
                                    <li>Approval usually takes 1-3 business days</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-1"></i> Requirements</h6>
                                <ul class="mb-0 small">
                                    <li>Valid business information</li>
                                    <li>Professional store description</li>
                                    <li>Complete contact details</li>
                                    <li>High-quality logo (recommended)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .step-item {
        position: relative;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin: 0 auto;
    }

    .step-item.active .step-number {
        background-color: var(--bs-primary) !important;
        color: white !important;
    }
</style>
@endsection