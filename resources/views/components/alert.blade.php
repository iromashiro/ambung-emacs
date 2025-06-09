@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-check-circle fa-2x"></i>
        </div>
        <div>
            <h5 class="alert-heading">Success!</h5>
            <p class="mb-0">{{ session('success') }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-exclamation-circle fa-2x"></i>
        </div>
        <div>
            <h5 class="alert-heading">Error!</h5>
            <p class="mb-0">{{ session('error') }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-exclamation-triangle fa-2x"></i>
        </div>
        <div>
            <h5 class="alert-heading">Warning!</h5>
            <p class="mb-0">{{ session('warning') }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-info-circle fa-2x"></i>
        </div>
        <div>
            <h5 class="alert-heading">Information</h5>
            <p class="mb-0">{{ session('info') }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-exclamation-circle fa-2x"></i>
        </div>
        <div>
            <h5 class="alert-heading">Please fix the following errors:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif