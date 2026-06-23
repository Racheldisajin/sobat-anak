@extends('layouts.app')
@section('title','Game Mewarnai — SobatAnak')
@section('content')
<section class="game-shell-page">
    <div class="game-shell-top">
        <a href="{{ route('mini-games') }}" class="game-back-link">← Kembali ke daftar game</a>
        <div class="game-point-chip">⭐ Poin Kamu: <b data-points>{{ number_format($authPoints ?? 0,0,',','.') }}</b></div>
    </div>
    <iframe class="game-frame" src="{{ asset('games/mewarnai/index.html') }}" title="Game Mewarnai SobatAnak" allow="fullscreen; autoplay"></iframe>
</section>
@include('partials.game-point-bridge', ['gameSlug' => 'mewarnai'])
@endsection

@push('game_styles')
<style>
    body{background:#fff7fb!important}
    main.min-h-screen{background:linear-gradient(180deg,#fff7fb 0%,#ffeaf4 58%,#ffffff 100%)!important}
    header.sticky,footer.site-footer{display:none!important}
    .game-shell-page{background:linear-gradient(180deg,#fff7fb 0%,#ffeaf4 58%,#ffffff 100%);min-height:100vh;padding:8px 18px 22px}
    .game-shell-top{max-width:1180px;margin:0 auto;padding:18px 0;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .game-back-link{display:inline-flex;align-items:center;border:1px solid #BFECE6;background:#fff;color:#2A3D3C;border-radius:999px;padding:10px 16px;font-weight:1000;text-decoration:none;box-shadow:0 10px 25px rgba(42,61,60,.06)}
    .game-back-link:hover{background:#EEFFFB}
    .game-point-chip{background:#fff;border:1px solid #f9a8d4;color:#2A3D3C;border-radius:999px;padding:10px 16px;font-weight:1000;box-shadow:0 10px 25px rgba(42,61,60,.06)}
    .game-frame{display:block;width:min(100%,1180px);height:calc(100vh - 110px);min-height:680px;margin:0 auto;border:0;border-radius:28px;background:#fff;box-shadow:0 24px 70px rgba(42,61,60,.12);overflow:hidden}
    @media(max-width:640px){.game-shell-page{padding:8px}.game-frame{height:calc(100vh - 125px);min-height:620px;border-radius:20px}}
</style>
@endpush
