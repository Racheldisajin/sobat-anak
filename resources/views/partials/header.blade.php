<header class="sticky top-0 z-50 bg-white/88 backdrop-blur-xl border-b border-[#D4EEEC]">
    <div class="site-header-inner max-w-7xl mx-auto px-4 md:px-8 py-3 flex items-center gap-4">
        <a href="{{ route('home') }}" class="brand-wrap flex items-center" style="text-decoration:none">
            <img src="{{ asset('images/logo-cropped.png') }}" alt="SobatAnak" class="brand-logo-img">
        </a>

        <nav class="desktop-nav flex items-center gap-1 ml-5">
            <a class="nav-link {{ request()->routeIs('home')?'active':'' }}" href="{{ route('home') }}">Home</a>
            <a class="nav-link {{ request()->routeIs('products')?'active':'' }}" href="{{ route('products') }}">Produk</a>
            <a class="nav-link {{ request()->routeIs('articles')?'active':'' }}" href="{{ route('articles') }}">Artikel</a>
            <a class="nav-link {{ request()->routeIs('mini-games')?'active':'' }}" href="{{ route('mini-games') }}">Mini Game</a>
        </nav>

        <div class="desktop-search header-search ml-auto relative w-64" data-site-search-wrap>
            <input data-site-search class="w-full pl-4 pr-10 py-2 rounded-full border-2 border-[#D4EEEC] bg-[#E8F5F4] text-sm font-bold" placeholder="Cari produk bayi..." autocomplete="off">
            <span class="absolute right-3 top-2 pointer-events-none">🔎</span>
            <div class="search-dropdown" data-site-search-dropdown>
                <div class="search-empty" data-site-search-empty>Ketik nama produk, contoh: susu, popok, botol.</div>
                <div data-site-search-results></div>
            </div>
        </div>

        <script type="application/json" id="site-products-json">{!! ($searchProducts ?? collect())->map(fn($p)=>[
            'id'=>$p->id,
            'name'=>$p->name,
            'category'=>$p->category,
            'price'=>$p->price,
            'image'=>$p->image,
            'rating'=>$p->rating,
            'sold'=>$p->sold,
            'stock'=>$p->stock ?? 0,
            'url'=>route('products').'#product-'.$p->id,
        ])->values()->toJson(JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>

        <a href="{{ route('mini-games') }}" class="hidden sm:flex btn-pill bg-yellow-100 border border-yellow-300 text-sm">⭐ <span data-points>{{ number_format($authPoints ?: 0,0,',','.') }}</span> Poin</a>
        <a href="{{ $authUser ? route('cart.index') : route('login') }}" class="cart-nav-icon relative w-11 h-11 rounded-full border-2 border-[#E8756A] bg-[#FDECEA] flex items-center justify-center" data-cart-link title="Keranjang">🛍️<span class="absolute -top-1 -right-1 w-5 h-5 bg-[#E8756A] text-white text-[10px] font-black rounded-full flex items-center justify-center" data-cart-count>{{ $authCartCount ?: 0 }}</span></a>

        @if($authUser)
            <div class="relative profile-menu">
                <button data-profile-toggle class="profile-btn">
                    @if(!empty($authUser->avatar))
                        <img src="{{ asset($authUser->avatar) }}" class="profile-avatar header-profile-photo" alt="{{ $authUser->name }}">
                    @else
                        <span class="profile-avatar">{{ strtoupper(substr($authUser->name,0,1)) }}</span>
                    @endif
                    <span class="hidden lg:block text-left leading-tight"><b>{{ $authUser->name }}</b><br><small>Profile</small></span>
                </button>
                <div data-profile-menu class="profile-dropdown"><a href="{{ route('profile') }}">👤 Lihat Profile</a>@if(($authUser->role ?? 'user') === 'admin')<a href="{{ route('admin.dashboard') }}">🛠️ Admin Dashboard</a>@endif<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">🚪 Logout</button></form></div>
            </div>
        @else
            <a href="{{ route('login') }}" class="auth-login-register" title="Login atau Register">
                <span class="auth-login-register-icon">👤</span>
                <span>Login/Register</span>
            </a>
        @endif
        <button data-mobile-toggle class="md:hidden w-11 h-11 rounded-full border-2 border-[#D4EEEC]">☰</button>
    </div>

    <div data-mobile-menu class="mobile-menu md:hidden fixed inset-x-0 top-[76px] bg-white/95 backdrop-blur-xl flex-col gap-4 p-6 border-b border-[#D4EEEC]">
        <a class="nav-link" href="{{ route('home') }}">Home</a><a class="nav-link" href="{{ route('products') }}">Produk</a><a class="nav-link" href="{{ route('articles') }}">Artikel</a><a class="nav-link" href="{{ route('mini-games') }}">Mini Game</a>
        @if($authUser)<a class="nav-link" href="{{ route('profile') }}">Profile</a>@if(($authUser->role ?? 'user') === 'admin')<a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a>@endif<a class="nav-link" href="{{ route('cart.index') }}">Cart</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link text-left">Logout</button></form>@else<a class="nav-link" href="{{ route('login') }}">Login</a><a class="nav-link" href="{{ route('register') }}">Register</a>@endif
    </div>
</header>
