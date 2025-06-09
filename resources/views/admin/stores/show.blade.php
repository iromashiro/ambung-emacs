@extends('layouts.admin')

@section('title', 'Store Review')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Store Review</h1>
        <a href="{{ route('admin.stores.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Stores
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Store Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Store Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <img src="{{ $store->logo_url ?? asset('images/store-default.png') }}"
                                alt="{{ $store->name }}" class="img-thumbnail rounded-circle"
                                style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                        <div class="col-md-9">
                            <h3>{{ $store->name }}</h3>
                            <p class="text-muted">{{ $store->description }}</p>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>Phone:</strong> {{ $store->phone }}</p>
                                    <p><strong>Email:</strong> {{ $store->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Address:</strong> {{ $store->address }}</p>
                                    <p><strong>City:</strong> {{ $store->city }}, {{ $store->province }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Owner Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Owner Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            <img src="{{ $store->user->avatar_url ?? asset('images/avatar-default.png') }}"
                                alt="{{ $store->user->name }}" class="img-thumbnail rounded-circle"
                                style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div class="col-md-10">
                            <h5>{{ $store->user->name }}</h5>
                            <p class="mb-1"><strong>Email:</strong> {{ $store->user->email }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $store->user->phone }}</p>
                            <p class="mb-0"><strong>Joined:</strong> {{ $store->user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Documents -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Business Documents</h5>
                </div>
                <div class="card-body">
                    @if($store->documents && count($store->documents) > 0)
                    <div class="row g-3">
                        @foreach($store->documents as $document)
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $document->name }}</h6>
                                    <p class="card-text small text-muted">Uploaded:
                                        {{ $document->created_at->format('d M Y') }}</p>
                                    <a href="{{ $document->url }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="fas fa-eye me-1"></i> View Document
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i> No business documents uploaded
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Approval Actions -->
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Approval Actions</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading">Store Status: {{ ucfirst($store->status) }}</h6>
                                <p class="mb-0">Submitted on {{ $store->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.stores.approve', $store) }}" method="POST" id="approvalForm">
                        @csrf

                        <div class="mb-3">
                            <label for="approval_notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="approval_notes" name="approval_notes"
                                rows="3"></textarea>
                            <div class="form-text">These notes will be visible to the store owner</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="submitApproval('approve')">
                                <i class="fas fa-check-circle me-1"></i> Approve Store
                            </button>
                            <button type="button" class="btn btn-danger" onclick="submitApproval('reject')">
                                <i class="fas fa-times-circle me-1"></i> Reject Store
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Store History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Store History</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($store->history as $history)
                        <li class="list-group-item px-0">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-{{ $history->status_color }} text-white d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ $history->status_icon }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">{{ $history->status_label }}</h6>
                                        <small
                                            class="text-muted">{{ $history->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <p class="mb-0">{{ $history->notes }}</p>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionModalLabel">Reject Store</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4"
                            required></textarea>
                        <div class="form-text">Please provide a clear reason for rejection to help the store owner make
                            necessary improvements.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmReject">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function submitApproval(action) {
        const form = document.getElementById('approvalForm');

        if (action === 'approve') {
            form.action = "{{ route('admin.stores.approve', $store) }}";
            form.submit();
        } else if (action === 'reject') {
            // Show rejection modal
            const rejectionModal = new bootstrap.Modal(document.getElementById('rejectionModal'));
            rejectionModal.show();

            // Handle rejection confirmation
            document.getElementById('confirmReject').addEventListener('click', function() {
                const rejectionReason = document.getElementById('rejection_reason').value;

                if (!rejectionReason.trim()) {
                    alert('Please provide a rejection reason');
                    return;
                }

                // Add rejection reason to form
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'rejection_reason';
                reasonInput.value = rejectionReason;
                form.appendChild(reasonInput);

                // Submit form
                form.action = "{{ route('admin.stores.reject', $store) }}";
                form.submit();
            });
        }
    }
</script>
@endpush
@endsection