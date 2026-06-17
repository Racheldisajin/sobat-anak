@extends('layouts.app')
@section('title','Admin Produk — SobatAnak')
@section('content')
<section class="max-w-7xl mx-auto px-6 md:px-12 py-10">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <span class="text-coral font-black uppercase tracking-widest text-xs">Admin CRUD</span>
            <h1 class="font-display section-title">Kelola <span class="text-teal">Produk</span></h1>
            <p class="text-[#6B8A88] font-bold mt-2">Tambah produk, upload gambar, atur stok, dan cari produk admin dengan cepat.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-pill btn-teal">Dashboard</a>
    </div>

    @if($errors->any())
        <div class="card p-4 mb-5 border-red-200 bg-red-50 text-red-600 font-black">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('admin.products') }}" class="admin-search-card mb-6">
        <div class="admin-search-input-wrap">
            <span>🔎</span>
            <input name="search" value="{{ $search ?? '' }}" placeholder="Cari nama produk, kategori, atau badge...">
        </div>
        <select name="stock" class="admin-search-select">
            <option value="" {{ empty($stockFilter) ? 'selected' : '' }}>Semua stok</option>
            <option value="tersedia" {{ ($stockFilter ?? '') === 'tersedia' ? 'selected' : '' }}>Stok tersedia</option>
            <option value="habis" {{ ($stockFilter ?? '') === 'habis' ? 'selected' : '' }}>Stok habis</option>
        </select>
        <button class="btn-pill btn-teal">Cari</button>
        @if(($search ?? '') !== '' || ($stockFilter ?? '') !== '')
            <a href="{{ route('admin.products') }}" class="btn-pill bg-white border border-[#D4EEEC]">Reset</a>
        @endif
    </form>

    <div class="card p-6 mb-8">
        <h2 class="font-display text-3xl mb-4">Tambah Produk</h2>
        <div class="admin-rating-info mb-5">
            <b>⭐ Rating otomatis</b>
            <span>Rating produk tidak diinput manual. Nilainya dihitung dari ulasan/rating user di halaman produk.</span>
        </div>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.products.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <label class="admin-field-label">Nama Produk
                <input name="name" class="auth-input mt-2" placeholder="Contoh: Botol Susu Anti-Kolik" required>
            </label>
            <label class="admin-field-label">Kategori / Usia
                <input name="category" class="auth-input mt-2" placeholder="Contoh: Bayi 0–12 bln" required>
            </label>
            <label class="admin-field-label">Harga Produk
                <input name="price" type="number" class="auth-input mt-2" placeholder="Contoh: 189000" required>
            </label>
            <label class="admin-field-label">Stok Barang
                <input name="stock" type="number" min="0" class="auth-input mt-2" placeholder="Contoh: 20" required>
            </label>
            <label class="admin-field-label">Badge / Label Produk
                <input name="badge" class="auth-input mt-2" placeholder="Contoh: Terlaris, Baru, Stok Terbatas">
            </label>
            <div class="admin-field-label admin-auto-rating-create">
                <span>Rating Produk</span>
                <div class="admin-auto-rating-box">
                    <strong>Otomatis</strong>
                    <small>Dihitung dari review user setelah produk mendapat ulasan.</small>
                </div>
            </div>
            <label class="admin-field-label">Jumlah Terjual
                <input name="sold" type="number" class="auth-input mt-2" placeholder="Contoh: 3241">
            </label>
            <label class="admin-field-label md:col-span-2">Upload Gambar Produk
                <input type="file" name="image_file" accept="image/png,image/jpeg,image/jpg,image/webp" class="auth-input mt-2" required>
                <small class="block text-[#6B8A88] mt-2 font-bold">Format: JPG, PNG, WEBP. Maksimal 4MB.</small>
            </label>
            <button class="btn-pill btn-coral w-fit">Tambah Produk</button>
        </form>
    </div>

    <div class="flex items-center justify-between gap-4 mb-4">
        <h2 class="font-display text-3xl">Daftar Produk</h2>
        <p class="text-[#6B8A88] font-black">{{ $products->count() }} produk ditemukan</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        @forelse($products as $product)
            @php
                $stock = (int) ($product->stock ?? 0);
                $reviewCount = (int) ($product->reviews_count ?? 0);
                $autoRating = $reviewCount > 0
                    ? round((float) ($product->reviews_avg_rating ?? 0), 1)
                    : 0;
                $ratingText = $reviewCount > 0
                    ? 'Dari '.$reviewCount.' ulasan user'
                    : 'Belum ada ulasan user';
            @endphp
            <div class="card p-5 grid md:grid-cols-[120px_1fr] gap-4 {{ $stock <= 0 ? 'admin-stock-empty' : '' }}">
                <div class="relative">
                    <img src="{{ $product->image }}" class="w-full h-32 object-cover rounded-2xl">
                    <span class="admin-stock-badge {{ $stock <= 0 ? 'empty' : '' }}">{{ $stock <= 0 ? 'Stok Habis' : 'Stok '.$stock }}</span>
                </div>
                <div class="grid gap-3">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.products.update',$product) }}" class="grid gap-3">
                        @csrf @method('PATCH')
                        <label class="admin-field-label compact">Nama Produk
                            <input name="name" value="{{ $product->name }}" class="auth-input mt-2" placeholder="Nama produk">
                        </label>
                        <label class="admin-field-label compact">Kategori / Usia
                            <input name="category" value="{{ $product->category }}" class="auth-input mt-2" placeholder="Kategori / usia">
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="admin-field-label compact">Harga
                                <input name="price" type="number" value="{{ $product->price }}" class="auth-input mt-2" placeholder="Harga">
                            </label>
                            <label class="admin-field-label compact">Stok Barang
                                <input name="stock" type="number" min="0" value="{{ $stock }}" class="auth-input mt-2" placeholder="Stok">
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="admin-field-label compact">Badge / Label
                                <input name="badge" value="{{ $product->badge }}" class="auth-input mt-2" placeholder="Badge">
                            </label>
                            <div class="admin-field-label compact">
                                <span>Rating Otomatis</span>
                                <div class="admin-auto-rating-box small">
                                    <strong>⭐ {{ number_format($autoRating, 1) }}</strong>
                                    <small>{{ $ratingText }}</small>
                                </div>
                            </div>
                        </div>
                        <label class="admin-field-label compact">Jumlah Terjual
                            <input name="sold" type="number" value="{{ $product->sold }}" class="auth-input mt-2" placeholder="Terjual">
                        </label>
                        <label class="font-black text-sm">Ganti Gambar Produk
                            <input type="file" name="image_file" accept="image/png,image/jpeg,image/jpg,image/webp" class="auth-input mt-2">
                            <small class="block text-[#6B8A88] mt-1 font-bold">Kosongkan kalau gambar tidak diganti.</small>
                        </label>
                        <div class="flex gap-2">
                            <button class="btn-pill btn-teal text-xs py-2">Update</button>
                    </form>
                    <form method="POST" action="{{ route('admin.products.destroy',$product) }}">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Hapus produk?')" class="btn-pill btn-coral text-xs py-2">Hapus</button>
                    </form>
                        </div>
                </div>
            </div>
        @empty
            <div class="card p-8 lg:col-span-2 text-center">
                <h3 class="font-display text-3xl">Produk tidak ditemukan</h3>
                <p class="text-[#6B8A88] font-bold mt-2">Coba gunakan kata kunci lain atau reset filter.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
