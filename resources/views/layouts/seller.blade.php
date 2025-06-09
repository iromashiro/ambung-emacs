<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seller Dashboard') - Ambung Emac</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link href="{{ asset('css/seller.css') }}" rel="stylesheet">

    @stack('styles')

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-end bg-white" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom bg-primary text-white p-3">
                <img src="{{ asset('images/logo-white.png') }}" alt="Ambung Emac" height="30">
                <span class="ms-2">Seller Center</span>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('seller.dashboard') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>

                @if($store && $store->status === 'approved')
                <a href="{{ route('seller.products.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.products.*') ? 'active' : '' }}">
                    <i class="fas fa-box me-2"></i> Products
                </a>
                <a href="{{ route('seller.orders.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag me-2"></i> Orders
                </a>
                <a href="{{ route('seller.reviews.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star me-2"></i> Reviews
                </a>
                <a href="{{ route('seller.reports.sales') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar me-2"></i> Reports
                </a>
                @endif

                <a href="{{ route('seller.store.edit') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.store.*') ? 'active' : '' }}">
                    <i class="fas fa-store me-2"></i> Store Settings
                </a>
                <a href="{{ route('seller.profile.edit') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user me-2"></i> Profile
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-sm btn-outline-primary" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="ms-auto d-flex align-items-center">
                        @if($store && $store->status === 'approved')
                        <div class="dropdown me-3">
                            <button class="btn btn-link text-dark dropdown-toggle text-decoration-none" type="button"
                                id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                @if($unreadNotifications > 0)
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadNotifications }}
                                </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown"
                                style="width: 300px;">
                                <li>
                                    <h6 class="dropdown-header">Notifications</h6>
                                </li>

                                @if(count($notifications) > 0)
                                @foreach($notifications as $notification)
                                <li>
                                    <a class="dropdown-item {{ $notification->read_at ? '' : 'bg-light' }}"
                                        href="{{ $notification->data['url'] ?? '#' }}">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i
                                                    class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} text-{{ $notification->data['color'] ?? 'primary' }}"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <p class="mb-0">{{ $notification->data['message'] }}</p>
                                                <small
                                                    class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-center"
                                        href="{{ route('seller.notifications.index') }}">View All</a></li>
                                @else
                                <li><a class="dropdown-item text-center py-3" href="#">No notifications</a></li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <div class="dropdown">
                            <button
                                class="btn btn-link text-dark dropdown-toggle text-decoration-none d-flex align-items-center"
                                type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ auth()->user()->avatar_url ?? asset('images/avatar-default.png') }}"
                                    alt="{{ auth()->user()->name }}" class="rounded-circle me-2" width="32" height="32">
                                <span>{{ auth()->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('seller.profile.edit') }}"><i
                                            class="fas fa-user me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('seller.store.edit') }}"><i
                                            class="fas fa-store me-2"></i> Store Settings</a></li>
                                <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank"><i
                                            class="fas fa-external-link-alt me-2"></i> View Store</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i
                                                class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @include('components.alert')

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.getElementById('wrapper').classList.toggle('toggled');
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>