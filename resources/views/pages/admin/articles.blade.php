@extends('layouts.admin')
@section('title','Artikel — Admin SobatAnak')
@section('page-title','Artikel')
@section('admin-content')

<style>
:root{--sa-teal:#49C5B6;--sa-dark:#263D3B;--sa-muted:#6B8A88;--sa-coral:#EF7168;--sa-border:#D4EEEC}
.admin-post-hero{background:linear-gradient(180deg,#F8FFFD 0%,#FFFFFF 76%);padding:3rem 0 1.6rem;border-bottom:1px solid var(--sa-border)}.admin-kicker{color:var(--sa-coral);font-weight:1000;text-transform:uppercase;letter-spacing:.12em;font-size:.78rem}.admin-post-hero-row{display:flex;justify-content:space-between;gap:1.5rem;align-items:flex-end;flex-wrap:wrap}.section-title{font-size:clamp(2.6rem,6vw,4.8rem)!important;line-height:.95;color:var(--sa-dark);letter-spacing:-.045em}.text-teal{color:var(--sa-teal)!important}.admin-muted{color:var(--sa-muted);font-weight:900}.admin-post-hero-actions,.admin-filter-actions,.admin-toolbar-actions{display:flex;flex-wrap:wrap;gap:.75rem;align-items:center}.btn-pill{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:.85rem 1.15rem;font-weight:1000;text-decoration:none;transition:.2s ease}.btn-teal{background:var(--sa-teal);color:white!important}.btn-coral{background:var(--sa-coral);color:white!important}.btn-pill:hover{transform:translateY(-2px)}.admin-alert-success{background:#EEFFFB;border:1px solid #BFECE6;color:#118B82;border-radius:1rem;padding:1rem;font-weight:1000;margin-bottom:1rem}
.admin-post-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1rem}.admin-post-stat{background:#fff;border:1px solid var(--sa-border);border-radius:1.25rem;padding:1.05rem 1.15rem;box-shadow:0 16px 46px rgba(38,61,59,.06)}.admin-post-stat span{display:block;color:var(--sa-muted);font-weight:1000;text-transform:uppercase;letter-spacing:.08em;font-size:.75rem}.admin-post-stat strong{display:block;color:var(--sa-dark);font-size:2rem;line-height:1;margin-top:.25rem}.admin-post-filter,.admin-post-toolbar,.admin-empty-posts{background:#fff;border:1px solid var(--sa-border);border-radius:1.35rem;padding:1.1rem;box-shadow:0 16px 46px rgba(38,61,59,.06);margin-bottom:1.2rem}.admin-post-filter{display:grid;grid-template-columns:1.5fr .75fr .9fr auto;gap:.85rem;align-items:end}.admin-post-filter label{display:block;color:var(--sa-muted);font-weight:1000;margin-bottom:.35rem;font-size:.82rem}.admin-post-filter input,.admin-post-filter select{width:100%;border:1px solid var(--sa-border);border-radius:1rem;padding:.85rem 1rem;font-weight:900;outline:none;background:#F8FFFD}.admin-post-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem}.admin-post-toolbar p{color:var(--sa-dark);font-weight:900}.admin-post-toolbar small{color:var(--sa-muted);font-weight:800}.admin-select-all{font-weight:1000;color:var(--sa-dark)}
.admin-post-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.admin-post-card{position:relative;overflow:hidden;background:#fff;border:1px solid var(--sa-border);border-radius:1.35rem;box-shadow:0 16px 46px rgba(38,61,59,.06)}.admin-post-check{position:absolute;top:.8rem;left:.8rem;z-index:2;background:white;border-radius:999px;padding:.35rem;box-shadow:0 8px 20px rgba(0,0,0,.08)}.admin-post-image-wrap{position:relative;height:150px;background:#F8FFFD}.admin-post-image{width:100%;height:100%;object-fit:cover}.admin-post-status{position:absolute;right:.75rem;top:.75rem;border-radius:999px;padding:.4rem .7rem;font-size:.68rem;font-weight:1000;background:#EDF4F3;color:var(--sa-muted)}.admin-post-status.is-published{background:#EEFFFB;color:#0BA699}.admin-post-status.is-draft{background:#FFF4E8;color:#D88312}.admin-post-body{padding:1rem}.admin-post-meta,.admin-post-foot{display:flex;align-items:center;justify-content:space-between;gap:.75rem;color:var(--sa-muted);font-weight:900;font-size:.78rem}.admin-post-body h3{font-size:1.1rem;color:var(--sa-dark);font-weight:1000;line-height:1.25;margin:.55rem 0}.admin-post-body p{color:var(--sa-muted);font-weight:800;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.admin-post-tags{margin:.8rem 0;color:#10A99D;font-weight:1000;font-size:.82rem}.admin-post-actions{display:flex;gap:.45rem}.admin-mini-btn{border:0;border-radius:.85rem;padding:.5rem .75rem;font-weight:1000;cursor:pointer}.admin-mini-btn.edit{background:#EEFFFB;color:#0BA699}.admin-mini-btn.delete{background:#FFF1F0;color:#EF7168}.admin-empty-posts{grid-column:1/-1;text-align:center;padding:2rem}.admin-empty-posts h3{color:var(--sa-dark);font-weight:1000;font-size:1.4rem}.admin-empty-posts p{color:var(--sa-muted);font-weight:900}
@media(max-width:1000px){.admin-post-filter{grid-template-columns:1fr 1fr}.admin-post-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.admin-post-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.admin-post-filter,.admin-post-grid,.admin-post-stats{grid-template-columns:1fr}.admin-post-toolbar{align-items:flex-start;flex-direction:column}.section-title{font-size:2.45rem!important}}
</style>
<section class="admin-post-hero">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <span class="admin-kicker">Admin CRUD</span>
        <div class="admin-post-hero-row">
            <div>
                <h1 class="font-display section-title mt-3">Kelola <span class="text-teal">Artikel</span></h1>
                <p class="admin-muted mt-2">Tambah, edit, publish/draft, cari, dan hapus artikel yang sudah tidak terpakai.</p>
            </div>
            <div class="admin-post-hero-actions">
                <a href="{{ route('admin.articles.create') }}" class="btn-pill btn-coral admin-add-post-btn">+ Tambah Artikel Baru</a>
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
