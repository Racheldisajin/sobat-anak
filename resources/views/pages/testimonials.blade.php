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
    <div class="testimonial-back-row mb-6">
        <a href="{{ route('home') }}" class="testimonial-back-home">← Kembali ke Beranda</a>
    </div>
    @if(session('success'))
        <div class="testimonial-alert mb-6" style="background:#D0F0ED; color:#2A3D3C; border-color:#4BBFB0;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="testimonial-alert mb-6" style="background:#FDECEA; color:#D84315; border-color:#F5A400;">
            <ul style="list-style-type: disc; margin-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
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
                    @php
                        $displayLikes = max((int)($t->likes_count ?? 0), (int)($t->real_likes_count ?? 0));
                        $isOwner = $authUser && (int)($t->user_id ?? 0) === (int)$authUser->id;
                        $isEdited = (bool)($t->is_edited ?? false);
                        $avatarPath = $t->user->avatar ?? null;
                        $avatarUrl = $avatarPath ? ((str_starts_with($avatarPath, 'http://') || str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, '/')) ? $avatarPath : asset($avatarPath)) : null;
                        $isAdminReview = strtolower((string)($t->user->role ?? 'user')) === 'admin';
                    @endphp
                    <article class="testimonial-review-item">
                        <div class="testimonial-review-avatar">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $t->name }}">
                            @else
                                {{ strtoupper(mb_substr($t->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <div class="testimonial-review-body">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-lg flex items-center gap-2 flex-wrap">{{ $t->name }} @if($isAdminReview)<span class="admin-review-badge">Admin SobatAnak</span>@endif</h3>
                                    <p class="testimonial-stars">{{ str_repeat('★', (int)($t->rating ?? 5)) }}{{ str_repeat('☆', 5 - (int)($t->rating ?? 5)) }}</p>
                                </div>
                                <small class="text-[#6B8A88] font-bold">{{ optional($t->created_at)->format('d M Y') }}</small>
                            </div>
                            <p class="testimonial-message">“{{ $t->message }}”</p>
                            @if($isEdited)
                                <span class="testimonial-edited-badge">Edited</span>
                            @endif
                            <div class="testimonial-review-actions-row">
                                <button type="button"
                                    class="testimonial-like-btn {{ in_array($t->id, $likedTestimonialIds ?? []) ? 'liked' : '' }}"
                                    data-testimonial-like="{{ $t->id }}"
                                    aria-label="Like komentar">
                                    ♥ <span>{{ $displayLikes }}</span> Like
                                </button>
                                @if($isOwner)
                                    <button type="button" class="testimonial-mini-action" data-toggle-edit="testimonial-edit-{{ $t->id }}">Edit Ulasan</button>
                                    <form method="POST" action="{{ route('testimonials.destroy.user', $t) }}" data-cute-confirm="Hapus ulasan website ini?" data-cute-detail="Ulasan kamu akan dihapus permanen dari SobatAnak.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="testimonial-mini-action danger">Hapus</button>
                                    </form>
                                @endif
                            </div>
                            @if($isOwner)
                                <form id="testimonial-edit-{{ $t->id }}" method="POST" action="{{ route('testimonials.update', $t) }}" class="testimonial-inline-edit hidden">
                                    @csrf
                                    @method('PATCH')
                                    <label>Rating</label>
                                    <select name="rating" class="auth-input">
                                        @for($rate = 5; $rate >= 1; $rate--)
                                            <option value="{{ $rate }}" @selected((int)($t->rating ?? 5) === $rate)>{{ str_repeat('★', $rate) }}{{ str_repeat('☆', 5 - $rate) }}</option>
                                        @endfor
                                    </select>
                                    <label>Pesan ulasan</label>
                                    <textarea name="message" class="auth-input" maxlength="400" required>{{ $t->message }}</textarea>
                                    <div class="flex gap-2 flex-wrap">
                                        <button class="testimonial-save-btn">Simpan Perubahan</button>
                                        <button type="button" class="testimonial-cancel-btn" data-toggle-edit="testimonial-edit-{{ $t->id }}">Batal</button>
                                    </div>
                                </form>
                            @endif
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
.testimonial-back-row{display:flex;align-items:center;justify-content:flex-start}.testimonial-back-home{display:inline-flex;align-items:center;gap:.45rem;background:#fff;border:1.5px solid var(--border);border-radius:999px;padding:.75rem 1.1rem;font-weight:1000;color:#2A3D3C;box-shadow:0 14px 30px rgba(42,61,60,.06);transition:.22s}.testimonial-back-home:hover{background:#D0F0ED;border-color:#4BBFB0;color:#2E8F84;transform:translateY(-2px)}
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
.testimonial-review-avatar{width:52px;height:52px;border-radius:999px;background:linear-gradient(135deg,var(--teal),var(--coral));color:white;display:flex;align-items:center;justify-content:center;font-weight:1000;font-size:1.25rem;overflow:hidden;flex:0 0 52px}.testimonial-review-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.testimonial-message{color:#6B8A88;font-weight:800;line-height:1.75;margin:1rem 0}
.testimonial-form-box{background:linear-gradient(135deg,#fff,#F6FEFD);border:1px solid var(--border);border-radius:1.5rem;padding:1.25rem;box-shadow:0 18px 42px rgba(42,61,60,.08)}
.testimonial-like-btn{display:inline-flex;align-items:center;gap:.35rem;border:1.5px solid var(--border);background:#fff;color:#6B8A88;border-radius:999px;padding:.45rem .85rem;font-weight:1000;transition:.22s}
.testimonial-like-btn:hover{border-color:var(--coral);color:var(--coral);transform:translateY(-1px)}
.testimonial-like-btn.liked{background:#FDECEA;border-color:rgba(232,117,106,.45);color:var(--coral)}
.testimonial-like-btn:disabled{opacity:.65;cursor:not-allowed;transform:none}
.testimonial-pagination nav{display:flex;justify-content:center}
.testimonial-edited-badge{display:inline-flex;width:max-content;border:1px solid #D4EEEC;background:#F0FFFC;color:#2E8F84;border-radius:999px;padding:.25rem .6rem;font-size:.72rem;font-weight:1000;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.7rem}.testimonial-review-actions-row{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap}.testimonial-review-actions-row form{display:inline}.testimonial-mini-action{border:1.5px solid #D4EEEC;background:#fff;color:#2E8F84;border-radius:999px;padding:.45rem .82rem;font-weight:1000;font-size:.8rem;transition:.18s}.testimonial-mini-action:hover{background:#D0F0ED;transform:translateY(-1px)}.testimonial-mini-action.danger{color:#E8756A;border-color:#FFD2CC;background:#FFF7F5}.testimonial-mini-action.danger:hover{background:#FDECEA}.testimonial-inline-edit{display:grid;gap:.62rem;background:#F8FEFD;border:1px dashed #BFECE6;border-radius:18px;padding:.9rem;margin:.95rem 0 0}.testimonial-inline-edit.hidden{display:none}.testimonial-inline-edit label{font-size:.78rem;font-weight:1000;color:#2A3D3C}.testimonial-inline-edit textarea{min-height:94px;resize:vertical}.testimonial-save-btn,.testimonial-cancel-btn{border:0;border-radius:999px;padding:.58rem 1rem;font-weight:1000}.testimonial-save-btn{background:#4BBFB0;color:white}.testimonial-cancel-btn{background:#FDECEA;color:#E8756A}
@media(max-width:980px){.testimonial-shop-layout{grid-template-columns:1fr}.testimonial-summary-card{position:relative;top:auto}.testimonial-review-item{grid-template-columns:1fr}.testimonial-review-avatar{display:none}}
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
