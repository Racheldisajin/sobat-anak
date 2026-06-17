@extends('layouts.app')
@section('title',($mode==='create'?'Tambah':'Edit').' Postingan Berita — SobatAnak')
@section('content')
<section class="admin-post-hero compact">
    <div class="max-w-5xl mx-auto px-6 md:px-12">
        <div class="admin-form-top-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-pill admin-back-dashboard-btn">← Admin Dashboard</a>
            <a href="{{ route('admin.articles') }}" class="btn-pill admin-back-articles-btn">← Daftar Artikel</a>
        </div>
        <span class="admin-kicker">Admin Postingan Berita</span>
        <h1 class="font-display section-title mt-3">{{ $mode==='create' ? 'Buat Artikel Baru' : 'Edit Artikel' }}</h1>
        <p class="admin-muted mt-2">Isi artikel parenting, kesehatan anak, produk, atau tips Mom & Baby Care. Gambar wajib untuk artikel baru.</p>
    </div>
</section>

<section class="max-w-5xl mx-auto px-6 md:px-12 py-10">
    @if($errors->any())
        <div class="admin-alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $mode==='create' ? route('admin.articles.store') : route('admin.articles.update',$article) }}" class="admin-post-form">
        @csrf
        @if($mode==='edit') @method('PATCH') @endif

        <div class="admin-form-card main">
            <div class="admin-form-head">
                <div>
                    <span>Konten Artikel</span>
                    <h2>{{ $mode==='create' ? 'Tulis postingan baru' : 'Perbarui postingan' }}</h2>
                </div>
                <a href="{{ route('admin.articles') }}" class="btn-pill bg-white border border-[#D4EEEC]">← Kembali</a>
            </div>

            <label class="admin-field full">Judul Postingan
                <input name="title" value="{{ old('title',$article->title) }}" required placeholder="Contoh: Tips Memilih Popok Newborn yang Aman">
            </label>

            <label class="admin-field full">Slug URL
                <input name="slug" value="{{ old('slug',$article->slug) }}" placeholder="otomatis kalau dikosongkan">
                <small>Slug dipakai untuk URL artikel. Contoh: tips-memilih-popok-newborn</small>
            </label>

            <div class="admin-form-grid two">
                <label class="admin-field">Kategori
                    <select name="category_id" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string)old('category_id',$article->category_id)===(string)$category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">Status
                    <select name="status" required>
                        <option value="published" @selected(old('status',$article->status ?: 'published')==='published')>Published</option>
                        <option value="draft" @selected(old('status',$article->status)==='draft')>Draft</option>
                    </select>
                    <small>Draft tidak tampil di halaman artikel user.</small>
                </label>
            </div>

            <label class="admin-field full">Tags
                <input name="tags" value="{{ old('tags',$article->tags) }}" placeholder="parenting, bayi, mom tips">
                <small>Pisahkan tag dengan koma agar AI dan search artikel lebih mudah menemukan konten.</small>
            </label>

            <label class="admin-field full">Isi Artikel / Content
                <textarea name="content" required placeholder="Tulis isi artikel lengkap di sini...">{{ old('content',$article->content) }}</textarea>
            </label>
        </div>

        <aside class="admin-form-card side">
            <span class="admin-side-title">Gambar Artikel</span>
            <div class="admin-image-preview-big">
                @if($mode==='edit' && $article->image)
                    <img src="{{ $article->image }}" alt="Preview artikel" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
                @else
                    <div><b>Preview</b><small>Gambar akan muncul di sini</small></div>
                @endif
            </div>
            <label class="admin-field full">Upload Gambar
                <input type="file" name="image_file" accept="image/png,image/jpeg,image/jpg,image/webp" {{ $mode==='create' ? 'required' : '' }}>
                <small>Format JPG, PNG, WEBP. Maksimal 4MB. {{ $mode==='edit' ? 'Kosongkan kalau gambar tidak diganti.' : '' }}</small>
            </label>
            <button class="btn-pill btn-coral w-full">{{ $mode==='create' ? 'Simpan Artikel' : 'Update Artikel' }}</button>
            <p class="admin-form-note">Pastikan judul, kategori, dan isi artikel sudah sesuai sebelum publish.</p>
        </aside>
    </form>
</section>
@endsection
