@extends('layouts.admin')
@section('title','Tambah Produk — Admin SobatAnak')
@section('page-title','Tambah Produk')
@section('admin-content')
<section>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <span class="text-coral font-black uppercase tracking-widest text-xs">Admin CRUD</span>
            <h1 style="font-family:'Fredoka',system-ui,sans-serif;font-size:2.2rem;font-weight:800;color:#263D3B;margin:.35rem 0 0">Tambah <span style="color:#49C5B6">Produk Baru</span></h1>
            <p style="color:#6B8A88;font-weight:800;font-size:.9rem;margin-top:.25rem">Isi data produk, stok, harga, badge, dan gambar agar produk tampil di website.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn-pill bg-white border border-[#D4EEEC]">← Dashboard</a>
            <a href="{{ route('admin.products') }}" class="btn-pill bg-white border border-[#D4EEEC]">← Kembali ke Produk</a>
        </div>
    </div>

    @if($errors->any())
        <div class="card p-4 mb-5 border-red-200 bg-red-50 text-red-600 font-black">{{ $errors->first() }}</div>
    @endif

    <div class="card p-6 mb-8">
        <h2 class="font-display text-3xl mb-4">Form Produk</h2>
        <div class="admin-rating-info mb-5">
            <b>⭐ Rating otomatis</b>
            <span>Rating produk tidak perlu diinput manual. Nilainya dihitung dari ulasan/rating user di halaman produk.</span>
        </div>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.products.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <label class="admin-field-label">Nama Produk
                <input name="name" value="{{ old('name') }}" class="auth-input mt-2" placeholder="Contoh: Botol Susu Anti-Kolik" required>
            </label>
            <label class="admin-field-label">Kategori / Usia
                <input name="category" value="{{ old('category') }}" class="auth-input mt-2" placeholder="Contoh: Bayi 0–12 bln" required>
            </label>
            <label class="admin-field-label">Harga Produk
                <input name="price" value="{{ old('price') }}" type="number" min="0" class="auth-input mt-2" placeholder="Contoh: 189000" required>
            </label>
            <label class="admin-field-label">Stok Barang
                <input name="stock" value="{{ old('stock') }}" type="number" min="0" class="auth-input mt-2" placeholder="Contoh: 20" required>
            </label>
            <label class="admin-field-label">Badge / Label Produk
                <input name="badge" value="{{ old('badge') }}" class="auth-input mt-2" placeholder="Contoh: Terlaris, Baru, Stok Terbatas">
            </label>
            <div class="admin-field-label admin-auto-rating-create">
                <span>Rating Produk</span>
                <div class="admin-auto-rating-box">
                    <strong>Otomatis</strong>
                    <small>Dihitung dari review user setelah produk mendapat ulasan.</small>
                </div>
            </div>
            <label class="admin-field-label">Jumlah Terjual
                <input name="sold" value="{{ old('sold') }}" type="number" min="0" class="auth-input mt-2" placeholder="Contoh: 3241">
            </label>
            <label class="admin-field-label md:col-span-2">Upload Gambar Utama Produk
                <input type="file" name="image_file" accept="image/png,image/jpeg,image/jpg,image/webp" class="auth-input mt-2" required>
                <small class="block text-[#6B8A88] mt-2 font-bold">Foto utama yang tampil di card produk dan detail produk.</small>
            </label>
            <label class="admin-field-label md:col-span-2">Tambah Foto Gallery Produk <span class="text-[#6B8A88]">(opsional)</span>
                <input type="file" name="gallery_images[]" accept="image/png,image/jpeg,image/jpg,image/webp" class="auth-input mt-2" multiple>
                <small class="block text-[#6B8A88] mt-2 font-bold">Bisa pilih beberapa foto. Gallery akan tampil di halaman detail produk.</small>
            </label>
            <div class="md:col-span-2 flex flex-wrap gap-2">
                <button class="btn-pill btn-coral">Simpan Produk</button>
                <a href="{{ route('admin.products') }}" class="btn-pill bg-white border border-[#D4EEEC]">Batal</a>
            </div>
        </form>
    </div>
</section>
@endsection
