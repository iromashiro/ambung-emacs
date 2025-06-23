@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" class="rounded-circle mb-3"
                        style="width: 100px; height: 100px; object-fit: cover;" alt="{{ $user->name }}">
                    @else
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-2x text-white"></i>
                    </div>
                    @endif
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small">{{ ucfirst($user->role) }}</p>
                </div>
            </div>

            <div class="list-group mt-3">
                <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action active">
                    <i class="fas fa-user me-2"></i>Profile
                </a>
                <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-bag me-2"></i>My Orders
                </a>
                <a href="{{ route('cart.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Profile Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Profile Information</h5>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3"><strong>Name:</strong></div>
                        <div class="col-sm-9">{{ $user->name }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><strong>Email:</strong></div>
                        <div class="col-sm-9">{{ $user->email }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><strong>Phone:</strong></div>
                        <div class="col-sm-9">{{ $user->phone ?? 'Not provided' }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><strong>Date of Birth:</strong></div>
                        <div class="col-sm-9">
                            {{ $user->date_of_birth ? $user->date_of_birth->format('d M Y') : 'Not provided' }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><strong>Gender:</strong></div>
                        <div class="col-sm-9">{{ $user->gender ? ucfirst($user->gender) : 'Not provided' }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><strong>Member Since:</strong></div>
                        <div class="col-sm-9">{{ $user->created_at->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Account Security -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Account Security</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6>Password</h6>
                            <p class="text-muted mb-0">Last updated: {{ $user->updated_at->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#changePasswordModal">
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
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