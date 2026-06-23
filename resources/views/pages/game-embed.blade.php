@extends('layouts.app')
@section('title',($game['title'] ?? 'Mini Game').' — SobatAnak')
@section('content')
<section class="game-embed-page">
    <div class="game-embed-topbar">
        <a href="{{ route('mini-games') }}" class="game-back">← Kembali ke daftar game</a>
        <div class="game-info">
            <span>{{ $game['icon'] ?? '🎮' }}</span>
            <div>
                <b>{{ $game['title'] ?? 'Mini Game' }}</b>
                <small>{{ $game['category'] ?? 'SobatAnak Game' }}</small>
            </div>
        </div>
        <div class="game-point">⭐ Poin Kamu: <b>{{ number_format($authPoints ?? 0,0,',','.') }}</b></div>
    </div>

    @if(!empty($game['available']))
        <div class="game-frame-wrap">
            <iframe src="{{ $game['src'] }}" title="{{ $game['title'] }}" allow="autoplay; fullscreen; gamepad; clipboard-read; clipboard-write" loading="eager"></iframe>
        </div>
    @else
        <div class="game-missing-card">
            <div>🗂️</div>
            <h1>Folder game belum ditemukan</h1>
            <p>Pastikan folder <b>public/games/{{ $game['slug'] }}</b> ada dan punya file <b>index.html</b> atau <b>dist/index.html</b>.</p>
            <a href="{{ route('mini-games') }}" class="game-back game-back--solid">Kembali</a>
        </div>
    @endif
</section>
<style>
.game-embed-page{min-height:calc(100vh - 100px);background:linear-gradient(135deg,#fff7ef 0%,#f7fffd 52%,#fff9df 100%);padding:1.2rem}.game-embed-topbar{max-width:1500px;margin:0 auto 1rem;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:1rem}.game-back,.game-point{display:inline-flex;align-items:center;justify-content:center;width:max-content;border:1px solid #bfece6;background:#fff;border-radius:999px;padding:.75rem 1rem;color:#263d3b;font-weight:1000;text-decoration:none;box-shadow:0 12px 26px rgba(42,61,60,.06)}.game-point{justify-self:end;border-color:#f6d89a;background:#fff9e9}.game-info{display:flex;align-items:center;gap:.7rem;justify-content:center;color:#263d3b;font-weight:1000}.game-info>span{width:3.2rem;height:3.2rem;border-radius:1rem;background:#fff;display:grid;place-items:center;font-size:1.8rem;border:1px solid #d7f0ed}.game-info small{display:block;color:#6b8a88;font-weight:900;margin-top:.1rem}.game-frame-wrap{max-width:1500px;margin:0 auto;border:12px solid #fff;border-radius:2rem;background:#fff;box-shadow:0 24px 70px rgba(42,61,60,.12);overflow:hidden;height:calc(100vh - 145px);min-height:620px}.game-frame-wrap iframe{width:100%;height:100%;border:0;display:block;background:#fff}.game-missing-card{max-width:680px;margin:4rem auto;background:#fff;border:1px solid #d7f0ed;border-radius:2rem;padding:2rem;text-align:center;box-shadow:0 22px 60px rgba(42,61,60,.10)}.game-missing-card>div{font-size:4rem}.game-missing-card h1{font-family:var(--font-display,inherit);font-size:2.5rem;color:#263d3b}.game-missing-card p{color:#6b8a88;font-weight:900;margin:1rem 0}.game-back--solid{background:#ef6f66;color:#fff;border-color:#ef6f66;margin:auto}@media(max-width:760px){.game-embed-page{padding:.7rem}.game-embed-topbar{grid-template-columns:1fr;justify-items:stretch}.game-back,.game-point{width:100%}.game-point{justify-self:stretch}.game-frame-wrap{border-width:7px;border-radius:1.35rem;height:calc(100vh - 210px);min-height:540px}}
</style>
@endsection
