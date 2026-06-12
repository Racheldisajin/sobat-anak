@extends('layouts.app')
@section('title',($mode==='create'?'Tambah':'Edit').' Postingan Berita — SobatAnak')
@section('content')
<section class="bg-gradient-to-br from-[#FDECEA] via-white to-[#D0F0ED] py-12">
    <div class="max-w-4xl mx-auto px-6 md:px-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Admin Postingan Berita</span>
        <h1 class="font-display section-title mt-3">{{ $mode==='create' ? 'Input Postingan Baru' : 'Edit Postingan' }}</h1>
        <p class="text-[#6B8A88] font-bold mt-2">Field artikel sekarang sudah mengikuti tabel <b>posts</b>, <b>post_categories</b>, dan <b>post_tags</b>.</p>
    </div>
</section>
<section class="max-w-4xl mx-auto px-6 md:px-12 py-10">
@if($errors->any())<div class="card p-4 mb-5 border-red-200 bg-red-50 text-red-600 font-black">{{ $errors->first() }}</div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $mode==='create' ? route('admin.articles.store') : route('admin.articles.update',$article) }}" class="card p-6 grid gap-4">
    @csrf @if($mode==='edit') @method('PATCH') @endif
    <label class="font-black">Judul Postingan
        <input name="title" value="{{ old('title',$article->title) }}" class="auth-input mt-2" required>
    </label>
    <label class="font-black">Slug URL
        <input name="slug" value="{{ old('slug',$article->slug) }}" class="auth-input mt-2" placeholder="otomatis kalau dikosongkan">
        <small class="block text-[#6B8A88] mt-2 font-bold">Contoh: panduan-memilih-botol-susu-aman</small>
    </label>
    <div class="grid md:grid-cols-2 gap-4">
        <label class="font-black">Kategori
            <select name="category_id" class="auth-input mt-2" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string)old('category_id',$article->category_id)===(string)$category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="font-black">Status
            <select name="status" class="auth-input mt-2" required>
                <option value="published" @selected(old('status',$article->status ?: 'published')==='published')>Published</option>
                <option value="draft" @selected(old('status',$article->status)==='draft')>Draft</option>
            </select>
        </label>
    </div>
    <label class="font-black">Tags
        <input name="tags" value="{{ old('tags',$article->tags) }}" class="auth-input mt-2" placeholder="parenting, bayi, tips ibu">
        <small class="block text-[#6B8A88] mt-2 font-bold">Pisahkan dengan koma.</small>
    </label>
    <div class="grid md:grid-cols-[180px_1fr] gap-4 items-center">
        <div class="admin-upload-preview">
            @if($mode==='edit' && $article->image)
                <img src="{{ $article->image }}" alt="Preview artikel">
            @else
                <span>Preview<br>Gambar</span>
            @endif
        </div>
        <label class="font-black">Upload Gambar Postingan
            <input type="file" name="image_file" accept="image/png,image/jpeg,image/jpg,image/webp" class="auth-input mt-2" {{ $mode==='create' ? 'required' : '' }}>
            <small class="block text-[#6B8A88] mt-2 font-bold">Format: JPG, PNG, WEBP. Maksimal 4MB. {{ $mode==='edit' ? 'Kosongkan kalau tidak mau ganti gambar.' : '' }}</small>
        </label>
    </div>
    <label class="font-black">Content / Isi Berita
        <textarea name="content" class="auth-input mt-2 min-h-[220px]" required>{{ old('content',$article->content) }}</textarea>
    </label>
    <div class="flex flex-wrap gap-3">
        <button class="btn-pill btn-coral">Simpan Postingan</button>
        <a href="{{ route('admin.articles') }}" class="btn-pill btn-teal">Kembali</a>
    </div>
</form>
</section>
@endsection
