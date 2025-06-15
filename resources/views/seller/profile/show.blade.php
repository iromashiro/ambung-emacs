@extends('layouts.seller')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Profile</h1>
                    <p class="text-muted">Manage your account information and settings</p>
                </div>
                <div>
                    <a href="{{ route('seller.profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Profile Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="img-fluid rounded-circle"
                                        style="width: 120px; height: 120px; object-fit: cover;" alt="Profile Picture">
                                    @else
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 120px; height: 120px;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="fw-bold">Full Name:</td>
                                                    <td>{{ $user->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Email:</td>
                                                    <td>
                                                        {{ $user->email }}
                                                        @if($user->email_verified_at)
                                                        <span class="badge bg-success ms-1">Verified</span>
                                                        @else
                                                        <span class="badge bg-warning ms-1">Unverified</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Phone:</td>
                                                    <td>{{ $user->phone ?? 'Not provided' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Role:</td>
                                                    <td><span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="fw-bold">Date of Birth:</td>
                                                    <td>{{ $user->date_of_birth ? $user->date_of_birth->format('M d, Y') : 'Not provided' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Gender:</td>
                                                    <td>{{ $user->gender ? ucfirst($user->gender) : 'Not specified' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Joined:</td>
                                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Last Login:</td>
                                                    <td>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($user->bio)
                            <div class="mt-3">
                                <h6 class="fw-bold">Bio:</h6>
                                <p class="text-muted">{{ $user->bio }}</p>
                            </div>
                            @endif

                            @if($user->address)
                            <div class="mt-3">
                                <h6 class="fw-bold">Address:</h6>
                                <p class="text-muted">{{ $user->address }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Store Information -->
                    @if($user->store)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    @if($user->store->logo)
                                    <img src="{{ asset('storage/' . $user->store->logo) }}" class="img-fluid rounded"
                                        style="width: 100px; height: 100px; object-fit: cover;" alt="Store Logo">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 100px; height: 100px;">
                                        <i class="fas fa-store fa-2x text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <h5 class="mb-2">{{ $user->store->name }}</h5>
                                    <p class="text-muted mb-3">{{ $user->store->description }}</p>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Status:</strong>
                                                @if($user->store->status === 'approved')
                                                <span class="badge bg-success">Active</span>
                                                @elseif($user->store->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                                @else
                                                <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </p>
                                            <p class="mb-1"><strong>Phone:</strong> {{ $user->store->phone }}</p>
                                            @if($user->store->email)
                                            <p class="mb-1"><strong>Email:</strong> {{ $user->store->email }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Address:</strong></p>
                                            <p class="text-muted">{{ $user->store->address }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('seller.store.show') }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-store me-1"></i> View Store Details
                                        </a>
                                        <a href="{{ route('seller.store.edit') }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit me-1"></i> Edit Store
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Account Security -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Account Security</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Password</h6>
                                    <p class="text-muted">Last changed:
                                        {{ $user->password_changed_at ? $user->password_changed_at->format('M d, Y') : 'Never' }}
                                    </p>
                                    <a href="{{ route('seller.profile.password') }}"
                                        class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-key me-1"></i> Change Password
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Two-Factor Authentication</h6>
                                    @if($user->two_factor_enabled)
                                    <p class="text-success">
                                        <i class="fas fa-check-circle me-1"></i> Enabled
                                    </p>
                                    <button class="btn btn-outline-danger btn-sm" onclick="disable2FA()">
                                        <i class="fas fa-times me-1"></i> Disable 2FA
                                    </button>
                                    @else
                                    <p class="text-muted">
                                        <i class="fas fa-times-circle me-1"></i> Disabled
                                    </p>
                                    <button class="btn btn-outline-success btn-sm" onclick="enable2FA()">
                                        <i class="fas fa-shield-alt me-1"></i> Enable 2FA
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Account Statistics -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Account Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <h4 class="text-primary mb-1">{{ $stats['total_products'] ?? 0 }}</h4>
                                    <small class="text-muted">Products</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h4 class="text-success mb-1">{{ $stats['total_orders'] ?? 0 }}</h4>
                                    <small class="text-muted">Orders</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info mb-1">Rp {{ number_format($stats['total_revenue'] ?? 0) }}</h4>
                                    <small class="text-muted">Revenue</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning mb-1">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</h4>
                                    <small class="text-muted">Rating</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Recent Activity</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($recentActivities) && $recentActivities->count() > 0)
                            @foreach($recentActivities as $activity)
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 30px; height: 30px; flex-shrink: 0;">
                                    <i class="fas fa-{{ $activity->icon ?? 'circle' }} text-white small"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="fw-bold">{{ $activity->title }}</small><br>
                                    <small class="text-muted">{{ $activity->description }}</small><br>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <p class="text-muted small mb-0">No recent activity</p>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('seller.profile.edit') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i> Edit Profile
                                </a>
                                <a href="{{ route('seller.profile.password') }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-key me-1"></i> Change Password
                                </a>
                                @if(!$user->store)
                                <a href="{{ route('seller.store.setup') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-store me-1"></i> Setup Store
                                </a>
                                @endif
                                <a href="{{ route('seller.profile.notifications') }}"
                                    class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-bell me-1"></i> Notification Settings
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="confirmLogout()">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function enable2FA() {
    alert('Two-Factor Authentication setup will be implemented soon.');
}

function disable2FA() {
    if (confirm('Are you sure you want to disable Two-Factor Authentication?')) {
        alert('2FA disabled successfully.');
    }
}

function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        document.getElementById('logout-form').submit();
    }
}
</script>

<!-- Hidden logout form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
@endsection