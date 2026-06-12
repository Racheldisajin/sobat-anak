@extends('layouts.app')
@section('title','CRUD Postingan Berita — SobatAnak')
@section('content')
<section class="bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-12">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Admin CRUD</span>
        <h1 class="font-display section-title mt-3">Postingan <span class="text-teal">Berita</span></h1>
        <p class="text-[#6B8A88] font-bold mt-2">Data ini sudah memakai tabel <b>posts</b>, <b>post_categories</b>, dan <b>post_tags</b>.</p>
        <a href="{{ route('admin.articles.create') }}" class="btn-pill btn-coral mt-5">+ Input Postingan Baru</a>
    </div>
</section>
<section class="max-w-7xl mx-auto px-6 md:px-12 py-10">
    @if(session('success'))<div class="card p-4 mb-5 text-teal font-black">{{ session('success') }}</div>@endif
    <form method="GET" action="{{ route('admin.articles') }}" class="admin-search-card mb-6">
        <div>
            <label class="font-black text-sm uppercase tracking-widest text-coral">Search Postingan</label>
            <input type="search" name="search" value="{{ $search ?? '' }}" class="auth-input mt-2" placeholder="Cari judul, kategori, tags, status, atau isi berita...">
        </div>
        <div class="flex gap-2 items-end">
            <button class="btn-pill btn-teal">Cari</button>
            <a href="{{ route('admin.articles') }}" class="btn-pill bg-white border border-[#D4EEEC]">Reset</a>
        </div>
    </form>
    <div class="card p-5 mb-5 flex items-center justify-between">
        <p class="text-[#6B8A88] font-black">{{ $articles->count() }} postingan ditemukan</p>
        <span class="text-xs uppercase tracking-widest text-coral font-black">posts table</span>
    </div>
    <div class="grid md:grid-cols-2 gap-5">
        @forelse($articles as $article)
        <div class="card p-5 admin-article-card">
            <div class="flex gap-4">
                <img src="{{ $article->image }}" class="admin-thumb-lg" alt="{{ $article->title }}">
                <div class="flex-1">
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span class="text-coral font-black uppercase text-xs">{{ $article->category_name }}</span>
                        <span class="admin-status-badge {{ $article->status === 'published' ? 'is-published' : 'is-draft' }}">{{ strtoupper($article->status) }}</span>
                    </div>
                    <h3 class="font-display text-2xl">{{ $article->title }}</h3>
                    <p class="text-[#6B8A88] font-bold mt-2">{{ $article->excerpt }}</p>
                    <p class="text-[#6B8A88] text-sm mt-2 font-bold">Slug: {{ $article->slug }} · View: {{ $article->counter }} · Tags: {{ $article->tags ?: '-' }}</p>
                    <div class="flex gap-2 mt-4">
                        <a href="{{ route('admin.articles.edit',$article) }}" class="btn-pill btn-teal text-xs py-2">Edit</a>
                        <form method="POST" action="{{ route('admin.articles.destroy',$article) }}" onsubmit="return confirm('Hapus postingan ini?')">@csrf @method('DELETE')<button class="btn-pill btn-coral text-xs py-2">Hapus</button></form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card p-8 text-[#6B8A88] font-black md:col-span-2">Belum ada postingan yang cocok dengan pencarian.</div>
        @endforelse
    </div>
</section>
@endsection
