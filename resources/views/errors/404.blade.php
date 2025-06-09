@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5">
                <div class="mb-4">
                    <img src="{{ asset('images/404.svg') }}" alt="404 Not Found" class="img-fluid"
                        style="max-height: 300px;">
                </div>

                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-3">Oops! Page Not Found</h2>
                <p class="text-muted mb-4">The page you are looking for might have been removed, had its name changed,
                    or is temporarily unavailable.</p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i> Go Home
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-bag me-1"></i> Browse Products
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