<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Ambung e-MAC" height="70">
                <span style="font: italic">Ambung e-MAC's</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Search Bar -->
                <form class="d-flex mx-auto" style="width: 50%;" action="{{ route('products.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search products...." name="search"
                            value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <!-- Categories Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-list me-1"></i> Categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                            @foreach(\App\Models\Category::take(10)->get() as $category)
                            <li><a class="dropdown-item"
                                    href="{{ route('products.index', ['category' => $category->id]) }}">{{ $category->name }}</a>
                            </li>
                            @endforeach
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('categories.index') }}">All Categories</a></li>
                        </ul>
                    </li>

                    <!-- Cart -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                            <i class="fas fa-shopping-cart"></i>

                            @php
                            $cartCount = auth()->check()
                            ? \App\Models\Cart::where('user_id', auth()->id())->sum('quantity')
                            : 0;
                            @endphp

                            {{-- selalu render span agar JS bisa mengubahnya --}}
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"
                                style="{{ $cartCount > 0 ? '' : 'display:none' }}">
                                {{ $cartCount }}
                            </span>
                        </a>
                    </li>

                    <!-- Authentication Links -->
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                    @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name) }}"
                                class="rounded-circle me-1" width="24" height="24" alt="{{ auth()->user()->name }}">
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            @if(auth()->user()->role === 'admin')
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                            @elseif(auth()->user()->role === 'seller')
                            <li><a class="dropdown-item" href="{{ route('seller.dashboard') }}">Seller Dashboard</a>
                            </li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">My Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">My Orders</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Category Menu -->
    <div class="bg-light border-bottom">
        <div class="container">
            <div class="d-flex overflow-auto py-2">
                @foreach(\App\Models\Category::take(12)->get() as $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}"
                    class="text-decoration-none text-dark me-4 py-2">
                    {{ $category->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
</header>