@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Ambung Emac" height="60" class="mb-3">
                        <h2 class="fw-bold">Create an Account</h2>
                        <p class="text-muted">Join Ambung Emac to start shopping</p>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" x-data="{ role: 'buyer' }">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label d-block">I want to:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="role" id="buyer" value="buyer"
                                    x-model="role" checked>
                                <label class="btn btn-outline-primary" for="buyer">
                                    <i class="fas fa-shopping-bag me-2"></i>
                                    Shop as Buyer
                                </label>

                                <input type="radio" class="btn-check" name="role" id="seller" value="seller"
                                    x-model="role">
                                <label class="btn btn-outline-primary" for="seller">
                                    <i class="fas fa-store me-2"></i>
                                    Sell as UMKM
                                </label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required autofocus>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                    name="phone" value="{{ old('phone') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                    id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                <div class="form-text">Minimum 8 characters</div>
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>
                        </div>

                        <!-- Additional fields for sellers -->
                        <div x-show="role === 'seller'" x-transition class="mt-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                As a seller, you'll need to set up your store after registration. Your store will need
                                approval before you can
                                start selling.
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                                    id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="{{ route('terms') }}" target="_blank">Terms of Service</a>
                                    and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2">Create Account</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}"
                                class="text-decoration-none">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection