@extends('layouts.app')
@section('title','Produk — SobatAnak')
@section('content')

<section class="shop-hero-sam">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <p class="shop-kicker">Katalog Produk</p>
        <h1>Produk Pilihan <span>Mom & Baby Care</span></h1>
        <p class="shop-sub">Temukan perlengkapan bayi, anak, dan perawatan keluarga dengan tampilan katalog yang lebih bersih.</p>
    </div>
</section>

<section class="shop-tools-sam">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="shop-search-row">
            <label class="shop-search-box">
                <span>🔎</span>
                <input data-product-search placeholder="Cari produk bayi, anak, perawatan..." autocomplete="off">
            </label>
            <select data-product-sort>
                <option>Terlaris</option>
                <option>Terbaru</option>
                <option>Harga Terendah</option>
                <option>Harga Tertinggi</option>
                <option>Rating Tertinggi</option>
            </select>
        </div>
        <div class="shop-cat-row">
            <button data-filter-cat="Semua" class="shop-cat active">Semua</button>
            @foreach($categories as $cat)
                <button data-filter-cat="{{ $cat }}" class="shop-cat">{{ $cat }}</button>
            @endforeach
        </div>
    </div>
</section>

@php $maxSoldProduct = $products->max('sold'); @endphp
<section class="max-w-7xl mx-auto px-6 md:px-12 py-10">
    <div class="shop-result-head">
        <div>
            <span>Produk tersedia</span>
            <b data-product-count>{{ $products->count() }}</b>
        </div>
    </div>

    <div class="sam-product-grid">
        @foreach($products as $p)
            @php
                $stock = (int) ($p->stock ?? 0);
                $isBestSeller = ((int) ($p->sold ?? 0)) === (int) ($maxSoldProduct ?? 0) && (int) ($maxSoldProduct ?? 0) > 0;
                $productBadge = $isBestSeller ? 'Terlaris' : ($p->badge ?? null);
            @endphp
            <a href="{{ route('product.show', $p->id) }}"
               id="product-{{ $p->id }}"
               class="sam-product-card {{ $stock <= 0 ? 'is-sold' : '' }}"
               data-product-card
               data-name="{{ $p->name }}"
               data-category="{{ $p->category }}"
               data-price="{{ $p->price }}"
               data-rating="{{ $p->rating }}"
               data-sold="{{ $p->sold }}"
               data-id="{{ $p->id }}">
                <div class="sam-product-img-wrap">
                    <img src="{{ $p->image }}" alt="{{ $p->name }}" class="sam-product-img">
                    @if($productBadge)
                        <span class="sam-product-badge">{{ $productBadge }}</span>
                    @endif
                    @if($stock <= 0)
                        <div class="sam-sold-mask">Stok Habis</div>
                    @else
                        <span class="sam-view-btn">Lihat Detail</span>
                    @endif
                </div>
                <div class="sam-product-info">
                    <p>{{ $p->category }}</p>
                    <h3>{{ $p->name }}</h3>
                    <div class="sam-mini-meta"><span>⭐ {{ number_format($p->rating,1) }}</span><span>{{ number_format($p->sold,0,',','.') }}+ terjual</span></div>
                    <b>Rp {{ number_format($p->price,0,',','.') }}</b>
                </div>
            </a>
        @endforeach
    </div>

    <div id="prod-empty" class="hidden text-center py-20">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="font-display text-2xl text-[#6B8A88]">Produk tidak ditemukan</h3>
        <p class="text-[#6B8A88] mt-2">Coba kata kunci atau kategori lain</p>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.querySelector('[data-product-search]');
    const sort = document.querySelector('[data-product-sort]');
    const catBtns = document.querySelectorAll('[data-filter-cat]');
    const cards = document.querySelectorAll('[data-product-card]');
    const countEl = document.querySelector('[data-product-count]');
    const emptyEl = document.getElementById('prod-empty');
    let activeCat = 'Semua';

    function filterProducts() {
        const q = (search?.value || '').toLowerCase().trim();
        let visible = 0;
        cards.forEach(c => {
            const matchCat = activeCat === 'Semua' || c.dataset.category === activeCat;
            const matchQ = !q || c.dataset.name.toLowerCase().includes(q) || c.dataset.category.toLowerCase().includes(q);
            const show = matchCat && matchQ;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) countEl.textContent = visible;
        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
        if (sort) {
            const vis = [...cards].filter(c => c.style.display !== 'none');
            vis.sort((a, b) => {
                const s = sort.value;
                if (s === 'Harga Terendah') return +a.dataset.price - +b.dataset.price;
                if (s === 'Harga Tertinggi') return +b.dataset.price - +a.dataset.price;
                if (s === 'Rating Tertinggi') return +b.dataset.rating - +a.dataset.rating;
                if (s === 'Terbaru') return +b.dataset.id - +a.dataset.id;
                return +b.dataset.sold - +a.dataset.sold;
            });
            vis.forEach(c => c.parentNode.appendChild(c));
        }
    }
    catBtns.forEach(btn => btn.addEventListener('click', () => { catBtns.forEach(b => b.classList.remove('active')); btn.classList.add('active'); activeCat = btn.dataset.filterCat; filterProducts(); }));
    search?.addEventListener('input', filterProducts);
    sort?.addEventListener('change', filterProducts);
    filterProducts();
});
</script>

