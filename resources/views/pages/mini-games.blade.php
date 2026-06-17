@extends('layouts.app')
@section('title','Mini Game — SobatAnak')
@section('content')
<section class="bg-gradient-to-br from-[#FDECEA] via-white to-[#EEF4C0] py-14 md:py-16 overflow-hidden">
    <div class="max-w-6xl mx-auto px-6 md:px-12 grid md:grid-cols-[minmax(0,1.08fr)_minmax(260px,.72fr)] gap-6 lg:gap-10 items-center">
        <div>
            <span class="text-coral font-black uppercase tracking-widest text-xs">Mini Game</span>
            <h1 class="font-display hero-title mt-3 max-w-3xl">Main Seru, Dapat <span class="text-teal">Poin</span></h1>
            <p class="text-[#6B8A88] font-bold mt-4 max-w-2xl">Klik main pada game pilihan. Poin tersimpan otomatis di akun kamu, jadi tiap user nilainya berbeda.</p>
            <div class="btn-pill bg-yellow-100 border border-yellow-300 mt-6 w-fit">⭐ Poin Kamu: <span data-points>{{ number_format($authPoints ?: 0,0,',','.') }}</span></div>
        </div>

        <div class="relative flex justify-center md:justify-end">
            <div class="mini-hero-visual">
                <div class="text-center text-8xl md:text-9xl float leading-none">🎮</div>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-12">
    <h2 class="section-title font-display mb-8">Daftar <span class="text-teal">Game</span></h2>
    <div class="grid md:grid-cols-3 gap-5">
        @foreach([['Puzzle Edukatif','🧩','Susun puzzle dan latih konsentrasi si kecil.'],['Memory Card','🃏','Ingat pasangan kartu dengan cepat.'],['TapTap Kuman','🦠','Tap kuman lucu untuk mendapatkan poin.']] as $g)
        <div class="card p-6 text-center">
            <div class="text-7xl mb-4">{{ $g[1] }}</div>
            <h3 class="font-display text-2xl">{{ $g[0] }}</h3>
            <p class="text-[#6B8A88] my-4">{{ $g[2] }}</p>
            <button data-play-game class="btn-pill btn-coral mx-auto">Main Sekarang</button>
        </div>
        @endforeach
    </div>

    <div class="mini-reward-cta mt-16">
        <div>
            <span class="text-coral font-black uppercase tracking-widest text-xs">Reward Voucher</span>
            <h2 class="section-title font-display mt-2">Tukar <span class="text-teal">Reward</span></h2>
            <p>Kamu sudah kumpulkan poin dari mini game? Masuk ke halaman reward untuk pilih voucher, lihat poin tersedia, dan cek riwayat penukaran.</p>
        </div>
        <div class="mini-reward-actions">
            <div class="mini-point-pill">⭐ Poin Kamu: <b data-points-bottom>{{ number_format($authPoints ?: 0,0,',','.') }}</b></div>
            @if(session('user_id'))
                <a href="{{ route('profile.rewards') }}" class="btn-pill btn-coral">Tukar Voucher →</a>
            @else
                <a href="{{ route('login') }}" class="btn-pill btn-coral">Login untuk Tukar →</a>
            @endif
        </div>
    </div>
</section>

<style>

.mini-reward-cta{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1.5rem;
    border:1px solid #BFECE6;
    border-radius:2rem;
    background:linear-gradient(135deg,#EEFFFB 0%,#fff 48%,#FFF8EC 100%);
    padding:1.6rem;
    box-shadow:0 20px 55px rgba(42,61,60,.06);
}
.mini-reward-cta p{
    color:#6B8A88;
    font-weight:900;
    line-height:1.7;
    max-width:42rem;
    margin-top:.5rem;
}
.mini-reward-actions{
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:.85rem;
    min-width:220px;
}
.mini-point-pill{
    background:#fff;
    border:1px solid #D4EEEC;
    border-radius:999px;
    padding:.75rem 1rem;
    color:#263D3B;
    font-weight:1000;
    box-shadow:0 10px 25px rgba(42,61,60,.05);
}
@media (max-width: 767px){
    .mini-reward-cta{flex-direction:column;align-items:flex-start;padding:1.2rem}
    .mini-reward-actions{align-items:flex-start;width:100%;min-width:0}
    .mini-reward-actions .btn-pill{width:100%;justify-content:center}
}

.mini-hero-visual{
    width: min(100%, 320px);
    min-height: 240px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius: 32px;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.7), rgba(255,255,255,.2) 55%, rgba(255,255,255,0) 70%);
}
@media (max-width: 767px){
    
.mini-reward-cta{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1.5rem;
    border:1px solid #BFECE6;
    border-radius:2rem;
    background:linear-gradient(135deg,#EEFFFB 0%,#fff 48%,#FFF8EC 100%);
    padding:1.6rem;
    box-shadow:0 20px 55px rgba(42,61,60,.06);
}
.mini-reward-cta p{
    color:#6B8A88;
    font-weight:900;
    line-height:1.7;
    max-width:42rem;
    margin-top:.5rem;
}
.mini-reward-actions{
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:.85rem;
    min-width:220px;
}
.mini-point-pill{
    background:#fff;
    border:1px solid #D4EEEC;
    border-radius:999px;
    padding:.75rem 1rem;
    color:#263D3B;
    font-weight:1000;
    box-shadow:0 10px 25px rgba(42,61,60,.05);
}
@media (max-width: 767px){
    .mini-reward-cta{flex-direction:column;align-items:flex-start;padding:1.2rem}
    .mini-reward-actions{align-items:flex-start;width:100%;min-width:0}
    .mini-reward-actions .btn-pill{width:100%;justify-content:center}
}

.mini-hero-visual{width:100%;min-height:160px;margin-top:.5rem}
}
</style>

<script id="miniRewardsPointSync">
document.addEventListener('DOMContentLoaded', function(){
    const topPoint = document.querySelector('[data-points]');
    const bottomPoint = document.querySelector('[data-points-bottom]');
    if(!topPoint || !bottomPoint) return;

    const observer = new MutationObserver(() => {
        bottomPoint.textContent = topPoint.textContent;
    });

    observer.observe(topPoint, { childList:true, characterData:true, subtree:true });
});
</script>
@endsection
