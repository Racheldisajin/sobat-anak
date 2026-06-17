@extends('layouts.app')
@section('title','CRUD Postingan Berita — SobatAnak')
@section('content')
<section class="admin-post-hero">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <span class="admin-kicker">Admin CRUD</span>
        <div class="admin-post-hero-row">
            <div>
                <h1 class="font-display section-title mt-3">Kelola <span class="text-teal">Postingan Berita</span></h1>
                <p class="admin-muted mt-2">Tambah, edit, publish/draft, cari, dan hapus artikel yang sudah tidak terpakai.</p>
            </div>
            <div class="admin-post-hero-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn-pill admin-back-dashboard-btn">← Kembali ke Admin Dashboard</a>
                <a href="{{ route('admin.articles.create') }}" class="btn-pill btn-coral admin-add-post-btn">+ Buat Artikel Baru</a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-10 admin-post-section">
    @if(session('success'))
        <div class="admin-alert-success">{{ session('success') }}</div>
    @endif

    <div class="admin-post-stats">
        <div class="admin-post-stat"><span>Total</span><strong>{{ $stats['total'] ?? $articles->count() }}</strong></div>
        <div class="admin-post-stat"><span>Published</span><strong>{{ $stats['published'] ?? 0 }}</strong></div>
        <div class="admin-post-stat"><span>Draft</span><strong>{{ $stats['draft'] ?? 0 }}</strong></div>
        <div class="admin-post-stat"><span>Total View</span><strong>{{ number_format($stats['views'] ?? 0,0,',','.') }}</strong></div>
    </div>

    <form method="GET" action="{{ route('admin.articles') }}" class="admin-post-filter">
        <div class="admin-filter-main">
            <label>Search Postingan</label>
            <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Cari judul, slug, kategori, tags, status, atau isi artikel...">
        </div>
        <div class="admin-filter-small">
            <label>Status</label>
            <select name="status">
                <option value="all" @selected(($status ?? 'all') === 'all')>Semua</option>
                <option value="published" @selected(($status ?? 'all') === 'published')>Published</option>
                <option value="draft" @selected(($status ?? 'all') === 'draft')>Draft</option>
            </select>
        </div>
        <div class="admin-filter-small">
            <label>Kategori</label>
            <select name="category_id">
                <option value="all" @selected(($categoryId ?? 'all') === 'all')>Semua</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string)($categoryId ?? 'all') === (string)$category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-filter-actions">
            <button class="btn-pill btn-teal">Cari</button>
            <a href="{{ route('admin.articles') }}" class="btn-pill bg-white border border-[#D4EEEC]">Reset</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.articles.bulk-destroy') }}" onsubmit="return confirm('Hapus semua postingan yang dipilih?')" class="admin-post-list-form">
        @csrf
        @method('DELETE')
        <div class="admin-post-toolbar">
            <div>
                <p><b>{{ $articles->count() }}</b> postingan ditemukan</p>
                <small>Pilih artikel yang sudah tidak terpakai, lalu hapus sekaligus.</small>
            </div>
            <div class="admin-toolbar-actions">
                <label class="admin-select-all"><input type="checkbox" id="checkAllPosts"> Pilih semua</label>
                <button type="submit" class="btn-pill btn-coral">Hapus Terpilih</button>
            </div>
        </div>

        <div class="admin-post-grid">
            @forelse($articles as $article)
                @php
                    $image = $article->image ?: asset('images/logo-cropped.png');
                    $statusClass = $article->status === 'published' ? 'is-published' : 'is-draft';
                @endphp
                <article class="admin-post-card {{ $article->status === 'draft' ? 'is-draft-card' : '' }}">
                    <label class="admin-post-check" title="Pilih artikel"><input type="checkbox" name="article_ids[]" value="{{ $article->id }}"></label>
                    <div class="admin-post-image-wrap">
                        <img src="{{ $image }}" class="admin-post-image" alt="{{ $article->title }}" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
                        <span class="admin-post-status {{ $statusClass }}">{{ strtoupper($article->status ?: 'DRAFT') }}</span>
                    </div>
                    <div class="admin-post-body">
                        <div class="admin-post-meta">
                            <span>{{ $article->category_name }}</span>
                            <span>{{ number_format($article->counter ?? 0,0,',','.') }} view</span>
                        </div>
                        <h3>{{ $article->title ?: 'Tanpa Judul' }}</h3>
                        <p>{{ $article->excerpt ?: 'Belum ada isi artikel.' }}</p>
                        <div class="admin-post-tags">{{ $article->tags ? '#'.str_replace(',', ' #', $article->tags) : 'Belum ada tag' }}</div>
                        <div class="admin-post-foot">
                            <small>Update: {{ optional($article->updated_at)->format('d M Y H:i') ?: '-' }}</small>
                            <div class="admin-post-actions">
                                <a href="{{ route('admin.articles.edit',$article) }}" class="admin-mini-btn edit">Edit</a>
                                <button type="button" data-delete-url="{{ route('admin.articles.destroy',$article) }}" class="admin-mini-btn delete js-delete-post">Hapus</button>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="admin-empty-posts">
                    <h3>Belum ada postingan yang cocok.</h3>
                    <p>Coba reset filter atau buat artikel baru dari tombol di atas.</p>
                    <a href="{{ route('admin.articles.create') }}" class="btn-pill btn-coral mt-4">+ Buat Artikel</a>
                </div>
            @endforelse
        </div>
    </form>

    <form id="singleDeletePostForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const checkAll = document.getElementById('checkAllPosts');
    if(checkAll){
        checkAll.addEventListener('change', function(){
            document.querySelectorAll('input[name="article_ids[]"]').forEach(cb => cb.checked = checkAll.checked);
        });
    }

    const deleteForm = document.getElementById('singleDeletePostForm');
    document.querySelectorAll('.js-delete-post').forEach(button => {
        button.addEventListener('click', function(){
            const url = this.dataset.deleteUrl;
            if(!url || !deleteForm) return;
            if(confirm('Hapus artikel ini?')){
                deleteForm.action = url;
                deleteForm.submit();
            }
        });
    });
});
</script>
@endsection