<style>
.shop-hero-sam{padding:4.5rem 0;background:radial-gradient(circle at 12% 18%,rgba(75,191,176,.18),transparent 26rem),radial-gradient(circle at 90% 62%,rgba(232,117,106,.16),transparent 24rem),linear-gradient(135deg,#F8FEFD 0%,#fff 45%,#FFF4EE 100%);border-bottom:1px solid #D4EEEC}.shop-kicker{color:#E8756A;font-weight:1000;text-transform:uppercase;letter-spacing:.18em;font-size:12px}.shop-hero-sam h1{font-family:var(--font-display,inherit);font-size:clamp(3rem,6vw,5.8rem);font-weight:1000;line-height:.96;letter-spacing:-.06em;color:#2A3D3C;margin-top:12px}.shop-hero-sam h1 span{display:block;color:#4BBFB0}.shop-sub{margin-top:16px;color:#6B8A88;font-weight:850;max-width:660px;font-size:1.05rem}.shop-tools-sam{position:sticky;top:76px;z-index:40;background:rgba(255,255,255,.94);backdrop-filter:blur(16px);border-bottom:1px solid #D4EEEC;padding:14px 0}.shop-search-row{display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:12px}.shop-search-box{display:flex;align-items:center;gap:10px;background:#F8FEFD;border:1px solid #D4EEEC;border-radius:999px;padding:0 18px}.shop-search-box input{width:100%;height:48px;background:transparent;outline:0;font-weight:900;color:#2A3D3C}.shop-tools-sam select{border:1px solid #D4EEEC;border-radius:999px;padding:0 18px;font-weight:1000;color:#2A3D3C;background:#fff}.shop-cat-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}.shop-cat{border:1px solid #D4EEEC;background:#fff;color:#6B8A88;border-radius:999px;padding:10px 14px;font-weight:1000;transition:.2s}.shop-cat.active,.shop-cat:hover{background:#4BBFB0;color:#fff;border-color:#4BBFB0}.shop-result-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}.shop-result-head div{display:inline-flex;align-items:center;gap:10px;color:#6B8A88;font-weight:900}.shop-result-head b{background:#EEFFFB;color:#2A3D3C;border:1px solid #BFECE6;border-radius:999px;padding:8px 13px}
.sam-product-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:28px;align-items:stretch}.sam-product-card{display:flex;flex-direction:column;text-decoration:none;color:#2A3D3C;background:linear-gradient(180deg,#fff 0%,#F8FEFD 100%);border:1px solid #D4EEEC;border-radius:34px;padding:14px;box-shadow:0 18px 55px rgba(42,61,60,.08);transition:.28s ease;position:relative;overflow:hidden;min-height:100%}.sam-product-card:after{content:'🧸';position:absolute;right:18px;top:14px;font-size:24px;opacity:.12;transform:rotate(10deg);pointer-events:none}.sam-product-card:nth-child(3n+2):after{content:'✨'}.sam-product-card:nth-child(3n+3):after{content:'🌈'}.sam-product-card:hover{transform:translateY(-8px);box-shadow:0 30px 85px rgba(42,61,60,.14);border-color:#BFECE6}.sam-product-img-wrap{position:relative;aspect-ratio:1/1;border-radius:26px;overflow:hidden;background:#F8FEFD;border:1px solid rgba(212,238,236,.95);box-shadow:inset 0 0 0 7px rgba(255,255,255,.72);transition:.28s}.sam-product-card:hover .sam-product-img-wrap{box-shadow:inset 0 0 0 7px rgba(255,255,255,.72),0 14px 32px rgba(75,191,176,.12)}.sam-product-img{width:100%;height:100%;object-fit:cover;display:block;transition:.35s}.sam-product-card:hover .sam-product-img{transform:scale(1.045)}.sam-product-badge{position:absolute;top:16px;left:16px;background:#fff;border:1px solid #D4EEEC;border-radius:999px;padding:9px 15px;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.15em;color:#2A3D3C;box-shadow:0 12px 24px rgba(42,61,60,.08)}.sam-view-btn{position:absolute;left:26px;right:26px;bottom:24px;border:0;border-radius:999px;background:linear-gradient(135deg,#4BBFB0,#2fa99c);color:#fff;min-height:54px;padding:0 20px;font-size:.95rem;font-weight:1000;text-transform:uppercase;letter-spacing:.08em;box-shadow:0 16px 34px rgba(75,191,176,.28);opacity:0;transform:translateY(12px);transition:.22s;display:flex;align-items:center;justify-content:center;text-align:center;white-space:nowrap;line-height:1}.sam-product-card:hover .sam-view-btn{opacity:1;transform:translateY(0)}.sam-view-btn:hover{background:linear-gradient(135deg,#E8756A,#ff9b88);box-shadow:0 16px 34px rgba(232,117,106,.28)}.sam-product-info{display:flex;flex-direction:column;text-align:center;padding:18px 12px 10px;flex:1}.sam-product-info p{align-self:center;color:#E8756A;background:#FFF2F0;border:1px solid #FFD7D1;border-radius:999px;padding:7px 12px;font-weight:1000;text-transform:uppercase;letter-spacing:.12em;font-size:10px;margin-bottom:10px}.sam-product-info h3{font-size:1.13rem;line-height:1.35;font-weight:1000;min-height:3.05em;color:#242B2B;margin:0}.sam-mini-meta{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;color:#6B8A88;font-size:12px;font-weight:900;margin-top:12px}.sam-mini-meta span{background:#EEFFFB;border:1px solid #D4EEEC;border-radius:999px;padding:6px 9px}.sam-product-info b{display:block;margin-top:auto;padding-top:14px;font-size:1.34rem;color:#1D2F2E}.sam-sold-mask{position:absolute;inset:0;background:rgba(255,255,255,.72);display:flex;align-items:center;justify-content:center;font-weight:1000;color:#E8756A;font-size:1.2rem}.is-sold .sam-product-img{filter:grayscale(.8);opacity:.55}
.sam-product-card{isolation:isolate}.sam-product-card:before{content:'';position:absolute;inset:10px;border-radius:28px;border:1px dashed rgba(75,191,176,.18);pointer-events:none;z-index:0}.sam-product-img-wrap,.sam-product-info{position:relative;z-index:1}.sam-product-info h3{font-family:var(--font-display,inherit);letter-spacing:-.02em}.sam-product-info b{font-family:var(--font-display,inherit);letter-spacing:-.02em}.sam-mini-meta span:first-child{background:#FFF8DB;border-color:#F7D977;color:#4B5A5A}.sam-mini-meta span:last-child{background:#EEFFFB;border-color:#BFECE6;color:#5D7977}.sam-product-info p{box-shadow:0 8px 20px rgba(232,117,106,.08)}
@media(max-width:1000px){.sam-product-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}.shop-search-row{grid-template-columns:1fr}}@media(max-width:640px){.shop-tools-sam{top:70px}.sam-product-grid{grid-template-columns:1fr}.shop-hero-sam{padding:3rem 0}.shop-hero-sam h1{font-size:3.2rem}.sam-view-btn{opacity:1;transform:none}}
</style>
@endsection
