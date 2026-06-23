@extends('layouts.admin')
@section('title','Admin Produk — SobatAnak')
@section('page-title','Produk')
@section('admin-content')
<section>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 style="font-family:'Fredoka',system-ui,sans-serif;font-size:1.9rem;font-weight:800;color:#263D3B;margin:0">Kelola <span style="color:#49C5B6">Produk</span></h1>
            <p style="color:#6B8A88;font-weight:800;font-size:.9rem;margin-top:.25rem">Halaman ini menampilkan produk yang sudah ditambahkan dan tampil di website.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.create') }}" class="btn-pill btn-coral">+ Tambah Produk Baru</a>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-4 mb-5 border-green-200 bg-green-50 text-green-700 font-black">{{ session('success') }}</div>
    @endif
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

    <div class="flex items-center justify-between gap-4 mb-4">
        <h2 class="font-display text-3xl">Daftar Produk</h2>
        <p class="text-[#6B8A88] font-black">{{ $products->count() }} produk ditemukan</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        @forelse($products as $product)
            @php
                $stock = (int) ($product->stock ?? 0);
                $reviewCount = (int) ($product->reviews_count ?? 0);
                $autoRating = $reviewCount > 0 ? round((float) ($product->reviews_avg_rating ?? 0), 1) : 0;
                $ratingText = $reviewCount > 0 ? 'Dari '.$reviewCount.' ulasan user' : 'Belum ada ulasan user';
                $galleryCount = \Illuminate\Support\Facades\Schema::hasTable('product_images')
                    ? \Illuminate\Support\Facades\DB::table('product_images')->where('product_id', $product->id)->count()
                    : 0;
            @endphp
            <div class="card p-5 grid md:grid-cols-[120px_1fr] gap-4 {{ $stock <= 0 ? 'admin-stock-empty' : '' }}">
                <div class="relative">
                    <img src="{{ $product->image }}" class="w-full h-32 object-cover rounded-2xl">
                    <span class="admin-stock-badge {{ $stock <= 0 ? 'empty' : '' }}">{{ $stock <= 0 ? 'Stok Habis' : 'Stok '.$stock }}</span>
                    <span class="admin-gallery-count-badge">📷 {{ $galleryCount ?: 1 }} foto</span>
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
                        <label class="font-black text-sm">Ganti Gambar Utama Produk
                            <input type="file" name="image_file" accept="image/png,image/jpeg,image/jpg,image/webp" class="auth-input mt-2">
                            <small class="block text-[#6B8A88] mt-1 font-bold">Kosongkan kalau gambar utama tidak diganti.</small>
                        </label>
                        <label class="font-black text-sm">Tambah Foto Gallery Produk
                            <input type="file" name="gallery_images[]" accept="image/png,image/jpeg,image/jpg,image/webp" class="auth-input mt-2" multiple>
                            <small class="block text-[#6B8A88] mt-1 font-bold">Pilih 1, 2, 3, atau beberapa foto tambahan sesuai kebutuhan.</small>
                        </label>
                        <label class="admin-gallery-replace-check">
                            <input type="checkbox" name="replace_gallery" value="1">
                            <span>Ganti semua foto gallery lama dengan gambar utama + foto baru</span>
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
                <a href="{{ route('admin.products.create') }}" class="btn-pill btn-coral mt-4 inline-flex">+ Tambah Produk Baru</a>
            </div>
        @endforelse
    </div>
</section>
@endsection
