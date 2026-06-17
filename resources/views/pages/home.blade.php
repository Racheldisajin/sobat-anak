@extends('layouts.app')@section('title','SobatAnak — Mom & Baby Care')@section('content')
<section class="hero-slider relative overflow-hidden bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA]">
    <div class="absolute top-10 right-20 w-64 h-64 rounded-full bg-[#4BBFB0]/10 blur-3xl"></div>
    <div class="hero-orb hero-orb-one"></div>
    <div class="hero-orb hero-orb-two"></div>
    <div class="max-w-7xl mx-auto px-6 md:px-12 py-12 md:py-16 relative">
        @php $slides=[
            ['Lovely Kids 🌟','Semua yang Si Kecil','Butuhkan Ada di Sini!','Produk bayi berkualitas tinggi — dari pakaian hingga mainan edukatif. Belanja dengan cinta, seperti kasih ibu.','Lihat Koleksi',route('products'),'https://images.unsplash.com/photo-1514090319495-53885bfc202f'],
            ['Produk Terbaik 🍼','Kualitas Premium untuk','Buah Hati Tercinta','Dipilih dengan cermat oleh para ahli parenting. Aman, nyaman, dan menyenangkan untuk tumbuh kembang si kecil.','Belanja Sekarang',route('products'),'https://img.rocket.new/generatedImages/rocket_gen_img_1598e07f6-1765125327744.png'],
            ['Main & Menang 🎮','Main Game, Kumpulkan','Poin & Tukar Hadiah!','Nikmati mini game seru, kumpulkan poin, dan tukarkan dengan voucher belanja menarik.','Main Sekarang',route('mini-games'),'https://img.rocket.new/generatedImages/rocket_gen_img_1eb162b6f-1766561509584.png']
        ]; @endphp
        <div class="hero-slide-stage min-h-[480px] md:min-h-[545px]">
            @foreach($slides as $idx=>$s)
            <div data-slide class="hero-slide {{ $idx===0 ? 'is-active' : '' }} grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div class="hero-copy">
                    <span class="text-coral font-black text-sm uppercase tracking-widest">— {{ $s[0] }}</span>
                    <h1 class="font-display hero-title mt-4">{{ $s[1] }}<br><span class="text-teal">{{ $s[2] }}</span></h1>
                    <p class="text-lg text-[#6B8A88] font-bold max-w-md mt-5">{{ $s[3] }}</p>
                    <div class="flex flex-wrap gap-3 mt-6"><a class="btn-pill btn-coral" href="{{ $s[5] }}">{{ $s[4] }} →</a><a class="btn-pill border-2 border-[#4BBFB0] text-teal" href="{{ route('mini-games') }}">🎮 Main & Poin</a></div>
                    <div class="flex gap-6 mt-6 font-black"><div>2.400+<br><small class="text-[#6B8A88]">Produk</small></div><div>50K+<br><small class="text-[#6B8A88]">Keluarga</small></div><div>⭐ 4.9<br><small class="text-[#6B8A88]">Rating</small></div></div>
                </div>
                <div class="relative hero-media-wrap">
                    <img class="hero-img w-full rounded-[2rem] shadow-2xl object-cover" src="{{ $s[6] }}" alt="SobatAnak slide">
                    <div class="float absolute bottom-5 left-5 bg-white/90 rounded-2xl px-4 py-3 shadow-xl font-black">🏆 Dipercaya 50.000+ Keluarga</div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="hero-controls">
            <button data-prev class="hero-nav" aria-label="Slide sebelumnya">‹</button>
            <div class="hero-dots">@foreach($slides as $idx=>$s)<button data-dot="{{ $idx }}" class="hero-dot {{ $idx===0 ? 'is-active' : '' }}" aria-label="Pindah slide {{ $idx+1 }}"></button>@endforeach</div>
            <button data-next class="hero-nav" aria-label="Slide berikutnya">›</button>
        </div>
    </div>
</section>

