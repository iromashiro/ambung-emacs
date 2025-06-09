{{-- resources/views/web/profile/show.blade.php --}}
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
                    <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action active">
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
                    <h5 class="mb-0">My Profile</h5>
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            @if($user->profile_photo)
                            <img src="{{ Storage::url($user->profile_photo) }}" class="img-fluid rounded-circle mb-3"
                                alt="{{ $user->name }}" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150&background=4e73df&color=ffffff"
                                class="img-fluid rounded-circle mb-3" alt="{{ $user->name }}">
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h4>{{ $user->name }}</h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-envelope me-2"></i> {{ $user->email }}
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-phone me-2"></i> {{ $user->phone ?? 'Not provided' }}
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-user-tag me-2"></i>
                                @if($user->hasRole('admin'))
                                <span class="badge bg-danger">Administrator</span>
                                @elseif($user->hasRole('seller'))
                                <span class="badge bg-success">Seller</span>
                                @else
                                <span class="badge bg-primary">Buyer</span>
                                @endif
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-calendar me-2"></i> Member since {{ $user->created_at->format('F Y') }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Account Information</h5>
                            <p class="text-muted small mb-3">Your personal information</p>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Full Name</label>
                                <p class="mb-0">{{ $user->name }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <p class="mb-0">{{ $user->email }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone Number</label>
                                <p class="mb-0">{{ $user->phone ?? 'Not provided' }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Account Security</h5>
                            <p class="text-muted small mb-3">Manage your password and security settings</p>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <p class="mb-2">••••••••</p>
                                <small class="text-muted">Last changed:
                                    {{ $user->updated_at->format('M d, Y') }}</small>
                            </div>

                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#changePasswordModal">
                                    <i class="fas fa-key me-1"></i> Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                            id="current_password" name="current_password" required>
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" required>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection