@php
    $cartCount = collect(session('cart', []))->sum('qty');
@endphp

<header class="bg-white border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" class="w-12 h-12 rounded-full object-cover" alt="SobatAnak">

            <div>
                <h1 class="text-xl sm:text-2xl font-black text-teal leading-none">
                    SobatAnak
                </h1>
                <p class="text-xs text-gray-500 tracking-widest mt-1">
                    TOKO BAYI & ANAK
                </p>
            </div>
        </a>

        <nav class="hidden lg:flex items-center gap-8 font-bold text-gray-700">
            <a href="{{ route('home') }}" class="hover:text-teal">Beranda</a>
            <a href="{{ route('products') }}" class="hover:text-teal">Produk</a>
            <a href="{{ route('mini-games') }}" class="hover:text-teal">Mini Games</a>
            <a href="{{ route('articles') }}" class="hover:text-teal">Artikel</a>
        </nav>

        <div class="hidden md:flex items-center gap-3">
            <input class="w-56 lg:w-72 rounded-full border px-5 py-3 outline-none"
                   placeholder="Cari produk bayi...">

            <div class="bg-yellow/40 px-5 py-3 rounded-full font-black">
                ⭐ 1.250 Poin
            </div>

            <a href="{{ route('cart.index') }}"
               class="relative bg-coral text-white px-5 py-3 rounded-full font-black hover:opacity-90 transition">
                Keranjang

                @if($cartCount > 0)
                    <span class="absolute -top-2 -right-2 bg-yellow text-ink text-xs font-black w-6 h-6 rounded-full flex items-center justify-center">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
        </div>

        <a href="{{ route('cart.index') }}"
           class="md:hidden relative bg-coral text-white px-4 py-3 rounded-full font-black">
            🛒

            @if($cartCount > 0)
                <span class="absolute -top-2 -right-2 bg-yellow text-ink text-xs font-black w-6 h-6 rounded-full flex items-center justify-center">
                    {{ $cartCount }}
                </span>
            @endif
        </a>
    </div>

    <nav class="lg:hidden border-t bg-white px-4 py-3 flex items-center justify-between text-sm font-bold overflow-x-auto gap-4">
        <a href="{{ route('home') }}" class="whitespace-nowrap">Beranda</a>
        <a href="{{ route('products') }}" class="whitespace-nowrap">Produk</a>
        <a href="{{ route('mini-games') }}" class="whitespace-nowrap">Mini Games</a>
        <a href="{{ route('articles') }}" class="whitespace-nowrap">Artikel</a>
    </nav>
</header>