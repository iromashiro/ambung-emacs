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

    <style>
        /* Custom Scrollbar for Sidebar */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Smooth scrolling */
        #sidebar-wrapper {
            scroll-behavior: smooth;
            height: 100vh;
            overflow-y: auto;
        }

        /* Sticky header */
        .sidebar-heading.sticky-top {
            z-index: 1020;
        }

        /* Menu item hover effects */
        .list-group-item-action:hover {
            background-color: #f8f9fa;
            border-left: 3px solid var(--bs-primary);
        }

        .list-group-item-action.active {
            background-color: var(--bs-primary);
            color: white;
            border-left: 3px solid var(--bs-dark);
        }

        .list-group-item-action.active i {
            color: white;
        }

        /* Badge positioning */
        .list-group-item .badge {
            font-size: 0.7rem;
        }

        /* Section headers */
        .list-group-item.bg-light {
            background-color: #f8f9fa !important;
            font-weight: 600;
            padding: 8px 16px;
            border: none;
        }

        /* Animation for badges */
        .badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                width: 100% !important;
                height: auto !important;
                max-height: 70vh;
            }
        }

        /* Animation for menu items */
        .list-group-item-action {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-white border-end shadow-sm" id="sidebar-wrapper" style="width: 250px;">
            <!-- Sidebar Heading -->
            <div class="sidebar-heading bg-primary text-white p-3 d-flex align-items-center sticky-top">
                <i class="fas fa-store me-2"></i>
                <span class="ms-2">Seller Center</span>
            </div>

            <!-- Sidebar Menu with Scroll -->
            <div class="list-group list-group-flush" style="padding-bottom: 20px;">
                <!-- Dashboard -->
                <a href="{{ route('seller.dashboard') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>

                @php
                $user = auth()->user();
                $store = $user->store;
                @endphp

                <!-- Store Management -->
                @if(!$store)
                <a href="{{ route('seller.store.setup') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.store.setup') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle me-2 text-success"></i> Setup Store
                </a>
                @else
                <!-- Store Menu -->
                <div class="list-group-item bg-light border-0">
                    <small class="text-muted fw-bold">STORE MANAGEMENT</small>
                </div>

                <a href="{{ route('seller.store.show') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.store.show') ? 'active' : '' }}">
                    <i class="fas fa-store me-2"></i> My Store
                </a>

                <a href="{{ route('seller.store.edit') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.store.edit') ? 'active' : '' }}">
                    <i class="fas fa-edit me-2"></i> Edit Store
                </a>

                @if($store->status === 'pending')
                <a href="{{ route('seller.store.status') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.store.status') ? 'active' : '' }}">
                    <i class="fas fa-clock me-2 text-warning"></i> Store Status
                </a>
                @endif

                @if($store->status === 'active')
                <!-- Products Menu -->
                <div class="list-group-item bg-light border-0">
                    <small class="text-muted fw-bold">PRODUCTS</small>
                </div>

                <a href="{{ route('seller.products.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.products.index') ? 'active' : '' }}">
                    <i class="fas fa-box me-2"></i> All Products
                </a>

                <a href="{{ route('seller.products.create') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.products.create') ? 'active' : '' }}">
                    <i class="fas fa-plus me-2"></i> Add Product
                </a>

                <!-- Orders Menu -->
                <div class="list-group-item bg-light border-0">
                    <small class="text-muted fw-bold">ORDERS</small>
                </div>

                <a href="{{ route('seller.orders.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag me-2"></i> All Orders
                </a>

                <a href="{{ route('seller.orders.new') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.orders.new') ? 'active' : '' }}">
                    <i class="fas fa-bell me-2 text-info"></i> New Orders
                    @if(isset($newOrdersCount) && $newOrdersCount > 0)
                    <span class="badge bg-danger ms-auto">{{ $newOrdersCount }}</span>
                    @endif
                </a>

                <a href="{{ route('seller.orders.processing') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.orders.processing') ? 'active' : '' }}">
                    <i class="fas fa-cog me-2 text-warning"></i> Processing
                </a>

                <a href="{{ route('seller.orders.completed') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.orders.completed') ? 'active' : '' }}">
                    <i class="fas fa-check-circle me-2 text-success"></i> Completed
                </a>

                <a href="{{ route('seller.orders.canceled') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.orders.canceled') ? 'active' : '' }}">
                    <i class="fas fa-times-circle me-2 text-danger"></i> Canceled
                </a>

                <!-- Reports Menu -->
                <div class="list-group-item bg-light border-0">
                    <small class="text-muted fw-bold">REPORTS</small>
                </div>

                <a href="{{ route('seller.reports.sales') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.reports.sales') ? 'active' : '' }}">
                    <i class="fas fa-chart-line me-2"></i> Sales Report
                </a>

                <a href="{{ route('seller.reports.products') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.reports.products') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar me-2"></i> Products Report
                </a>

                <a href="{{ route('seller.reports.inventory') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.reports.inventory') ? 'active' : '' }}">
                    <i class="fas fa-warehouse me-2"></i> Inventory Report
                </a>
                @endif
                @endif

                <!-- Profile Menu -->
                <div class="list-group-item bg-light border-0">
                    <small class="text-muted fw-bold">ACCOUNT</small>
                </div>

                <a href="{{ route('seller.profile.show') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.profile.show') ? 'active' : '' }}">
                    <i class="fas fa-user me-2"></i> My Profile
                </a>

                <a href="{{ route('seller.profile.edit') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('seller.profile.edit') ? 'active' : '' }}">
                    <i class="fas fa-user-edit me-2"></i> Edit Profile
                </a>

                <!-- Logout -->
                <div class="list-group-item border-0" style="margin-top: 10px;">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                            onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </div>
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
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
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
                // Improved implementation
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const wrapper = document.getElementById('wrapper');
                    if (wrapper) {
                        wrapper.classList.toggle('toggled');
                    }
                });
            }

            // Auto-scroll to active menu item
            const activeMenuItem = document.querySelector('.list-group-item-action.active');
            if (activeMenuItem) {
                activeMenuItem.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            // Smooth scroll for menu clicks
            const menuItems = document.querySelectorAll('.list-group-item-action');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Add loading state
                    this.style.opacity = '0.7';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 200);
                });
            });

            // Remember scroll position
            const sidebar = document.getElementById('sidebar-wrapper');
            const scrollPosition = localStorage.getItem('sidebarScrollPosition');
            if (scrollPosition) {
                sidebar.scrollTop = scrollPosition;
            }

            // Save scroll position
            sidebar.addEventListener('scroll', function() {
                localStorage.setItem('sidebarScrollPosition', this.scrollTop);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')
</body>

</html>