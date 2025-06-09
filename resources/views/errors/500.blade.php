@extends('layouts.app')

@section('title', 'Server Error')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5">
                <div class="mb-4">
                    <img src="{{ asset('images/500.svg') }}" alt="500 Server Error" class="img-fluid"
                        style="max-height: 300px;">
                </div>

                <h1 class="display-1 fw-bold text-warning">500</h1>
                <h2 class="mb-3">Internal Server Error</h2>
                <p class="text-muted mb-4">Something went wrong on our end. We're working to fix this issue. Please try
                    again later.</p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i> Go Home
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Try Again
                    </button>
                    <a href="{{ route('contact') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-envelope me-1"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection