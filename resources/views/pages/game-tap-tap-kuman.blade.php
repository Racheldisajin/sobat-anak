@extends('layouts.app')
@section('title','TapTap Kuman — SobatAnak')
@section('content')
<section class="game-shell-page">
    <div class="game-shell-top">
        <a href="{{ route('mini-games') }}" class="game-back-link">← Kembali ke daftar game</a>
        <div class="game-point-chip">⭐ Poin Kamu: <b data-points>{{ number_format($authPoints ?: 0,0,',','.') }}</b></div>
    </div>
    <div id="root"></div>
</section>
@endsection

@push('game_assets')
<link rel="stylesheet" href="{{ asset('games/tap-tap-kuman/assets/index-tc834MW9.css') }}">
<script type="module" src="{{ asset('games/tap-tap-kuman/assets/index-BYQ8aDYL.js') }}"></script>
@endpush

@push('game_styles')
<style>
    body{background:#fff7ed!important}
    main.min-h-screen{background:linear-gradient(180deg,#fff7ed 0%,#fff3e6 58%,#ffffff 100%)!important}
    header.sticky,footer.site-footer{display:none!important}
    .game-shell-page{background:linear-gradient(180deg,#fff7ed 0%,#fff3e6 58%,#ffffff 100%);min-height:100vh;padding-top:8px}
    .game-shell-top{max-width:1100px;margin:0 auto;padding:18px 18px 0;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .game-back-link{display:inline-flex;align-items:center;border:1px solid #BFECE6;background:#fff;color:#2A3D3C;border-radius:999px;padding:10px 16px;font-weight:1000;text-decoration:none;box-shadow:0 10px 25px rgba(42,61,60,.06)}
    .game-back-link:hover{background:#EEFFFB}
    .game-point-chip{background:#fff;border:1px solid #F8D4A7;color:#2A3D3C;border-radius:999px;padding:10px 16px;font-weight:1000;box-shadow:0 10px 25px rgba(42,61,60,.06)}
    #root{min-height:720px}
    #app_root{background:transparent!important;padding-top:10px!important}
</style>
@endpush
