@extends('layouts.app')
@section('title','Artikel — SobatAnak')
@section('content')
@php
    $articles = collect($articles ?? []);

    $articleCategories = collect($categories ?? [])
        ->map(function ($cat) {
            return (object) [
                'name' => $cat->name ?? $cat->category_name ?? $cat->title ?? 'Artikel',
                'slug' => $cat->slug ?? \Illuminate\Support\Str::slug($cat->name ?? $cat->category_name ?? $cat->title ?? 'artikel'),
            ];
        })
        ->filter(fn ($cat) => filled($cat->name))
        ->values();

    if ($articleCategories->isEmpty()) {
        $articleCategories = $articles
            ->map(function ($article) {
                $name = $article->category->name
                    ?? $article->category_name
                    ?? $article->category
                    ?? 'Artikel';

                return (object) [
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                ];
            })
            ->filter(fn ($cat) => filled($cat->name))
            ->unique('slug')
            ->values();
    }
@endphp

<section class="bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-16">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Artikel</span>
        <h1 class="font-display hero-title mt-3">Tips Parenting & <span class="text-teal">Mom Care</span></h1>
    </div>
</section>

<section class="article-tools-sam">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <label class="article-search-box">
            <span>🔎</span>
            <input data-article-search placeholder="Cari artikel parenting, MPASI, perawatan..." autocomplete="off">
        </label>

        <div class="article-cat-row">
            <button type="button" data-filter-article-cat="Semua" class="article-cat active">Semua</button>
            @foreach($articleCategories as $cat)
                <button type="button" data-filter-article-cat="{{ $cat->slug }}" class="article-cat">{{ $cat->name }}</button>
            @endforeach
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-10">
    <div class="article-result-head">
        <div>
            <span>Artikel tersedia</span>
            <b data-article-count>{{ $articles->count() }}</b>
        </div>
    </div>

    <div class="article-grid-sam">
        @foreach($articles as $a)
            @php
                $catName = $a->category->name
                    ?? $a->category_name
                    ?? $a->category
                    ?? 'Artikel';

                $catSlug = $a->category->slug
                    ?? \Illuminate\Support\Str::slug($catName);

                $articleSlug = $a->slug ?? $a->id;

                $searchText = trim(
                    ($a->title ?? '') . ' ' .
                    ($catName ?? '') . ' ' .
                    ($a->excerpt ?? '') . ' ' .
                    strip_tags((string) ($a->content ?? $a->body ?? ''))
                );
            @endphp
            <a href="{{ route('article.show', $articleSlug) }}"
               class="card article-card-sam block overflow-hidden hover:-translate-y-1 transition"
               data-article-card
               data-title="{{ e($a->title ?? '') }}"
               data-category="{{ $catSlug }}"
               data-search="{{ e($searchText) }}">
                <img class="product-img w-full" src="{{ $a->image ?? asset('images/article-placeholder.jpg') }}" alt="{{ $a->title ?? 'Artikel SobatAnak' }}">
                <div class="p-5">
                    <span class="text-coral font-black text-xs uppercase">{{ $catName }}</span>
                    <h3 class="font-display text-xl mt-2">{{ $a->title }}</h3>
                    <p class="text-[#6B8A88] mt-2">{{ $a->excerpt }}</p>
                    <span class="btn-pill btn-teal mt-4 text-sm inline-flex">Baca Artikel</span>
                </div>
            </a>
        @endforeach
    </div>

    <div id="article-empty" class="hidden text-center py-20">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="font-display text-2xl text-[#6B8A88]">Artikel tidak ditemukan</h3>
        <p class="text-[#6B8A88] mt-2">Coba kata kunci atau kategori lain.</p>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.querySelector('[data-article-search]');
    const catBtns = document.querySelectorAll('[data-filter-article-cat]');
    const cards = document.querySelectorAll('[data-article-card]');
    const countEl = document.querySelector('[data-article-count]');
    const emptyEl = document.getElementById('article-empty');
    let activeCat = 'Semua';

    function normalize(text) {
        return (text || '').toString().toLowerCase().trim();
    }

    function filterArticles() {
        const q = normalize(search ? search.value : '');
        let visible = 0;

        cards.forEach(card => {
            const cardCategory = card.dataset.category || '';
            const cardSearch = normalize(card.dataset.search);
            const matchCat = activeCat === 'Semua' || cardCategory === activeCat;
            const matchQ = !q || cardSearch.includes(q);
            const show = matchCat && matchQ;

            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (countEl) countEl.textContent = visible;
        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
    }

    catBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            catBtns.forEach(item => item.classList.remove('active'));
            btn.classList.add('active');
            activeCat = btn.dataset.filterArticleCat || 'Semua';
            filterArticles();
        });
    });

    if (search) search.addEventListener('input', filterArticles);
    filterArticles();
});
</script>

<style>
.article-tools-sam{position:sticky;top:76px;z-index:40;background:rgba(255,255,255,.94);backdrop-filter:blur(16px);border-top:1px solid #D4EEEC;border-bottom:1px solid #D4EEEC;padding:14px 0}.article-search-box{display:flex;align-items:center;gap:12px;background:#F8FEFD;border:1px solid #D4EEEC;border-radius:999px;padding:0 18px;box-shadow:0 10px 34px rgba(75,191,176,.06)}.article-search-box input{width:100%;height:52px;background:transparent;outline:0;font-weight:900;color:#2A3D3C}.article-search-box input::placeholder{color:#9AA7B3}.article-cat-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}.article-cat{border:1px solid #D4EEEC;background:#fff;color:#6B8A88;border-radius:999px;padding:10px 16px;font-weight:1000;transition:.2s}.article-cat.active,.article-cat:hover{background:#4BBFB0;color:#fff;border-color:#4BBFB0;box-shadow:0 12px 24px rgba(75,191,176,.18)}.article-result-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}.article-result-head div{display:inline-flex;align-items:center;gap:10px;color:#6B8A88;font-weight:900}.article-result-head b{background:#EEFFFB;color:#2A3D3C;border:1px solid #BFECE6;border-radius:999px;padding:8px 13px}.article-grid-sam{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:28px}.article-card-sam{min-height:100%;border:1px solid #D4EEEC;box-shadow:0 18px 55px rgba(42,61,60,.08)}.article-card-sam img{height:260px;object-fit:cover}.article-card-sam h3{color:#2A3D3C}.article-card-sam p{font-weight:800;line-height:1.7}@media(max-width:1000px){.article-grid-sam{grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}}@media(max-width:640px){.article-tools-sam{top:70px}.article-grid-sam{grid-template-columns:1fr}.article-card-sam img{height:230px}}
</style>
@endsection
