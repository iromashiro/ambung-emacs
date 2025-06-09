@extends('layouts.app')

@section('title', 'Access Forbidden')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5">
                <div class="mb-4">
                    <img src="{{ asset('images/403.svg') }}" alt="403 Forbidden" class="img-fluid"
                        style="max-height: 300px;">
                </div>

                <h1 class="display-1 fw-bold text-danger">403</h1>
                <h2 class="mb-3">Access Forbidden</h2>
                <p class="text-muted mb-4">You don't have permission to access this resource. Please contact the
                    administrator if you believe this is an error.</p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i> Go Home
                    </a>
                    <button class="btn btn-outline-secondary" onclick="history.back()">
                        <i class="fas fa-arrow-left me-1"></i> Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection