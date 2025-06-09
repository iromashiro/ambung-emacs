@extends('layouts.seller')

@section('title', 'Add New Product')

@section('content')
<div class="container" x-data="productForm()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Add New Product</h1>
        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Products
        </a>
    </div>

    <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                                    name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku"
                                    name="sku" value="{{ old('sku') }}">
                                @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to auto-generate</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                name="description" rows="5" required>{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing and Inventory -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Pricing & Inventory</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Regular Price (Rp) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                        id="price" name="price" value="{{ old('price') }}" min="0" step="1000" required
                                        x-model="price">
                                </div>
                                @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="discount_percentage" class="form-label">Discount (%)</label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control @error('discount_percentage') is-invalid @enderror"
                                        id="discount_percentage" name="discount_percentage"
                                        value="{{ old('discount_percentage', 0) }}" min="0" max="100"
                                        x-model="discount">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('discount_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Final Price</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control bg-light" readonly x-bind:value="finalPrice">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock Quantity <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                    id="stock" name="stock" value="{{ old('stock', 1) }}" min="0" required>
                                @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="weight" class="form-label">Weight (grams) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                        id="weight" name="weight" value="{{ old('weight', 100) }}" min="1" required>
                                    <span class="input-group-text">g</span>
                                </div>
                                @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label for="length" class="form-label">Length (cm)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('length') is-invalid @enderror"
                                        id="length" name="length" value="{{ old('length') }}" min="0">
                                    <span class="input-group-text">cm</span>
                                </div>
                                @error('length')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="width" class="form-label">Width (cm)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('width') is-invalid @enderror"
                                        id="width" name="width" value="{{ old('width') }}" min="0">
                                    <span class="input-group-text">cm</span>
                                </div>
                                @error('width')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="height" class="form-label">Height (cm)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('height') is-invalid @enderror"
                                        id="height" name="height" value="{{ old('height') }}" min="0">
                                    <span class="input-group-text">cm</span>
                                </div>
                                @error('height')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Product Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="main_image" class="form-label">Main Image <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('main_image') is-invalid @enderror"
                                id="main_image" name="main_image" accept="image/*" required>
                            @error('main_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Recommended size: 800x800 pixels, max 2MB</div>
                        </div>

                        <div class="mb-3">
                            <label for="additional_images" class="form-label">Additional Images</label>
                            <input type="file" class="form-control @error('additional_images') is-invalid @enderror"
                                id="additional_images" name="additional_images[]" accept="image/*" multiple>
                            @error('additional_images')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">You can select multiple images. Max 5 additional images.</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Additional Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="specifications" class="form-label">Specifications</label>
                            <div class="specifications-container">
                                <template x-for="(spec, index) in specifications" :key="index">
                                    <div class="row mb-2">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" x-model="spec.key"
                                                name="specification_keys[]" placeholder="e.g. Material">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" x-model="spec.value"
                                                name="specification_values[]" placeholder="e.g. Cotton">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100"
                                                @click="removeSpecification(index)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button type="button" class="btn btn-outline-primary mt-2" @click="addSpecification">
                                <i class="fas fa-plus me-1"></i> Add Specification
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Publish Settings -->
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Publish Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_active" id="statusActive"
                                    value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusActive">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_active" id="statusInactive"
                                    value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusInactive">Inactive</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                    value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Featured Product</label>
                            </div>
                            <div class="form-text">Featured products appear on the homepage</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Product
                            </button>
                            <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function productForm() {
        return {
            price: {{ old('price', 0) }},
            discount: {{ old('discount_percentage', 0) }},
            specifications: [
                { key: '', value: '' }
            ],

            get finalPrice() {
                const discountAmount = this.price * (this.discount / 100);
                const final = this.price - discountAmount;
                return final.toLocaleString('id-ID');
            },

            addSpecification() {
                this.specifications.push({ key: '', value: '' });
            },

            removeSpecification(index) {
                this.specifications.splice(index, 1);
                if (this.specifications.length === 0) {
                    this.addSpecification();
                }
            }
        };
    }
</script>
@endpush
@endsection