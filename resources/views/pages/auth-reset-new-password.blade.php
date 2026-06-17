@extends('layouts.app')
@section('title','Password Baru — SobatAnak')
@section('content')
@include('partials.auth-inline-style')

<section class="auth-split-section auth-split-login auth-page-fast">
    <div class="auth-split-orb auth-split-orb-one"></div>
    <div class="auth-split-orb auth-split-orb-two"></div>

    <div class="auth-split-card auth-animate-in">
        <div class="auth-split-form-panel">
            <div class="auth-mini-badge">Password Baru</div>
            <h1 class="auth-split-title">New Password</h1>
            <p class="auth-split-subtitle">Buat password baru, lalu konfirmasi password baru kamu.</p>

            @if($errors->any())
                <div class="auth-alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.new.update') }}" class="auth-modern-form auth-loading-form">
                @csrf
                <label class="auth-field-wrap">
                    <span class="auth-field-icon">🔒</span>
                    <input name="password" type="password" placeholder="Password baru minimal 6 karakter" required>
                </label>

                <label class="auth-field-wrap">
                    <span class="auth-field-icon">✅</span>
                    <input name="password_confirmation" type="password" placeholder="Konfirmasi password baru" required>
                </label>

                <button class="auth-submit-btn" type="submit" data-loading-text="Menyimpan...">Save New Password</button>
            </form>
        </div>

        <div class="auth-split-welcome-panel">
            <div class="auth-floating-dot dot-a">🔑</div>
            <div class="auth-floating-dot dot-b">🧸</div>
            <div class="auth-floating-dot dot-c">💚</div>
            <h2>Fresh Start!</h2>
            <p>Setelah berhasil, kamu akan diarahkan kembali ke halaman login.</p>
        </div>
    </div>
</section>

<script>
document.addEventListener('submit', function(e){
    const form = e.target;
    if(!form.classList || !form.classList.contains('auth-loading-form')) return;
    const btn = form.querySelector('button[type="submit"]');
    if(!btn) return;
    const txt = btn.getAttribute('data-loading-text');
    if(txt) btn.innerHTML = txt;
    btn.classList.add('is-loading');
});
</script>
@endsection
