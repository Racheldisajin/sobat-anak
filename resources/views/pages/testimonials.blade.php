@extends('layouts.app')
@section('title','Semua Ulasan Website — SobatAnak')
@section('content')

<section class="bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-14 md:py-16">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Ulasan Website</span>
        <h1 class="font-display hero-title mt-3">Semua Ulasan <span class="text-teal">Pengguna</span></h1>
        <p class="text-[#6B8A88] font-bold mt-3 max-w-2xl">Lihat pengalaman pengguna SobatAnak. Ulasan bisa difilter berdasarkan bintang dan bisa diurutkan berdasarkan like terbanyak atau terbaru.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-10 testimonial-page">
    @if(session('success'))
        <div class="testimonial-alert mb-6">{{ session('success') }}</div>
    @endif

    <div class="testimonial-shop-layout">
        <aside class="testimonial-summary-card">
            <p class="text-coral font-black uppercase tracking-widest text-xs">Ringkasan Rating</p>
            <div class="testimonial-score mt-3">
                <b>{{ number_format($averageRating, 1) }}</b>
                <span>/ 5</span>
            </div>
            <p class="testimonial-stars mt-2">{{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</p>
            <p class="text-[#6B8A88] font-bold mt-2">{{ number_format($totalReviews, 0, ',', '.') }} ulasan masuk</p>

            <div class="testimonial-rating-list mt-6">
                @for($i = 5; $i >= 1; $i--)
                    @php $count = (int) ($ratingCounts[$i] ?? 0); @endphp
                    <a href="{{ route('testimonials.index', ['rating' => $i, 'sort' => $activeSort]) }}"
                       class="testimonial-rating-row {{ (string)$activeRating === (string)$i ? 'active' : '' }}">
                        <span>{{ $i }} ★</span>
                        <b>{{ $count }}</b>
                    </a>
                @endfor
            </div>

            @if($authUser)
                <button type="button" class="btn-pill btn-coral mt-6 w-full justify-center" onclick="document.getElementById('testimonial-full-form')?.classList.toggle('hidden')">+ Tulis Ulasan</button>
            @else
                <a href="{{ route('login') }}" class="btn-pill btn-teal mt-6 w-full justify-center">Login untuk Ulasan</a>
            @endif
        </aside>

        <main class="testimonial-main-list">
            @if($authUser)
            <div id="testimonial-full-form" class="testimonial-form-box hidden mb-6">
                <form method="POST" action="{{ route('testimonials.store.user') }}" class="grid gap-4">
                    @csrf
                    <div>
                        <label class="text-sm font-black text-[#2A3D3C]">Rating kamu</label>
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

            <div class="testimonial-toolbar">
                <div class="testimonial-filter-pills">
                    <a class="{{ $activeRating === 'all' ? 'active' : '' }}" href="{{ route('testimonials.index', ['rating' => 'all', 'sort' => $activeSort]) }}">Semua</a>
                    @for($i = 5; $i >= 1; $i--)
                        <a class="{{ (string)$activeRating === (string)$i ? 'active' : '' }}" href="{{ route('testimonials.index', ['rating' => $i, 'sort' => $activeSort]) }}">{{ $i }} Bintang</a>
                    @endfor
                </div>

                <div class="testimonial-sort-tabs">
                    <a class="{{ $activeSort === 'liked' ? 'active' : '' }}" href="{{ route('testimonials.index', ['rating' => $activeRating, 'sort' => 'liked']) }}">Paling Disukai</a>
                    <a class="{{ $activeSort === 'newest' ? 'active' : '' }}" href="{{ route('testimonials.index', ['rating' => $activeRating, 'sort' => 'newest']) }}">Terbaru</a>
                </div>
            </div>

            <div class="testimonial-review-list">
                @forelse($testimonials as $t)
                    <article class="testimonial-review-item">
                        <div class="testimonial-review-avatar">{{ strtoupper(mb_substr($t->name ?? 'U', 0, 1)) }}</div>
                        <div class="testimonial-review-body">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-lg">{{ $t->name }}</h3>
                                    <p class="testimonial-stars">{{ str_repeat('★', (int)($t->rating ?? 5)) }}{{ str_repeat('☆', 5 - (int)($t->rating ?? 5)) }}</p>
                                </div>
                                <small class="text-[#6B8A88] font-bold">{{ optional($t->created_at)->format('d M Y') }}</small>
                            </div>
                            <p class="testimonial-message">“{{ $t->message }}”</p>
                            <button type="button"
                                class="testimonial-like-btn {{ in_array($t->id, $likedTestimonialIds ?? []) ? 'liked' : '' }}"
                                data-testimonial-like="{{ $t->id }}"
                                aria-label="Like komentar">
                                ♥ <span>{{ $t->likes_count ?? $t->likes_count ?? 0 }}</span> Like
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="card p-8 text-center">
                        <h3 class="font-display text-2xl">Belum ada ulasan di filter ini</h3>
                        <p class="text-[#6B8A88] mt-2">Coba pilih filter bintang lain atau tulis ulasan baru.</p>
                    </div>
                @endforelse
            </div>

            @if($testimonials->hasPages())
                <div class="testimonial-pagination mt-8">
                    {{ $testimonials->links() }}
                </div>
            @endif
        </main>
    </div>
</section>

<style>
.testimonial-alert{background:#D0F0ED;border:1px solid var(--border);border-radius:1rem;padding:1rem 1.2rem;font-weight:900;color:#2A3D3C}
.testimonial-shop-layout{display:grid;grid-template-columns:320px minmax(0,1fr);gap:1.4rem;align-items:start}
.testimonial-summary-card{position:sticky;top:96px;background:white;border:1px solid var(--border);border-radius:1.5rem;padding:1.35rem;box-shadow:0 18px 44px rgba(42,61,60,.08)}
.testimonial-score b{font-size:3rem;line-height:1;color:var(--coral);font-weight:1000}.testimonial-score span{color:#6B8A88;font-weight:900}
.testimonial-stars{color:#F5A400;letter-spacing:.08em;font-weight:1000}
.testimonial-rating-list{display:grid;gap:.55rem}
.testimonial-rating-row{display:flex;align-items:center;justify-content:space-between;border:1px solid var(--border);background:#F8FEFD;border-radius:999px;padding:.65rem .85rem;font-weight:900;color:#2A3D3C;transition:.2s}
.testimonial-rating-row:hover,.testimonial-rating-row.active{background:#D0F0ED;border-color:#4BBFB0;color:#2E8F84}
.testimonial-toolbar{background:white;border:1px solid var(--border);border-radius:1.4rem;padding:1rem;display:grid;gap:1rem;margin-bottom:1rem}
.testimonial-filter-pills,.testimonial-sort-tabs{display:flex;gap:.55rem;flex-wrap:wrap}
.testimonial-filter-pills a,.testimonial-sort-tabs a{border:1px solid var(--border);border-radius:999px;padding:.55rem .9rem;font-weight:900;color:#2A3D3C;background:#fff;transition:.2s}
.testimonial-filter-pills a:hover,.testimonial-filter-pills a.active,.testimonial-sort-tabs a:hover,.testimonial-sort-tabs a.active{background:#FDECEA;border-color:rgba(232,117,106,.45);color:var(--coral)}
.testimonial-review-list{display:grid;gap:1rem}
.testimonial-review-item{display:grid;grid-template-columns:52px 1fr;gap:1rem;background:#fff;border:1px solid var(--border);border-radius:1.4rem;padding:1.15rem;box-shadow:0 12px 30px rgba(42,61,60,.05)}
.testimonial-review-avatar{width:52px;height:52px;border-radius:999px;background:linear-gradient(135deg,var(--teal),var(--coral));color:white;display:flex;align-items:center;justify-content:center;font-weight:1000;font-size:1.25rem}
.testimonial-message{color:#6B8A88;font-weight:800;line-height:1.75;margin:1rem 0}
.testimonial-form-box{background:linear-gradient(135deg,#fff,#F6FEFD);border:1px solid var(--border);border-radius:1.5rem;padding:1.25rem;box-shadow:0 18px 42px rgba(42,61,60,.08)}
.testimonial-like-btn{display:inline-flex;align-items:center;gap:.35rem;border:1.5px solid var(--border);background:#fff;color:#6B8A88;border-radius:999px;padding:.45rem .85rem;font-weight:1000;transition:.22s}
.testimonial-like-btn:hover{border-color:var(--coral);color:var(--coral);transform:translateY(-1px)}
.testimonial-like-btn.liked{background:#FDECEA;border-color:rgba(232,117,106,.45);color:var(--coral)}
.testimonial-like-btn:disabled{opacity:.65;cursor:not-allowed;transform:none}
.testimonial-pagination nav{display:flex;justify-content:center}
@media(max-width:980px){.testimonial-shop-layout{grid-template-columns:1fr}.testimonial-summary-card{position:relative;top:auto}.testimonial-review-item{grid-template-columns:1fr}.testimonial-review-avatar{display:none}}
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
