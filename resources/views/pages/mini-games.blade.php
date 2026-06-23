@extends('layouts.app')
@section('title','Mini Game — SobatAnak')
@section('content')


<section class="mini-page-hero">
    <div class="max-w-7xl mx-auto px-6 md:px-12 mini-hero-grid">
        <div>
            <div class="mini-eyebrow">Mini Game SobatAnak</div>
            <h1 class="mini-title">Main Seru, Kumpulkan <span>Poin</span></h1>
            <p class="mini-subtitle">Pilih game favorit, mainkan sampai selesai, lalu kumpulkan poin untuk ditukar reward. Semua game dibuat ringan dan ramah anak.</p>
            <div class="mini-hero-actions">
                <div class="mini-point-chip">⭐ Poin Kamu: <b data-points>{{ number_format($authPoints ?: 0,0,',','.') }}</b></div>
                <a href="#daftar-game" class="mini-scroll-btn">Lihat Game ↓</a>
            </div>
        </div>
        <div class="mini-hero-card" aria-hidden="true">
            <div class="mini-orbit mini-orbit-a">🧩</div>
            <div class="mini-orbit mini-orbit-b">🎨</div>
            <div class="mini-orbit mini-orbit-c">🦷</div>
            <div class="mini-main-icon">🎮</div>
            <p>Belajar sambil bermain</p>
        </div>
    </div>
</section>

<section id="daftar-game" class="max-w-7xl mx-auto px-6 md:px-12 py-12">
    <div class="mini-layout-grid">
        <div>
            <div class="mini-section-head">
                <div>
                    <div class="mini-eyebrow">Daftar Game</div>
                    <h2 class="mini-section-title">Pilih Game Favorit</h2>
                </div>
                <span class="mini-count">{{ $activeGameCount ?? collect($games ?? [])->where('is_active', true)->count() }} game aktif</span>
            </div>

            <div class="mini-games-grid">
                @foreach(($games ?? collect()) as $game)
                    @php
                        $isActive = !empty($game['is_active']);
                        $isReady = !empty($game['available']);
                    @endphp

                    @if($isActive)
                        <a href="{{ $game['url'] }}" class="mini-game-card mini-{{ $game['color'] }}" aria-label="Main {{ $game['title'] }}">
                    @else
                        <div class="mini-game-card mini-{{ $game['color'] }} mini-game-maintenance" aria-disabled="true">
                    @endif
                        <div class="mini-card-top">
                            @if(!empty($game['cover_image']))
                                <img src="{{ asset(ltrim($game['cover_image'], '/')) }}" class="mini-game-cover" alt="{{ $game['title'] }}">
                            @else
                                <div class="mini-game-icon">{{ $game['icon'] }}</div>
                            @endif

                            @if(!$isActive)
                                <span class="mini-status mini-status-off">Maintenance</span>
                            @else
                                <span class="mini-status">{{ $isReady ? 'Aktif' : 'Cek Build' }}</span>
                            @endif
                        </div>
                        <h3>{{ $game['title'] }}</h3>
                        <p>{{ $game['desc'] }}</p>

                        @if(!$isActive)
                            <div class="mini-maintenance-note">🔧 Game sedang maintenance</div>
                            <div class="mini-play-row mini-play-disabled">
                                <span>Belum Bisa Dimainkan</span>
                                <b>!</b>
                            </div>
                        @else
                            <div class="mini-play-row">
                                <span>Main Sekarang</span>
                                <b>→</b>
                            </div>
                        @endif
                    @if($isActive)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <aside class="mini-side">
            <div class="mini-leaderboard-card">
                <div class="mini-leaderboard-head">
                    <span>🏆 Papan Peringkat</span>
                    <h3>Top 10 Sobat Poin</h3>
                </div>
                <div class="mini-leaderboard-list">
                    @forelse($leaderboard as $index => $point)
                        <div class="mini-rank-item {{ $index < 3 ? 'is-winner' : '' }}">
                            <div class="mini-rank-number">
                                @if($index === 0) 🥇
                                @elseif($index === 1) 🥈
                                @elseif($index === 2) 🥉
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="mini-rank-avatar">{{ strtoupper(substr($point->user->name ?? 'U', 0, 1)) }}</div>
                            <div class="mini-rank-info">
                                <b>{{ $point->user->name ?? 'User' }}</b>
                                <span>{{ number_format($point->points, 0, ',', '.') }} poin</span>
                            </div>
                        </div>
                    @empty
                        <div class="mini-empty-rank">Belum ada data poin. Ayo mulai main!</div>
                    @endforelse
                </div>
            </div>

            <div class="mini-reward-box">
                <div>🎁</div>
                <h3>Tukar Poin Jadi Reward</h3>
                <p>Poin dari game bisa dipakai untuk menukar voucher dan hadiah SobatAnak.</p>
                @if(session('user_id'))
                    <a href="{{ route('profile.rewards') }}">Tukar Reward →</a>
                @else
                    <a href="{{ route('login') }}">Login untuk Tukar →</a>
                @endif
            </div>
        </aside>
    </div>
