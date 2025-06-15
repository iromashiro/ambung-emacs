@extends('layouts.seller')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Edit Profile</h1>
                    <p class="text-muted">Update your account information</p>
                </div>
                <div>
                    <a href="{{ route('seller.profile.show') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>
            </div>

            <form action="{{ route('seller.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="email"
                                                name="email" value="{{ old('email', $user->email) }}" required>
                                            @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            @if(!$user->email_verified_at)
                                            <small class="text-warning">Email not verified. <a
                                                    href="{{ route('verification.send') }}">Resend
                                                    verification</a></small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                            @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                                            <input type="date"
                                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                                id="date_of_birth" name="date_of_birth"
                                                value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                                            @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gender" class="form-label">Gender</label>
                                            <select class="form-select @error('gender') is-invalid @enderror"
                                                id="gender" name="gender">
                                                <option value="">Select Gender</option>
                                                <option value="male"
                                                    {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male
                                                </option>
                                                <option value="female"
                                                    {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>
                                                    Female</option>
                                                <option value="other"
                                                    {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>
                                                    Other</option>
                                            </select>
                                            @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio"
                                        name="bio" rows="3"
                                        placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                                    @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                        name="address" rows="3"
                                        placeholder="Your complete address...">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Profile Picture -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Profile Picture</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center mb-3">
                                        <div id="currentAvatar">
                                            @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                                class="img-fluid rounded-circle"
                                                style="width: 120px; height: 120px; object-fit: cover;"
                                                alt="Current Avatar">
                                            @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                                style="width: 120px; height: 120px;">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="mb-3">
                                            <label for="avatar" class="form-label">Upload New Picture</label>
                                            <input type="file"
                                                class="form-control @error('avatar') is-invalid @enderror" id="avatar"
                                                name="avatar" accept="image/*">
                                            @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Max 2MB, JPG/PNG format. Leave empty to keep
                                                current picture.</small>
                                        </div>

                                        <!-- Preview -->
                                        <div id="avatarPreview" style="display: none;">
                                            <label class="form-label">Preview:</label>
                                            <div>
                                                <img id="previewImage" class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;" alt="Preview">
                                            </div>
                                        </div>

                                        @if($user->avatar)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remove_avatar"
                                                name="remove_avatar" value="1">
                                            <label class="form-check-label" for="remove_avatar">
                                                Remove current picture
                                            </label>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Account Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Account Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Account Status</label>
                                    <div>
                                        <span class="badge bg-success">Active</span>
                                        @if($user->email_verified_at)
                                        <span class="badge bg-info">Verified</span>
                                        @else
                                        <span class="badge bg-warning">Unverified</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Member Since</label>
                                    <p class="text-muted mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Last Login</label>
                                    <p class="text-muted mb-0">
                                        {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Preferences -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Notification Preferences</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="email_notifications"
                                        name="email_notifications" value="1"
                                        {{ old('email_notifications', $user->email_notifications) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_notifications">
                                        Email Notifications
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="order_notifications"
                                        name="order_notifications" value="1"
                                        {{ old('order_notifications', $user->order_notifications) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_notifications">
                                        Order Notifications
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="marketing_notifications"
                                        name="marketing_notifications" value="1"
                                        {{ old('marketing_notifications', $user->marketing_notifications) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="marketing_notifications">
                                        Marketing Notifications
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                    <a href="{{ route('seller.profile.show') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <a href="{{ route('seller.profile.password') }}" class="btn btn-outline-warning">
                                        <i class="fas fa-key me-1"></i> Change Password
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Avatar preview
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('avatarPreview');
    const previewImage = document.getElementById('previewImage');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Remove avatar checkbox
document.getElementById('remove_avatar')?.addEventListener('change', function() {
    const currentAvatar = document.getElementById('currentAvatar');
    if (this.checked) {
        currentAvatar.style.opacity = '0.5';
    } else {
        currentAvatar.style.opacity = '1';
    }
});
</script>
@endsection