@extends('layouts.seller')

@section('title', 'Store Status')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Store Status</h1>
                    <p class="text-muted">Check your store approval status</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Status Card -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            @if($store->status === 'pending')
                            <div class="mb-4">
                                <i class="fas fa-clock text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="text-warning">Under Review</h3>
                            <p class="text-muted mb-4">Your store is currently being reviewed by our admin team.</p>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-1"></i> What's Next?</h6>
                                <ul class="mb-0 text-start">
                                    <li>Our team will review your store information</li>
                                    <li>We may contact you if additional information is needed</li>
                                    <li>You'll receive an email notification once approved</li>
                                    <li>Approval usually takes 1-3 business days</li>
                                </ul>
                            </div>

                            @elseif($store->status === 'approved')
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="text-success">Store Approved!</h3>
                            <p class="text-muted mb-4">Congratulations! Your store has been approved and is now active.
                            </p>

                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Your First Product
                                </a>
                                <a href="{{ route('seller.store.show') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-store me-1"></i> View Store
                                </a>
                            </div>

                            @elseif($store->status === 'rejected')
                            <div class="mb-4">
                                <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="text-danger">Store Rejected</h3>
                            <p class="text-muted mb-4">Unfortunately, your store application was not approved.</p>

                            @if($store->rejection_reason)
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle me-1"></i> Rejection Reason</h6>
                                <p class="mb-0">{{ $store->rejection_reason }}</p>
                            </div>
                            @endif

                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('seller.store.edit') }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i> Update Store Information
                                </a>
                                <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Store Information -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Store Name:</td>
                                            <td>{{ $store->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td>{{ $store->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Email:</td>
                                            <td>{{ $store->email ?: 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Submitted:</td>
                                            <td>{{ $store->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Description:</strong>
                                        <p class="text-muted mt-1">{{ $store->description }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Address:</strong>
                                        <p class="text-muted mt-1">{{ $store->address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Timeline -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Application Timeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item completed">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Application Submitted</h6>
                                        <small class="text-muted">{{ $store->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>

                                @if($store->status === 'pending')
                                <div class="timeline-item active">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Under Review</h6>
                                        <small class="text-muted">In progress...</small>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-light"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1 text-muted">Approval Decision</h6>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                                @elseif($store->status === 'approved')
                                <div class="timeline-item completed">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Store Approved</h6>
                                        <small class="text-muted">{{ $store->updated_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                                @elseif($store->status === 'rejected')
                                <div class="timeline-item completed">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Application Rejected</h6>
                                        <small class="text-muted">{{ $store->updated_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Contact Support -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Need Help?</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">If you have questions about your application status, please
                                contact our support team.</p>
                            <a href="mailto:support@example.com" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope me-1"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
    }

    .timeline-item.completed .timeline-marker {
        background-color: var(--bs-success) !important;
    }

    .timeline-item.active .timeline-marker {
        background-color: var(--bs-warning) !important;
    }
</style>
@endsection