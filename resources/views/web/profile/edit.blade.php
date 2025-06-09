{{-- resources/views/web/profile/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                    <a href="{{ route('profile.addresses') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt me-2"></i> My Addresses
                    </a>
                    @if(auth()->user()->hasRole('buyer'))
                    <a href="{{ route('buyer.orders.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i> My Orders
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('seller'))
                    <a href="{{ route('seller.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-store me-2"></i> Seller Dashboard
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Profile</h5>
                    <a href="{{ route('profile.show') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                @if($user->profile_photo)
                                <img src="{{ Storage::url($user->profile_photo) }}"
                                    class="img-fluid rounded-circle mb-3" alt="{{ $user->name }}"
                                    style="width: 150px; height: 150px; object-fit: cover;" id="profile-preview">
                                @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150&background=4e73df&color=ffffff"
                                    class="img-fluid rounded-circle mb-3" alt="{{ $user->name }}" id="profile-preview">
                                @endif

                                <div class="mb-3">
                                    <label for="profile_photo" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control @error('profile_photo') is-invalid @enderror"
                                        id="profile_photo" name="profile_photo" accept="image/*">
                                    @error('profile_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection