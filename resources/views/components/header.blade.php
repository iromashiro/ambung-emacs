<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Ambung e-MAC" height="100px" width="270px">
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
                    {{-- resources/views/layouts/app.blade.php - Update bagian user dropdown --}}

                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" class="rounded-circle me-1"
                                style="width: 24px; height: 24px; object-fit: cover;" alt="{{ auth()->user()->name }}">
                            @endif
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            @if(auth()->user()->role === 'buyer')
                            <li><a class="dropdown-item" href="{{ route('buyer.profile.show') }}">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a></li>
                            @elseif(auth()->user()->role === 'seller')
                            <li><a class="dropdown-item" href="{{ route('seller.profile.show') }}">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">
                                    <i class="fas fa-shopping-bag me-2"></i>My Orders
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endauth
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