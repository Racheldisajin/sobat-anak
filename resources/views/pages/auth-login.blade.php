@extends('layouts.app')
@section('title','Login — SobatAnak')
@section('content')
@include('partials.auth-inline-style')

<section class="auth-split-section auth-split-login auth-page-fast">
    <div class="auth-split-orb auth-split-orb-one"></div>
    <div class="auth-split-orb auth-split-orb-two"></div>

    <div class="auth-split-card auth-animate-in">
        <div class="auth-split-form-panel">
            <div class="auth-mini-badge">Masuk Akun</div>
            <h1 class="auth-split-title">Hello!</h1>
            <p class="auth-split-subtitle">Masuk ke akun SobatAnak untuk menyimpan poin, keranjang, dan reward kamu.</p>

            @if($errors->any())
                <div class="auth-alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="auth-modern-form auth-loading-form">
                @csrf
                <label class="auth-field-wrap">
                    <span class="auth-field-icon">✉️</span>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="E-mail" required>
                </label>

                <label class="auth-field-wrap">
                    <span class="auth-field-icon">🔒</span>
                    <input name="password" type="password" placeholder="Password" required>
                </label>

                <div class="auth-form-options">
                    <label class="auth-remember">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                </div>

                <button class="auth-submit-btn" type="submit" data-loading-text="Masuk...">Sign In</button>
            </form>

            <p class="auth-switch-text">Belum punya akun? <a href="{{ route('register') }}">Create</a></p>
        </div>

        <div class="auth-split-welcome-panel">
            <div class="auth-floating-dot dot-a">🍼</div>
            <div class="auth-floating-dot dot-b">🧸</div>
            <div class="auth-floating-dot dot-c">💛</div>
            <h2>Welcome Back!</h2>
            <p>Selamat datang kembali di ruang belanja mom & baby care yang ramah, ceria, dan aman untuk keluarga.</p>
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
