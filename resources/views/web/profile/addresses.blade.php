{{-- resources/views/web/profile/addresses.blade.php --}}
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
                    <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                    <a href="{{ route('profile.addresses') }}" class="list-group-item list-group-item-action active">
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
                    <h5 class="mb-0">My Addresses</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addAddressModal">
                        <i class="fas fa-plus me-1"></i> Add New Address
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($addresses->count() > 0)
                    <div class="row">
                        @foreach($addresses as $address)
                        <div class="col-md-6 mb-3">
                            <div class="card {{ $address->is_default ? 'border-primary' : '' }}">
                                <div class="card-body">
                                    @if($address->is_default)
                                    <span class="badge bg-primary mb-2">Default Address</span>
                                    @endif
                                    <h6 class="card-title">{{ $address->name }}</h6>
                                    <p class="card-text mb-1">{{ $address->phone }}</p>
                                    <p class="card-text">
                                        {{ $address->address_line1 }}<br>
                                        @if($address->address_line2)
                                        {{ $address->address_line2 }}<br>
                                        @endif
                                        {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                                    </p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="editAddress({{ $address->id }}, '{{ $address->name }}', '{{ $address->phone }}', '{{ $address->address_line1 }}', '{{ $address->address_line2 }}', '{{ $address->city }}', '{{ $address->state }}', '{{ $address->postal_code }}', {{ $address->is_default ? 'true' : 'false' }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        @if(!$address->is_default)
                                        <form action="{{ route('profile.addresses.destroy', $address) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this address?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                        <h5>No addresses found</h5>
                        <p class="text-muted">Add your first address to get started</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.addresses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1 <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                    </div>

                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">Postal Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Address Modal -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAddressModalLabel">Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAddressForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Full Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_address_line1" class="form-label">Address Line 1 <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_address_line1" name="address_line1" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="edit_address_line2" name="address_line2">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_city" name="city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_state" name="state" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_postal_code" class="form-label">Postal Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_postal_code" name="postal_code" required>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_is_default" name="is_default">
                        <label class="form-check-label" for="edit_is_default">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function editAddress(id, name, phone, address_line1, address_line2, city, state, postal_code, is_default) {
    document.getElementById('editAddressForm').action = `/profile/addresses/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_address_line1').value = address_line1;
    document.getElementById('edit_address_line2').value = address_line2;
    document.getElementById('edit_city').value = city;
    document.getElementById('edit_state').value = state;
    document.getElementById('edit_postal_code').value = postal_code;
    document.getElementById('edit_is_default').checked = is_default;

    new bootstrap.Modal(document.getElementById('editAddressModal')).show();
}
</script>
@endpush
@endsection