</section>

<style>
.mini-page-hero{position:relative;overflow:hidden;padding:70px 0;background:radial-gradient(circle at 10% 15%,rgba(75,191,176,.22),transparent 25rem),radial-gradient(circle at 88% 72%,rgba(232,117,106,.20),transparent 25rem),linear-gradient(135deg,#EEFFFB 0%,#fff 46%,#FFF3E7 100%);border-bottom:1px solid #D4EEEC}
.mini-hero-grid{display:grid;grid-template-columns:minmax(0,1.1fr) 360px;gap:42px;align-items:center}.mini-eyebrow{color:#E8756A;font-weight:1000;text-transform:uppercase;letter-spacing:.18em;font-size:12px}.mini-title{font-family:var(--font-display,inherit);font-weight:1000;font-size:clamp(3.1rem,7vw,6.2rem);letter-spacing:-.06em;line-height:.95;color:#2A3D3C;margin-top:12px}.mini-title span,.mini-section-title span{color:#4BBFB0}.mini-subtitle{max-width:680px;margin-top:20px;color:#66817F;font-size:1.08rem;line-height:1.8;font-weight:850}.mini-hero-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:26px}.mini-point-chip,.mini-scroll-btn{display:inline-flex;align-items:center;gap:8px;padding:13px 18px;border-radius:999px;font-weight:1000;text-decoration:none}.mini-point-chip{background:#fff;border:1px solid #F4C873;color:#2A3D3C;box-shadow:0 14px 34px rgba(42,61,60,.08)}.mini-scroll-btn{background:#4BBFB0;color:#fff;box-shadow:0 14px 32px rgba(75,191,176,.25)}.mini-hero-card{min-height:300px;border-radius:36px;background:rgba(255,255,255,.72);border:1px solid rgba(212,238,236,.92);box-shadow:0 28px 80px rgba(42,61,60,.10);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;flex-direction:column}.mini-main-icon{font-size:7rem;filter:drop-shadow(0 18px 22px rgba(42,61,60,.12));animation:miniFloat 3s ease-in-out infinite}.mini-hero-card p{font-weight:1000;color:#2A3D3C;margin-top:6px}.mini-orbit{position:absolute;font-size:2.4rem;background:#fff;border:1px solid #D4EEEC;border-radius:24px;padding:12px;box-shadow:0 18px 45px rgba(42,61,60,.08)}.mini-orbit-a{left:34px;top:36px}.mini-orbit-b{right:38px;top:56px}.mini-orbit-c{right:58px;bottom:42px}@keyframes miniFloat{0%,100%{transform:translateY(0) rotate(-3deg)}50%{transform:translateY(-12px) rotate(4deg)}}
.mini-layout-grid{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:30px;align-items:start}.mini-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:22px}.mini-section-title{font-family:var(--font-display,inherit);font-weight:1000;color:#2A3D3C;font-size:clamp(2.2rem,4vw,3.4rem);letter-spacing:-.04em;line-height:1}.mini-count{background:#FDECEA;color:#E8756A;border:1px solid #F8BEB7;border-radius:999px;padding:10px 14px;font-weight:1000;font-size:13px;white-space:nowrap}.mini-games-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.mini-game-card{display:flex;flex-direction:column;min-height:250px;text-decoration:none;color:#2A3D3C;background:#fff;border:1px solid #D4EEEC;border-radius:30px;padding:22px;box-shadow:0 18px 55px rgba(42,61,60,.07);position:relative;overflow:hidden;transition:.25s ease}.mini-game-card:before{content:"";position:absolute;inset:auto -20% -40% -20%;height:55%;background:linear-gradient(135deg,rgba(75,191,176,.13),rgba(232,117,106,.10));border-radius:50%;transition:.25s}.mini-game-card:hover{transform:translateY(-7px);box-shadow:0 26px 70px rgba(42,61,60,.12);border-color:#BFECE6}.mini-game-card:hover:before{transform:translateY(-8px) scale(1.04)}.mini-card-top{display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1}.mini-game-cover{width:76px;height:76px;object-fit:cover;border-radius:24px;border:1px solid #D4EEEC;background:#fff;box-shadow:0 12px 26px rgba(42,61,60,.08)}.mini-game-icon{width:76px;height:76px;border-radius:24px;display:flex;align-items:center;justify-content:center;font-size:2.8rem;background:#F8FEFD;border:1px solid #D4EEEC}.mini-status{background:#D0F0ED;color:#2A3D3C;border:1px solid #4BBFB0;border-radius:999px;padding:7px 10px;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.08em}.mini-game-card h3{position:relative;z-index:1;font-size:1.45rem;font-weight:1000;margin-top:18px;letter-spacing:-.02em}.mini-game-card p{position:relative;z-index:1;color:#6B8A88;font-weight:800;line-height:1.65;margin-top:8px;flex:1}.mini-play-row{position:relative;z-index:1;margin-top:18px;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,var(--mini-accent,#4BBFB0),var(--mini-accent-2,#2da99a));border:0;border-radius:999px;padding:13px 16px;font-weight:1000;color:#fff;box-shadow:0 14px 30px rgba(75,191,176,.25);transition:.22s ease}.mini-play-row b{width:28px;height:28px;border-radius:999px;background:rgba(255,255,255,.22);display:inline-flex;align-items:center;justify-content:center}.mini-game-card:hover .mini-play-row{transform:translateY(-2px);box-shadow:0 18px 36px rgba(42,61,60,.16)}.mini-teal{--mini-accent:#4BBFB0;--mini-accent-2:#2fa99c}.mini-purple{--mini-accent:#9B7BFF;--mini-accent-2:#7457E8}.mini-orange{--mini-accent:#FF9B55;--mini-accent-2:#E8756A}.mini-green{--mini-accent:#45C879;--mini-accent-2:#2FAB61}.mini-blue{--mini-accent:#5EA8FF;--mini-accent-2:#3F83E6}.mini-pink{--mini-accent:#FF80B5;--mini-accent-2:#E85D96}.mini-teal .mini-game-icon{background:#EEFFFB}.mini-purple .mini-game-icon{background:#F3F0FF}.mini-orange .mini-game-icon{background:#FFF4EA}.mini-green .mini-game-icon{background:#F0FFF4}.mini-blue .mini-game-icon{background:#EEF7FF}.mini-pink .mini-game-icon{background:#FFF0F6}
.mini-game-maintenance{cursor:not-allowed;opacity:.78;filter:saturate(.72);background:linear-gradient(135deg,#fff,#F8FEFD)}.mini-game-maintenance:hover{transform:none;box-shadow:0 18px 55px rgba(42,61,60,.07);border-color:#D4EEEC}.mini-game-maintenance:after{content:"";position:absolute;inset:0;background:repeating-linear-gradient(135deg,rgba(42,61,60,.025) 0 10px,rgba(75,191,176,.035) 10px 20px);pointer-events:none}.mini-status-off{background:#FFF1EE!important;color:#E8756A!important;border-color:#F8BEB7!important}.mini-maintenance-note{position:relative;z-index:1;margin-top:14px;display:inline-flex;align-items:center;gap:8px;width:max-content;max-width:100%;padding:9px 12px;border-radius:999px;background:#FFF8E6;border:1px solid #F4C873;color:#8A6B10;font-weight:1000;font-size:12px}.mini-play-disabled{background:linear-gradient(135deg,#D8E6E4,#B8CCC9)!important;box-shadow:none!important;color:#5C7370!important}.mini-play-disabled b{background:rgba(255,255,255,.55);color:#E8756A}.mini-game-maintenance:hover .mini-play-row{transform:none;box-shadow:none}
.mini-side{display:grid;gap:18px;position:sticky;top:92px}.mini-leaderboard-card,.mini-reward-box{background:#fff;border:1px solid #D4EEEC;border-radius:28px;box-shadow:0 18px 55px rgba(42,61,60,.07);overflow:hidden}.mini-leaderboard-head{padding:20px 22px;background:linear-gradient(135deg,#EEFFFB,#fff);border-bottom:1px solid #D4EEEC}.mini-leaderboard-head span{color:#E8756A;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.14em}.mini-leaderboard-head h3{font-size:1.35rem;font-weight:1000;color:#2A3D3C;margin-top:5px}.mini-leaderboard-list{padding:10px}.mini-rank-item{display:grid;grid-template-columns:34px 42px 1fr;align-items:center;gap:10px;padding:10px;border-radius:18px}.mini-rank-item.is-winner{background:#FFF8E6}.mini-rank-number{text-align:center;font-weight:1000;color:#6B8A88}.mini-rank-avatar{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#4BBFB0,#E8756A);color:#fff;font-weight:1000}.mini-rank-info{min-width:0}.mini-rank-info b{display:block;color:#2A3D3C;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.mini-rank-info span{display:block;color:#6B8A88;font-weight:900;font-size:12px}.mini-empty-rank{text-align:center;color:#6B8A88;font-weight:900;padding:22px}.mini-reward-box{padding:24px;text-align:center}.mini-reward-box>div{font-size:3rem}.mini-reward-box h3{font-weight:1000;color:#2A3D3C;font-size:1.3rem;margin-top:6px}.mini-reward-box p{color:#6B8A88;font-weight:850;line-height:1.6;margin:8px 0 16px}.mini-reward-box a{display:inline-flex;background:#E8756A;color:#fff;border-radius:999px;padding:12px 16px;text-decoration:none;font-weight:1000;box-shadow:0 14px 32px rgba(232,117,106,.22)}
@media(max-width:1020px){.mini-hero-grid,.mini-layout-grid{grid-template-columns:1fr}.mini-side{position:static}.mini-hero-card{max-width:440px}.mini-games-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.mini-page-hero{padding:44px 0}.mini-games-grid{grid-template-columns:1fr}.mini-section-head{align-items:flex-start;flex-direction:column}.mini-title{font-size:3.4rem}.mini-hero-card{min-height:230px}.mini-main-icon{font-size:5rem}}
</style>
@endsection
