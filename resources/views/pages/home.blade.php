@extends('layouts.app')@section('title','SobatAnak — Mom & Baby Care')@section('content')
<section class="hero-slider relative overflow-hidden bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA]">
    <div class="absolute top-10 right-20 w-64 h-64 rounded-full bg-[#4BBFB0]/10 blur-3xl"></div>
    <div class="hero-orb hero-orb-one"></div>
    <div class="hero-orb hero-orb-two"></div>
    <div class="max-w-7xl mx-auto px-6 md:px-12 py-8 md:py-10 relative">
        @php $slides=[
            ['Lovely Kids 🌟','Semua yang Si Kecil','Butuhkan Ada di Sini!','Produk bayi berkualitas tinggi — dari pakaian hingga mainan edukatif. Belanja dengan cinta, seperti kasih ibu.','Lihat Koleksi',route('products'),'https://images.unsplash.com/photo-1514090319495-53885bfc202f'],
            ['Produk Terbaik 🍼','Kualitas Premium untuk','Buah Hati Tercinta','Dipilih dengan cermat oleh para ahli parenting. Aman, nyaman, dan menyenangkan untuk tumbuh kembang si kecil.','Belanja Sekarang',route('products'),'https://img.rocket.new/generatedImages/rocket_gen_img_1598e07f6-1765125327744.png'],
            ['Main & Menang 🎮','Main Game, Kumpulkan','Poin & Tukar Hadiah!','Nikmati mini game seru, kumpulkan poin, dan tukarkan dengan voucher belanja menarik.','Main Sekarang',route('mini-games'),'https://img.rocket.new/generatedImages/rocket_gen_img_1eb162b6f-1766561509584.png']
        ]; @endphp
        <div class="hero-slide-stage min-h-[340px] md:min-h-[390px]">
            @foreach($slides as $idx=>$s)
            <div data-slide class="hero-slide {{ $idx===0 ? 'is-active' : '' }} grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <div class="hero-copy">
                    <span class="text-coral font-black text-xs uppercase tracking-widest">— {{ $s[0] }}</span>
                    <h1 class="font-display hero-title-sm mt-3">{{ $s[1] }}<br><span class="text-teal">{{ $s[2] }}</span></h1>
                    <p class="text-base text-[#6B8A88] font-bold max-w-md mt-3">{{ $s[3] }}</p>
                    <div class="flex flex-wrap gap-3 mt-4">
                        <a class="btn-pill btn-coral" href="{{ $s[5] }}">{{ $s[4] }} →</a>
                        @if($idx === 2)
                            <a class="btn-pill border-2 border-[#4BBFB0] text-teal" href="{{ route('mini-games') }}">🎮 Main & Poin</a>
                        @endif
                    </div>
                </div>
                <div class="relative hero-media-wrap">
                    <img class="hero-img-sm w-full rounded-[1.5rem] shadow-xl object-cover" src="{{ $s[6] }}" alt="SobatAnak slide">
                </div>
            </div>
            @endforeach
        </div>
        <div class="hero-controls" style="display:none" aria-hidden="true">
            <button data-prev class="hero-nav" aria-label="Slide sebelumnya">‹</button>
            <div class="hero-dots">@foreach($slides as $idx=>$s)<button data-dot="{{ $idx }}" class="hero-dot {{ $idx===0 ? 'is-active' : '' }}" aria-label="Pindah slide {{ $idx+1 }}"></button>@endforeach</div>
            <button data-next class="hero-nav" aria-label="Slide berikutnya">›</button>
        </div>

        {{-- AI Search tepat di bawah hero controls --}}
        <div class="hero-ai-inline mt-6">
            <div class="hero-ai-inner">
                <div class="hero-ai-left">
                    <span class="sobat-ai-kicker">✦ AI SEARCH SOBATANAK</span>
                    <h2 class="hero-ai-title">Tanya AI <mark>Mom & Baby</mark></h2>
                    <p class="hero-ai-desc">Ketik pertanyaan, AI akan rekomendasikan produk & artikel SobatAnak.</p>
                </div>
                <div class="sobat-ai-right">
                    <form class="sobat-ai-search" id="sobatAiForm" autocomplete="off">
                        <span class="sobat-ai-dot"></span>
                        <input id="sobatAiInput" type="text" placeholder="Contoh: perawatan kulit bayi" aria-label="Tanya AI SobatAnak">
                        <button type="submit">Jelajahi</button>
                        <button type="button" id="sobatAiClear" class="sobat-ai-clear" aria-label="Bersihkan">×</button>
                    </form>
                    <div id="sobatAiSuggestions" class="sobat-ai-suggestions" aria-live="polite"></div>
                    <div class="sobat-ai-chips" id="sobatAiChips">
                        <button type="button">botol susu anti kolik</button>
                        <button type="button">popok newborn</button>
                        <button type="button">mainan edukatif</button>
                        <button type="button">perawatan bayi</button>
                        <button type="button">perlengkapan mpasi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>