<section class="sobat-ai-chat-section" id="ai-search-sobatanak">
    <div class="sobat-ai-card">
        <div class="sobat-ai-left">
            <span class="sobat-ai-kicker">✦ AI SEARCH SOBATANAK</span>
            <h2 class="sobat-ai-title">Tanya AI <mark>Mom & Baby</mark></h2>
            <p class="sobat-ai-desc">Ketik pertanyaan, lalu kamu akan masuk ke halaman chat AI seperti Gemini. Jika cocok, AI akan merekomendasikan produk dan artikel SobatAnak.</p>
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
            <div class="sobat-ai-chat" id="sobatAiChat" hidden>
                <div class="sobat-ai-chat-head">
                    <div><b>Chat AI SobatAnak</b><span>Rekomendasi realtime dari produk & artikel toko</span></div>
                    <button type="button" id="sobatAiClose">Tutup</button>
                </div>
                <div class="sobat-ai-messages" id="sobatAiMessages"></div>
            </div>
        </div>
    </div>
</section>

<style>
.sobat-ai-chat-section{padding:4rem 1.5rem;background:linear-gradient(135deg,#fff 0%,#f7fffd 55%,#e9fbf7 100%)}
.sobat-ai-card{max-width:1180px;margin:0 auto;border:1px solid rgba(75,191,176,.28);border-radius:2rem;background:rgba(255,255,255,.82);box-shadow:0 22px 55px rgba(42,61,60,.08);padding:3rem;display:grid;grid-template-columns:.72fr 1.28fr;gap:3rem;align-items:center;overflow:hidden;position:relative}
.sobat-ai-card:before{content:"";position:absolute;right:-140px;bottom:-130px;width:360px;height:360px;background:rgba(75,191,176,.13);border-radius:999px;filter:blur(8px)}
.sobat-ai-left,.sobat-ai-right{position:relative;z-index:1}.sobat-ai-kicker{display:block;color:#e8756a;font-weight:1000;letter-spacing:.18em;font-size:.72rem;margin-bottom:1.2rem}.sobat-ai-title{font-family:Nunito,system-ui,sans-serif;color:#2a3d3c;font-weight:1000;font-size:clamp(1.8rem,4vw,3.4rem);line-height:1.04;letter-spacing:-.055em;max-width:500px}.sobat-ai-title mark{background:linear-gradient(180deg,transparent 55%,#ffe8a6 0);color:#2a3d3c;padding:0 .08em}.sobat-ai-desc{color:#6b8a88;font-weight:900;font-size:1.02rem;line-height:1.75;max-width:470px;margin-top:1.25rem}.sobat-ai-search{display:flex;align-items:center;gap:.9rem;background:#fff;border:1.5px solid rgba(47,118,214,.28);border-radius:999px;padding:.65rem .7rem .65rem 1.15rem;box-shadow:0 18px 42px rgba(42,61,60,.10);position:relative}.sobat-ai-dot{width:1rem;height:1rem;border-radius:999px;background:#67d98c;box-shadow:0 0 0 .75rem rgba(103,217,140,.15);flex:0 0 auto}.sobat-ai-search input{flex:1;min-width:0;border:0;outline:0;background:transparent;color:#2a3d3c;font-weight:1000;font-size:1.15rem}.sobat-ai-search input::placeholder{color:#99a7b5}.sobat-ai-search button[type="submit"]{border:0;background:#2f76d6;color:#fff;font-weight:1000;border-radius:999px;padding:1rem 1.45rem;box-shadow:0 10px 20px rgba(47,118,214,.22);transition:.22s}.sobat-ai-search button[type="submit"]:hover{transform:translateY(-2px)}.sobat-ai-clear{display:none;border:0!important;background:#f4f8f8!important;color:#6b8a88!important;width:2.3rem;height:2.3rem;border-radius:999px;font-size:1.2rem;font-weight:1000;padding:0!important;box-shadow:none!important}.sobat-ai-search.has-text .sobat-ai-clear{display:inline-grid;place-items:center}.sobat-ai-chips{display:flex;flex-wrap:wrap;gap:.7rem;margin-top:1rem}.sobat-ai-chips button,.sobat-ai-suggestions button{border:1px solid rgba(75,191,176,.35);background:rgba(255,255,255,.92);color:#2da69b;border-radius:999px;padding:.72rem 1rem;font-weight:1000;transition:.2s}.sobat-ai-chips button:hover,.sobat-ai-suggestions button:hover{background:#dff7f4;transform:translateY(-1px)}.sobat-ai-suggestions{display:none;margin:.75rem 0 0;background:#fff;border:1px solid rgba(75,191,176,.25);border-radius:1.35rem;padding:.6rem;box-shadow:0 16px 35px rgba(42,61,60,.08)}.sobat-ai-suggestions.is-open{display:grid;gap:.45rem}.sobat-ai-suggestions button{width:100%;text-align:left;border-radius:1rem;background:#fbfffe;color:#2a3d3c}.sobat-ai-chat{margin-top:1.15rem;background:#fff;border:1px solid rgba(75,191,176,.28);border-radius:1.5rem;box-shadow:0 22px 45px rgba(42,61,60,.08);overflow:hidden;animation:sobatChatIn .28s ease both}.sobat-ai-chat-head{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1rem 1.1rem;background:#f6fefd;border-bottom:1px solid rgba(75,191,176,.20)}.sobat-ai-chat-head b{display:block;color:#2a3d3c;font-size:1.05rem}.sobat-ai-chat-head span{display:block;color:#6b8a88;font-weight:800;font-size:.82rem}.sobat-ai-chat-head button{border:0;background:#fdecea;color:#e8756a;border-radius:999px;padding:.55rem .85rem;font-weight:1000}.sobat-ai-messages{max-height:520px;overflow:auto;padding:1.05rem;display:grid;gap:.85rem}.sobat-ai-bubble{max-width:86%;border-radius:1.25rem;padding:1rem 1.05rem;font-weight:800;line-height:1.62;white-space:pre-line}.sobat-ai-bubble.user{justify-self:end;background:#2f76d6;color:#fff;border-bottom-right-radius:.35rem}.sobat-ai-bubble.bot{justify-self:start;background:#f2fbfa;color:#2a3d3c;border:1px solid rgba(75,191,176,.20);border-bottom-left-radius:.35rem}.sobat-ai-recs{display:grid;gap:.75rem;margin-top:.9rem}.sobat-ai-rec-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.sobat-ai-rec{display:flex;gap:.75rem;align-items:center;background:#fff;border:1px solid rgba(75,191,176,.22);border-radius:1rem;padding:.65rem;text-decoration:none;color:#2a3d3c}.sobat-ai-rec img{width:54px;height:54px;border-radius:.8rem;object-fit:cover;background:#f2f8f8}.sobat-ai-rec b{display:block;font-weight:1000;font-size:.9rem}.sobat-ai-rec small{display:block;color:#6b8a88;font-weight:900}.sobat-ai-rec strong{display:block;color:#2da69b;font-size:.9rem}.sobat-ai-article{display:block;background:#fff;border:1px solid rgba(75,191,176,.22);border-radius:1rem;padding:.8rem;color:#2a3d3c}.sobat-ai-article small{display:block;color:#e8756a;font-weight:1000}.sobat-ai-typing{display:inline-flex;gap:.25rem}.sobat-ai-typing i{width:.45rem;height:.45rem;background:#6b8a88;border-radius:999px;animation:sobatDot 1s infinite ease-in-out}.sobat-ai-typing i:nth-child(2){animation-delay:.12s}.sobat-ai-typing i:nth-child(3){animation-delay:.24s}@keyframes sobatDot{0%,80%,100%{opacity:.25;transform:translateY(0)}40%{opacity:1;transform:translateY(-4px)}}@keyframes sobatChatIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}@media(max-width:920px){.sobat-ai-card{grid-template-columns:1fr;padding:2rem}.sobat-ai-title{font-size:clamp(2.1rem,12vw,4rem)}.sobat-ai-rec-grid{grid-template-columns:1fr}}@media(max-width:560px){.sobat-ai-chat-section{padding:2.5rem 1rem}.sobat-ai-search{border-radius:1.4rem;align-items:stretch}.sobat-ai-search button[type="submit"]{padding:.85rem 1rem}.sobat-ai-bubble{max-width:94%}}
</style>

<script>
(() => {
    const form = document.getElementById('sobatAiForm');
    if (!form) return;
    const input = document.getElementById('sobatAiInput');
    const clearBtn = document.getElementById('sobatAiClear');
    const suggestions = document.getElementById('sobatAiSuggestions');
    const chips = document.getElementById('sobatAiChips');
    const chat = document.getElementById('sobatAiChat');
    const messages = document.getElementById('sobatAiMessages');
    const closeBtn = document.getElementById('sobatAiClose');
    const placeholders = ['botol susu anti kolik','popok newborn','mainan edukatif','perawatan kulit bayi','pakaian bayi nyaman','perlengkapan mpasi','anak susah makan'];
    let placeholderIndex = 0;
    let sessionId = null;
    let debounce = null;

    setInterval(() => {
        if (document.activeElement === input || input.value.trim()) return;
        placeholderIndex = (placeholderIndex + 1) % placeholders.length;
        input.placeholder = 'Contoh: ' + placeholders[placeholderIndex];
    }, 2400);

    function csrf(){ return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
    function esc(text){ return String(text || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
    function addBubble(role, html){
        const div = document.createElement('div');
        div.className = 'sobat-ai-bubble ' + role;
        div.innerHTML = html;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }
    function productCard(p){
        return `<a class="sobat-ai-rec" href="${p.url}"><img src="${esc(p.image)}" alt=""><span><b>${esc(p.name)}</b><small>${esc(p.category)} · ⭐ ${esc(p.rating)}</small><strong>${esc(p.price)}</strong><small>${esc(p.stock_status)}</small></span></a>`;
    }
    function articleCard(a){
        return `<div class="sobat-ai-article"><small>${esc(a.category)}</small><b>${esc(a.title)}</b><p>${esc(a.excerpt)}</p></div>`;
    }
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
    closeBtn.addEventListener('click', () => { chat.hidden = true; messages.innerHTML=''; sessionId = null; });
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
<section class="py-16 testimonial-web-section"><div class="max-w-7xl mx-auto px-6 md:px-12"><div class="testimonial-home-head mb-8"><div class="testimonial-home-copy"><span class="text-coral font-black uppercase tracking-widest text-xs">Ulasan Website</span><h2 class="section-title font-display mt-2">Apa Kata <span class="text-teal">Mereka</span></h2><p class="text-[#6B8A88] font-bold mt-2">Komentar paling depan otomatis diurutkan dari yang paling banyak like. Semua ulasan bisa dilihat dan difilter seperti marketplace.</p></div><div class="testimonial-home-actions">@if($authUser)<button type="button" class="btn-pill btn-coral testimonial-action-primary" onclick="document.getElementById('testimonial-form-box')?.classList.toggle('hidden')">+ Tulis Ulasan</button>@else<a href="{{ route('login') }}" class="btn-pill btn-teal testimonial-action-primary">Login untuk Ulasan</a>@endif<a href="{{ route('testimonials.index') }}" class="btn-pill testimonial-action-secondary">Lihat Semua Ulasan</a></div></div>@if($authUser)<div id="testimonial-form-box" class="testimonial-form-box hidden mb-8"><form method="POST" action="{{ route('testimonials.store.user') }}" class="grid gap-4">@csrf<div><label class="text-sm font-black text-[#2A3D3C]">Rating</label><select name="rating" class="auth-input mt-2"><option value="5">★★★★★ - Sangat suka</option><option value="4">★★★★☆ - Bagus</option><option value="3">★★★☆☆ - Cukup</option><option value="2">★★☆☆☆ - Kurang</option><option value="1">★☆☆☆☆ - Perlu diperbaiki</option></select></div><div><label class="text-sm font-black text-[#2A3D3C]">Komentar kamu</label><textarea name="message" class="auth-input min-h-[120px] mt-2" placeholder="Tulis pengalaman kamu memakai SobatAnak..." maxlength="400" required></textarea></div><button class="btn-pill btn-coral w-fit">Kirim Ulasan</button></form></div>@endif<div class="grid md:grid-cols-3 gap-5">@forelse($testimonials as $t)<div class="card p-6 testimonial-card"><div class="flex items-center justify-between gap-3"><p class="text-yellow-500 tracking-wide">{{ str_repeat('★', (int)($t->rating ?? 5)) }}{{ str_repeat('☆', 5 - (int)($t->rating ?? 5)) }}</p><button type="button" class="testimonial-like-btn {{ in_array($t->id, $likedTestimonialIds ?? []) ? 'liked' : '' }}" data-testimonial-like="{{ $t->id }}" aria-label="Like komentar">♥ <span>{{ $t->likes_count ?? $t->likes_count ?? 0 }}</span></button></div><p class="text-[#6B8A88] my-4 leading-relaxed">“{{ $t->message }}”</p><div class="flex items-center justify-between gap-3"><b>{{ $t->name }}</b><small class="text-[#6B8A88] font-bold">{{ optional($t->created_at)->format('d M Y') }}</small></div></div>@empty<div class="md:col-span-3 card p-8 text-center"><h3 class="font-display text-2xl">Belum ada ulasan</h3><p class="text-[#6B8A88] mt-2">Jadilah user pertama yang memberi komentar tentang SobatAnak.</p></div>@endforelse</div></div></section>
<section class="py-16 bg-white"><div class="max-w-7xl mx-auto px-6 md:px-12"><div class="flex justify-between mb-8"><h2 class="section-title font-display">Artikel <span class="text-teal">Parenting</span></h2><a class="font-black" href="{{ route('articles') }}">Semua Artikel →</a></div><div class="grid md:grid-cols-3 gap-5">@foreach($articles as $a)<article class="card modal-card cursor-pointer" data-open-article data-title="{{ $a->title }}" data-category="{{ $a->category_name }}" data-excerpt="{{ $a->excerpt }}" data-image="{{ $a->image }}" data-date="{{ $a->created_at ? $a->created_at->format('d M Y') : 'SobatAnak' }}"><img class="product-img w-full" src="{{ $a->image }}"><div class="p-5"><span class="text-coral font-black text-xs uppercase">{{ $a->category_name }}</span><h3 class="font-display text-xl mt-2">{{ $a->title }}</h3><p class="text-[#6B8A88] mt-2">{{ $a->excerpt }}</p><button type="button" class="modal-read-more mt-3">Baca ringkasan →</button></div></article>@endforeach</div></div></section>

<div class="info-modal" data-product-modal aria-hidden="true"><div class="info-modal-backdrop" data-modal-close></div><div class="info-modal-panel product-detail-panel" role="dialog" aria-modal="true"><button class="modal-close" data-modal-close aria-label="Tutup">×</button><div class="grid lg:grid-cols-[1fr_1.05fr] gap-6 items-stretch"><div class="modal-image-wrap"><img data-modal-product-image src="" alt="Detail produk"></div><div class="modal-content"><span class="modal-kicker" data-modal-product-category></span><h2 class="font-display modal-title" data-modal-product-name></h2><div class="flex flex-wrap gap-2 my-4"><span class="modal-chip" data-modal-product-rating></span><span class="modal-chip" data-modal-product-sold></span><span class="modal-chip" data-modal-product-badge></span></div><h3 class="modal-price" data-modal-product-price></h3><p class="modal-desc" data-modal-product-desc></p><div class="modal-benefits"><div><b>✨ Kelebihan</b><p>Aman, nyaman, dan cocok untuk kebutuhan harian bayi serta anak.</p></div><div><b>🧸 Cocok Untuk</b><p>Orang tua yang ingin produk praktis, berkualitas, dan mudah digunakan.</p></div><div><b>🛍️ Catatan</b><p>Tambahkan ke cart untuk lanjut ke halaman checkout khusus akun kamu.</p></div></div><div class="flex flex-wrap gap-3 mt-6"><button data-modal-product-buy class="btn-pill btn-coral">+ Masukkan Cart</button><a href="{{ route('cart.index') }}" class="btn-pill btn-teal">Lihat Cart</a></div></div></div></div></div>
<div class="info-modal" data-article-modal aria-hidden="true"><div class="info-modal-backdrop" data-modal-close></div><div class="info-modal-panel article-detail-panel" role="dialog" aria-modal="true"><button class="modal-close" data-modal-close aria-label="Tutup">×</button><div class="modal-article-hero"><img data-modal-article-image src="" alt="Detail artikel"></div><div class="modal-content article-modal-content"><span class="modal-kicker" data-modal-article-category></span><h2 class="font-display modal-title" data-modal-article-title></h2><p class="modal-meta">📅 <span data-modal-article-date></span> · ⏱️ 3 menit baca · SobatAnak Editorial</p><p class="modal-desc" data-modal-article-excerpt></p><div class="article-pop-grid"><div><b>Ringkasan</b><p data-modal-article-summary></p></div><div><b>Yang Dibahas</b><ul><li>Tips praktis untuk orang tua.</li><li>Hal penting yang perlu diperhatikan.</li><li>Rekomendasi kebiasaan baik di rumah.</li></ul></div><div><b>Catatan SobatAnak</b><p>Gunakan artikel ini sebagai panduan awal. Sesuaikan kembali dengan kebutuhan si kecil dan kondisi keluarga.</p></div></div></div></div></div>

<style>

.testimonial-home-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:2rem;align-items:center}
.testimonial-home-copy{max-width:58rem}
.testimonial-home-copy p{max-width:100%;line-height:1.65}
.testimonial-home-actions{display:flex;flex-direction:column;gap:.85rem;align-items:stretch;min-width:230px}
.testimonial-action-primary,.testimonial-action-secondary{justify-content:center;white-space:nowrap}
.testimonial-action-secondary{background:#fff;border:2px solid #4BBFB0;color:var(--teal2)}
.testimonial-action-secondary:hover{background:#D0F0ED;transform:translateY(-2px)}
@media(max-width:1024px){.testimonial-home-head{grid-template-columns:1fr}.testimonial-home-actions{flex-direction:row;flex-wrap:wrap;min-width:0}.testimonial-action-primary,.testimonial-action-secondary{width:fit-content}}
@media(max-width:640px){.testimonial-home-actions{flex-direction:column;align-items:stretch}.testimonial-action-primary,.testimonial-action-secondary{width:100%}}

.testimonial-form-box{background:linear-gradient(135deg,#fff,#F6FEFD);border:1px solid var(--border);border-radius:1.5rem;padding:1.25rem;box-shadow:0 18px 42px rgba(42,61,60,.08)}
.testimonial-card{position:relative}.testimonial-like-btn{display:inline-flex;align-items:center;gap:.35rem;border:1.5px solid var(--border);background:#fff;color:#6B8A88;border-radius:999px;padding:.35rem .7rem;font-weight:1000;transition:.22s}.testimonial-like-btn:hover{border-color:var(--coral);color:var(--coral);transform:translateY(-1px)}.testimonial-like-btn.liked{background:#FDECEA;border-color:rgba(232,117,106,.45);color:var(--coral)}.testimonial-like-btn:disabled{opacity:.65;cursor:not-allowed;transform:none}
</style>
<script>
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