<style>
/* Hero lebih kecil */
.hero-title-sm{font-family:'Fredoka',system-ui,sans-serif;font-size:clamp(2rem,5vw,3.6rem);line-height:1.08;font-weight:700;letter-spacing:.01em;color:#2a3d3c}
.hero-title-sm .text-teal{color:#4BBFB0}
.hero-img-sm{height:300px;object-fit:cover;transition:transform 8s ease}
.hero-slide.is-active .hero-img-sm{transform:scale(1.03)}
@media(max-width:768px){.hero-img-sm{height:220px}}

/* AI inline di dalam hero */
.hero-ai-inline{border-top:1px solid rgba(75,191,176,.2);padding-top:1.25rem}
.hero-ai-inner{display:grid;grid-template-columns:.55fr 1.45fr;gap:2rem;align-items:center;background:rgba(255,255,255,.7);border:1px solid rgba(75,191,176,.22);border-radius:1.5rem;padding:1.5rem 2rem;backdrop-filter:blur(8px)}
.hero-ai-left{position:relative;z-index:1}
.hero-ai-title{font-family:'Fredoka',system-ui,sans-serif;color:#2a3d3c;font-weight:700;font-size:clamp(1.3rem,2.8vw,2rem);line-height:1.1;letter-spacing:.01em}
.hero-ai-title mark{background:linear-gradient(180deg,transparent 55%,#ffe8a6 0);color:#2a3d3c;padding:0 .06em}
.hero-ai-desc{color:#6b8a88;font-weight:800;font-size:.9rem;line-height:1.6;margin-top:.5rem}
@media(max-width:860px){.hero-ai-inner{grid-template-columns:1fr;gap:1rem;padding:1.25rem}}
@media(max-width:560px){.hero-ai-inner{padding:1rem}.hero-ai-title{font-size:1.4rem}}

/* Komponen AI (dipindahkan dari section lama) */
.sobat-ai-kicker{display:block;color:#e8756a;font-weight:1000;letter-spacing:.18em;font-size:.68rem;margin-bottom:.75rem}
.sobat-ai-search{display:flex;align-items:center;gap:.9rem;background:#fff;border:1.5px solid rgba(47,118,214,.28);border-radius:999px;padding:.55rem .6rem .55rem 1rem;box-shadow:0 12px 30px rgba(42,61,60,.08);position:relative}
.sobat-ai-dot{width:.85rem;height:.85rem;border-radius:999px;background:#67d98c;box-shadow:0 0 0 .6rem rgba(103,217,140,.15);flex:0 0 auto}
.sobat-ai-search input{flex:1;min-width:0;border:0;outline:0;background:transparent;color:#2a3d3c;font-weight:900;font-size:1rem}
.sobat-ai-search input::placeholder{color:#99a7b5}
.sobat-ai-search button[type="submit"]{border:0;background:#2f76d6;color:#fff;font-weight:1000;border-radius:999px;padding:.75rem 1.2rem;font-size:.92rem;box-shadow:0 8px 18px rgba(47,118,214,.22);transition:.22s}
.sobat-ai-search button[type="submit"]:hover{transform:translateY(-2px)}
.sobat-ai-clear{display:none;border:0!important;background:#f4f8f8!important;color:#6b8a88!important;width:2rem;height:2rem;border-radius:999px;font-size:1.1rem;font-weight:1000;padding:0!important;box-shadow:none!important}
.sobat-ai-search.has-text .sobat-ai-clear{display:inline-grid;place-items:center}
.sobat-ai-chips{display:flex;flex-wrap:wrap;gap:.55rem;margin-top:.8rem}
.sobat-ai-chips button,.sobat-ai-suggestions button{border:1px solid rgba(75,191,176,.35);background:rgba(255,255,255,.92);color:#2da69b;border-radius:999px;padding:.55rem .85rem;font-weight:900;font-size:.85rem;transition:.2s}
.sobat-ai-chips button:hover,.sobat-ai-suggestions button:hover{background:#dff7f4;transform:translateY(-1px)}
.sobat-ai-suggestions{display:none;margin:.65rem 0 0;background:#fff;border:1px solid rgba(75,191,176,.25);border-radius:1.2rem;padding:.5rem;box-shadow:0 12px 28px rgba(42,61,60,.08)}
.sobat-ai-suggestions.is-open{display:grid;gap:.4rem}
.sobat-ai-suggestions button{width:100%;text-align:left;border-radius:.9rem;background:#fbfffe;color:#2a3d3c}
</style>

<script>
(() => {
    const form = document.getElementById('sobatAiForm');
    if (!form) return;
    const input = document.getElementById('sobatAiInput');
    const clearBtn = document.getElementById('sobatAiClear');
    const suggestions = document.getElementById('sobatAiSuggestions');
    const chips = document.getElementById('sobatAiChips');
    const placeholders = ['botol susu anti kolik','popok newborn','mainan edukatif','perawatan kulit bayi','pakaian bayi nyaman','perlengkapan mpasi','anak susah makan'];
    let placeholderIndex = 0;
    let debounce = null;

    setInterval(() => {
        if (document.activeElement === input || input.value.trim()) return;
        placeholderIndex = (placeholderIndex + 1) % placeholders.length;
        input.placeholder = 'Contoh: ' + placeholders[placeholderIndex];
    }, 2400);

    function esc(text){ return String(text || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
    function askAI(text){
        if (!text) return;
        window.location.href = '{{ route('ai-chat.page') }}?q=' + encodeURIComponent(text);
    }
    form.addEventListener('submit', e => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        askAI(text);
        input.value = '';
        form.classList.remove('has-text');
    });
    input.addEventListener('input', () => {
        const q = input.value.trim();
        form.classList.toggle('has-text', !!q);
        clearTimeout(debounce);
        if (!q) { suggestions.classList.remove('is-open'); suggestions.innerHTML=''; return; }
        debounce = setTimeout(async () => {
            try {
                const res = await fetch('{{ route('ai-chat.suggest') }}?q=' + encodeURIComponent(q), {headers:{'Accept':'application/json'}});
                const data = await res.json();
                if (!data.suggestions || !data.suggestions.length) { suggestions.classList.remove('is-open'); suggestions.innerHTML=''; return; }
                suggestions.innerHTML = data.suggestions.map(item => `<button type="button">${esc(item)}</button>`).join('');
                suggestions.classList.add('is-open');
            } catch(e) { suggestions.classList.remove('is-open'); }
        }, 140);
    });
    suggestions.addEventListener('click', e => {
        const btn = e.target.closest('button');
        if (!btn) return;
        input.value = btn.textContent.trim();
        form.classList.add('has-text');
        suggestions.classList.remove('is-open');
        input.focus();
    });
    chips.addEventListener('click', e => {
        const btn = e.target.closest('button');
        if (!btn) return;
        input.value = btn.textContent.trim();
        form.classList.add('has-text');
        askAI(input.value.trim());
        input.value = '';
        form.classList.remove('has-text');
    });
    clearBtn.addEventListener('click', () => { input.value=''; form.classList.remove('has-text'); suggestions.classList.remove('is-open'); suggestions.innerHTML=''; input.focus(); });
})();
</script>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="flex items-end justify-between mb-9">
            <div>
                <span class="uppercase tracking-widest text-[#6B8A88] font-bold text-xs">Pilihan Terbaik</span>
                <h2 class="section-title font-display">Produk <span class="text-teal italic">Unggulan</span></h2>
            </div>
            <a class="font-black border-b" href="{{ route('products') }}">Lihat Semua Produk →</a>
        </div>

        @php
            $maxSoldLanding = $products->max('sold');
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($products as $p)
                @php
                    $stock = is_null($p->stock ?? null) ? 20 : (int) $p->stock;
                    $isBestSeller = ((int) ($p->sold ?? 0)) === (int) ($maxSoldLanding ?? 0) && (int) ($maxSoldLanding ?? 0) > 0;
                    $productBadge = $isBestSeller ? 'Terlaris' : ((strtolower((string) ($p->badge ?? '')) === 'terlaris') ? null : ($p->badge ?? null));
                @endphp

                <div class="card reveal modal-card cursor-pointer {{ $stock <= 0 ? 'is-stock-empty' : '' }}"
                    data-open-product
                    data-id="{{ $p->id }}"
                    data-name="{{ $p->name }}"
                    data-category="{{ $p->category }}"
                    data-price="{{ $p->price }}"
                    data-rating="{{ $p->rating }}"
                    data-sold="{{ $p->sold }}"
                    data-image="{{ $p->image }}"
                    data-badge="{{ $productBadge }}"
                    data-stock="{{ $stock }}">
                    <div class="relative">
                        <img class="product-img w-full {{ $stock <= 0 ? 'stock-empty-img' : '' }}" src="{{ $p->image }}" alt="{{ $p->name }}">
                        @if($productBadge)
                            <span class="badge {{ $isBestSeller ? 'badge-best-seller' : '' }}">{{ $productBadge }}</span>
                        @endif
                        @if($stock <= 0)
                            <span class="landing-stock-empty">Stok Habis</span>
                        @elseif($stock <= 5)
                            <span class="landing-stock-low">Stok tinggal sedikit</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="text-xs uppercase tracking-widest text-[#6B8A88] font-black">{{ $p->category }}</p>
                        <h3 class="font-display text-lg mt-1">{{ $p->name }}</h3>
                        <p class="text-sm text-[#6B8A88]">⭐ {{ number_format((float) $p->rating, 1) }} · {{ number_format($p->sold,0,',','.') }} terjual</p>
                        <div class="flex items-center justify-between mt-4">
                            <b>Rp {{ number_format($p->price,0,',','.') }}</b>
                            @if($stock > 0)
                                <button data-buy data-product-id="{{ $p->id }}" class="btn-pill btn-coral text-xs py-2">+ Keranjang</button>
                            @else
                                <span class="landing-habis-chip">Habis</span>
                            @endif
                        </div>
                        <button type="button" class="modal-read-more mt-3">Lihat detail produk →</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
<section class="py-16 bg-[#F0FAF9]"><div class="max-w-7xl mx-auto px-6 md:px-12 grid md:grid-cols-2 gap-8 items-center"><div><span class="text-coral font-black uppercase tracking-widest text-xs">Points & Rewards</span><h2 class="section-title font-display mt-3">Main Game, Kumpulkan <span class="text-teal">Poin</span></h2><p class="text-[#6B8A88] font-bold mt-4">Poin tersimpan di browser dan bisa dipakai untuk menukar reward.</p><a class="btn-pill btn-coral mt-6" href="{{ route('mini-games') }}">Mulai Main 🎮</a></div><div class="grid gap-3">@foreach($rewards as $r)<div class="card p-5 flex justify-between items-center"><div><b>{{ $r->name }}</b><p class="text-sm text-[#6B8A88]">{{ $r->description }}</p></div><span class="font-black text-coral">{{ $r->points }} pts</span></div>@endforeach</div></div></section>
<section class="py-16 testimonial-web-section">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="testimonial-home-head simple-review-head">
            <div>
                <span class="simple-eyebrow">Ulasan Website</span>
                <h2 class="section-title font-display mt-2">Apa Kata <span class="text-teal">Mereka</span></h2>
                <p class="simple-review-sub">Komentar dengan love terbanyak tampil paling depan. Semua ulasan bisa dilihat dan difilter seperti marketplace.</p>
            </div>
            <div class="testimonial-home-actions simple-review-actions">
                @if($authUser)
                    <button type="button" class="btn-pill btn-coral simple-write-btn" onclick="document.getElementById('testimonial-form-box')?.classList.toggle('hidden')">+ Tulis Ulasan</button>
                @else
                    <a href="{{ route('login') }}" class="btn-pill btn-coral simple-write-btn">Login untuk Ulasan</a>
                @endif
                <a href="{{ route('testimonials.index', ['sort' => 'liked']) }}" class="btn-pill simple-all-btn">Lihat Semua Ulasan</a>
            </div>
        </div>

        @if(session('success'))
            <div class="testimonial-alert mb-6 p-4 rounded-xl font-bold" style="background:#D0F0ED; color:#2A3D3C;">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="testimonial-alert mb-6 p-4 rounded-xl font-bold" style="background:#FDECEA; color:#D84315;">
                <ul style="list-style-type: disc; margin-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($authUser)
            <div id="testimonial-form-box" class="testimonial-form-box hidden mb-8">
                <form method="POST" action="{{ route('testimonials.store.user') }}" class="grid gap-4">
                    @csrf
                    <div>
                        <label class="text-sm font-black text-[#2A3D3C]">Rating</label>
                        <select name="rating" class="auth-input mt-2">
                            <option value="5">★★★★★ - Sangat suka</option>
                            <option value="4">★★★★☆ - Bagus</option>
                            <option value="3">★★★☆☆ - Cukup</option>
                            <option value="2">★★☆☆☆ - Kurang</option>
                            <option value="1">★☆☆☆☆ - Perlu diperbaiki</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-black text-[#2A3D3C]">Komentar kamu</label>
                        <textarea name="message" class="auth-input min-h-[120px] mt-2" placeholder="Tulis pengalaman kamu memakai SobatAnak..." maxlength="400" required></textarea>
                    </div>
                    <button class="btn-pill btn-coral w-fit">Kirim Ulasan</button>
                </form>
            </div>
        @endif

        <div class="simple-testimonial-grid">
            @forelse($testimonials as $t)
                @php
                    $displayLikes = max((int)($t->likes_count ?? 0), (int)($t->real_likes_count ?? 0));
                    $isOwner = $authUser && (int)($t->user_id ?? 0) === (int)$authUser->id;
                    $isEdited = (bool)($t->is_edited ?? false);
                    $avatarPath = $t->user->avatar ?? null;
                    $avatarUrl = $avatarPath ? ((str_starts_with($avatarPath, 'http://') || str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, '/')) ? $avatarPath : asset($avatarPath)) : null;
                    $isAdminReview = strtolower((string)($t->user->role ?? 'user')) === 'admin';
                @endphp
                <article class="simple-testimonial-card">
                    <div class="simple-review-top">
                        <p class="simple-stars">{{ str_repeat('★', (int)($t->rating ?? 5)) }}{{ str_repeat('☆', 5 - (int)($t->rating ?? 5)) }}</p>
                        <button type="button" class="testimonial-like-btn simple-like-btn {{ in_array($t->id, $likedTestimonialIds ?? []) ? 'liked' : '' }}" data-testimonial-like="{{ $t->id }}" aria-label="Like komentar">♥ <span>{{ $displayLikes }}</span></button>
                    </div>
                    <p class="simple-review-message">“{{ $t->message }}”</p>
                    @if($isEdited)
                        <span class="testimonial-edited-badge">Edited</span>
                    @endif
                    @if($isOwner)
                        <div class="testimonial-owner-actions">
                            <button type="button" class="testimonial-mini-action" data-toggle-edit="home-testimonial-edit-{{ $t->id }}">Edit</button>
                            <form method="POST" action="{{ route('testimonials.destroy.user', $t) }}" data-cute-confirm="Hapus ulasan website ini?" data-cute-detail="Ulasan kamu akan dihapus permanen dari SobatAnak.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="testimonial-mini-action danger">Hapus</button>
                            </form>
                        </div>
                        <form id="home-testimonial-edit-{{ $t->id }}" method="POST" action="{{ route('testimonials.update', $t) }}" class="testimonial-inline-edit hidden">
                            @csrf
                            @method('PATCH')
                            <select name="rating" class="auth-input">
                                @for($rate = 5; $rate >= 1; $rate--)
                                    <option value="{{ $rate }}" @selected((int)($t->rating ?? 5) === $rate)>{{ str_repeat('★', $rate) }}{{ str_repeat('☆', 5 - $rate) }}</option>
                                @endfor
                            </select>
                            <textarea name="message" class="auth-input" maxlength="400" required>{{ $t->message }}</textarea>
                            <div class="flex gap-2 flex-wrap">
                                <button class="testimonial-save-btn">Simpan</button>
                                <button type="button" class="testimonial-cancel-btn" data-toggle-edit="home-testimonial-edit-{{ $t->id }}">Batal</button>
                            </div>
                        </form>
                    @endif
                    <div class="simple-review-footer">
                        <div class="reviewer-inline">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $t->name }}" class="review-avatar-sm">
                            @else
                                <span class="review-avatar-sm review-avatar-fallback">{{ strtoupper(mb_substr($t->name ?? 'U', 0, 1)) }}</span>
                            @endif
                            <span>
                                <b>{{ $t->name }}</b>
                                @if($isAdminReview)
                                    <em class="admin-review-badge">Admin SobatAnak</em>
                                @endif
                            </span>
                        </div>
                        <span>{{ optional($t->created_at)->format('d M Y') }}</span>
                    </div>
                </article>
            @empty
                <div class="md:col-span-3 card p-8 text-center"><h3 class="font-display text-2xl">Belum ada ulasan</h3><p class="text-[#6B8A88] mt-2">Jadilah user pertama yang memberi komentar tentang SobatAnak.</p></div>
            @endforelse
        </div>
    </div>
</section>
<section class="py-16 bg-white"><div class="max-w-7xl mx-auto px-6 md:px-12"><div class="flex justify-between mb-8"><h2 class="section-title font-display">Artikel <span class="text-teal">Parenting</span></h2><a class="font-black" href="{{ route('articles') }}">Semua Artikel →</a></div><div class="grid md:grid-cols-3 gap-5">@foreach($articles as $a)<a href="{{ route('article.show', $a->slug ?: $a->id) }}" class="card block overflow-hidden transition hover:-translate-y-1 hover:shadow-xl" aria-label="Baca artikel {{ $a->title }}"><img class="product-img w-full" src="{{ $a->image }}" alt="{{ $a->title }}"><div class="p-5"><span class="text-coral font-black text-xs uppercase">{{ $a->category_name }}</span><h3 class="font-display text-xl mt-2 text-[#243D3B]">{{ $a->title }}</h3><p class="text-[#6B8A88] mt-2">{{ $a->excerpt }}</p><span class="modal-read-more inline-flex mt-3">Baca Artikel →</span></div></a>@endforeach</div></div></section>

<div class="info-modal" data-product-modal aria-hidden="true"><div class="info-modal-backdrop" data-modal-close></div><div class="info-modal-panel product-detail-panel" role="dialog" aria-modal="true"><button class="modal-close" data-modal-close aria-label="Tutup">×</button><div class="grid lg:grid-cols-[1fr_1.05fr] gap-6 items-stretch"><div class="modal-image-wrap"><img data-modal-product-image src="" alt="Detail produk"></div><div class="modal-content"><span class="modal-kicker" data-modal-product-category></span><h2 class="font-display modal-title" data-modal-product-name></h2><div class="flex flex-wrap gap-2 my-4"><span class="modal-chip" data-modal-product-rating></span><span class="modal-chip" data-modal-product-sold></span><span class="modal-chip" data-modal-product-badge></span></div><h3 class="modal-price" data-modal-product-price></h3><p class="modal-desc" data-modal-product-desc></p><div class="modal-benefits"><div><b>✨ Kelebihan</b><p>Aman, nyaman, dan cocok untuk kebutuhan harian bayi serta anak.</p></div><div><b>🧸 Cocok Untuk</b><p>Orang tua yang ingin produk praktis, berkualitas, dan mudah digunakan.</p></div><div><b>🛍️ Catatan</b><p>Tambahkan ke cart untuk lanjut ke halaman checkout khusus akun kamu.</p></div></div><div class="flex flex-wrap gap-3 mt-6"><button data-modal-product-buy class="btn-pill btn-coral">+ Masukkan Cart</button><a href="{{ route('cart.index') }}" class="btn-pill btn-teal">Lihat Cart</a></div></div></div></div></div>
<div class="info-modal" data-article-modal aria-hidden="true"><div class="info-modal-backdrop" data-modal-close></div><div class="info-modal-panel article-detail-panel" role="dialog" aria-modal="true"><button class="modal-close" data-modal-close aria-label="Tutup">×</button><div class="modal-article-hero"><img data-modal-article-image src="" alt="Detail artikel"></div><div class="modal-content article-modal-content"><span class="modal-kicker" data-modal-article-category></span><h2 class="font-display modal-title" data-modal-article-title></h2><p class="modal-meta">📅 <span data-modal-article-date></span> · ⏱️ 3 menit baca · SobatAnak Editorial</p><p class="modal-desc" data-modal-article-excerpt></p><div class="article-pop-grid"><div><b>Ringkasan</b><p data-modal-article-summary></p></div><div><b>Yang Dibahas</b><ul><li>Tips praktis untuk orang tua.</li><li>Hal penting yang perlu diperhatikan.</li><li>Rekomendasi kebiasaan baik di rumah.</li></ul></div><div><b>Catatan SobatAnak</b><p>Gunakan artikel ini sebagai panduan awal. Sesuaikan kembali dengan kebutuhan si kecil dan kondisi keluarga.</p></div></div></div></div></div>

<style>
.testimonial-web-section{background:#F8FCFB;border-top:1px solid #D4EEEC;border-bottom:1px solid #D4EEEC;padding-top:4.25rem;padding-bottom:4.25rem}.testimonial-home-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:2rem;align-items:end;margin-bottom:1.25rem}.simple-eyebrow{display:inline-flex;color:#E8756A;font-weight:1000;text-transform:uppercase;letter-spacing:.18em;font-size:12px}.simple-review-sub{color:#6B8A88;font-weight:900;margin-top:.45rem;font-size:1.05rem;max-width:760px}.simple-review-actions{display:flex;flex-direction:column;gap:.8rem;min-width:245px}.simple-write-btn{justify-content:center;box-shadow:none}.simple-all-btn{justify-content:center;background:#fff;border:2px solid #4BBFB0;color:#2E8F84}.simple-all-btn:hover{background:#D0F0ED;transform:translateY(-2px)}.simple-testimonial-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.25rem;margin-top:1.1rem}.simple-testimonial-card{background:#fff;border:1.5px solid #D4EEEC;border-radius:28px;padding:1.7rem 1.85rem;min-height:190px;box-shadow:0 14px 38px rgba(42,61,60,.045);transition:.22s ease;display:flex;flex-direction:column;justify-content:space-between}.simple-testimonial-card:hover{transform:translateY(-4px);border-color:#BFECE6;box-shadow:0 22px 56px rgba(42,61,60,.08)}.simple-review-top{display:flex;align-items:center;justify-content:space-between;gap:1rem}.simple-stars{color:#F5A400;letter-spacing:.06em;font-weight:1000;font-size:1.06rem}.simple-like-btn{display:inline-flex;align-items:center;gap:.35rem;border:1.5px solid #FFD2CC;background:#FFF4F2;color:#E8756A;border-radius:999px;padding:.48rem .78rem;font-weight:1000;transition:.2s;line-height:1}.simple-like-btn:hover{border-color:#E8756A;background:#fff;transform:translateY(-1px)}.simple-like-btn.liked{background:#FDECEA;border-color:rgba(232,117,106,.55);color:#E8756A}.testimonial-like-btn:disabled{opacity:.65;cursor:not-allowed;transform:none}.simple-review-message{color:#6B8A88;font-weight:850;line-height:1.75;font-size:1.05rem;margin:1.35rem 0;min-height:48px}.simple-review-footer{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-top:auto}.simple-review-footer b{font-family:var(--font-display,inherit);font-size:1.08rem;color:#1F3332}.simple-review-footer span{color:#6B8A88;font-weight:900;font-size:.9rem}.testimonial-form-box{background:#fff;border:1px solid #D4EEEC;border-radius:1.5rem;padding:1.25rem;box-shadow:0 18px 42px rgba(42,61,60,.08)}.testimonial-edited-badge{display:inline-flex;width:max-content;border:1px solid #D4EEEC;background:#F0FFFC;color:#2E8F84;border-radius:999px;padding:.25rem .6rem;font-size:.72rem;font-weight:1000;text-transform:uppercase;letter-spacing:.08em;margin-top:-.35rem;margin-bottom:.65rem}.testimonial-owner-actions{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin:.15rem 0 .9rem}.testimonial-owner-actions form{display:inline}.testimonial-mini-action{border:1.5px solid #D4EEEC;background:#fff;color:#2E8F84;border-radius:999px;padding:.42rem .75rem;font-weight:1000;font-size:.78rem;transition:.18s}.testimonial-mini-action:hover{background:#D0F0ED;transform:translateY(-1px)}.testimonial-mini-action.danger{color:#E8756A;border-color:#FFD2CC;background:#FFF7F5}.testimonial-mini-action.danger:hover{background:#FDECEA}.testimonial-inline-edit{display:grid;gap:.65rem;background:#F8FEFD;border:1px dashed #BFECE6;border-radius:18px;padding:.85rem;margin:.25rem 0 .9rem}.testimonial-inline-edit.hidden{display:none}.testimonial-inline-edit textarea{min-height:86px;resize:vertical}.testimonial-save-btn,.testimonial-cancel-btn{border:0;border-radius:999px;padding:.55rem .95rem;font-weight:1000}.testimonial-save-btn{background:#4BBFB0;color:white}.testimonial-cancel-btn{background:#FDECEA;color:#E8756A}
.reviewer-inline{display:flex;align-items:center;gap:.7rem;min-width:0}.reviewer-inline>span{display:grid;gap:.15rem}.review-avatar-sm{width:42px;height:42px;border-radius:999px;object-fit:cover;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 10px 20px rgba(42,61,60,.10);border:2px solid #D4EEEC;flex:0 0 42px}.review-avatar-fallback{background:linear-gradient(135deg,#4BBFB0,#E8756A);color:#fff;font-weight:1000}.admin-review-badge{display:inline-flex;width:max-content;background:#FFF5D9;border:1px solid #F5A400;color:#9B6400;border-radius:999px;padding:.18rem .55rem;font-size:.68rem;font-weight:1000;text-transform:uppercase;letter-spacing:.08em;font-style:normal}
@media(max-width:1024px){.testimonial-home-head{grid-template-columns:1fr}.simple-review-actions{flex-direction:row;flex-wrap:wrap;min-width:0}.simple-write-btn,.simple-all-btn{width:fit-content}.simple-testimonial-grid{grid-template-columns:1fr 1fr}}@media(max-width:640px){.simple-review-actions{flex-direction:column;align-items:stretch}.simple-write-btn,.simple-all-btn{width:100%}.simple-testimonial-grid{grid-template-columns:1fr}.simple-testimonial-card{padding:1.35rem}}
</style>
<script>

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-toggle-edit]');
    if (!toggle) return;
    event.preventDefault();
    const target = document.getElementById(toggle.dataset.toggleEdit);
    if (target) target.classList.toggle('hidden');
});

document.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-testimonial-like]');
    if (!btn) return;
    event.preventDefault();
    btn.disabled = true;
    const id = btn.dataset.testimonialLike;
    try {
        const response = await fetch(`/testimonials/${id}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (!data.ok) {
            if (data.redirect) window.location.href = data.redirect;
            else alert(data.message || 'Gagal like komentar.');
            return;
        }
        btn.classList.toggle('liked', !!data.liked);
        const count = btn.querySelector('span');
        if (count) count.textContent = data.likes_count;
    } catch (error) {
        alert('Gagal like komentar. Coba lagi.');
    } finally {
        btn.disabled = false;
    }
});
</script>

@endsection