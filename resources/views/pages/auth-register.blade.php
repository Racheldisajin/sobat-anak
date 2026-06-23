@extends('layouts.app')
@section('title','Register — SobatAnak')
@section('content')
@include('partials.auth-inline-style')

<section class="auth-split-section auth-split-register auth-page-fast">
    <div class="auth-split-orb auth-split-orb-one"></div>
    <div class="auth-split-orb auth-split-orb-two"></div>

    <div class="auth-split-card auth-split-card-register auth-animate-in">
        <div class="auth-split-welcome-panel auth-register-welcome">
            <div class="auth-floating-dot dot-a">🧸</div>
            <div class="auth-floating-dot dot-b">🍼</div>
            <div class="auth-floating-dot dot-c">🌈</div>
            <h2>Hello Friend!</h2>
            <p>Buat akun baru agar poin awal, cart, alamat, dan reward SobatAnak tersimpan khusus untuk kamu.</p>
        </div>

        <div class="auth-split-form-panel auth-register-form-panel">
            <div class="auth-mini-badge">Buat Akun</div>
            <h1 class="auth-split-title">Create Account</h1>
            <p class="auth-split-subtitle">Daftar sebentar saja, lalu kami mengirim verifikasi ke Gmail anda.</p>

            @if($errors->any())
                <div class="auth-alert-error">{{ $errors->first() }}</div>
            @endif
            @if(session('mail_warning'))
                <div class="auth-alert-soft">{{ session('mail_warning') }}</div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="auth-modern-form auth-loading-form">
                @csrf
                <label class="auth-field-wrap">
                    <span class="auth-field-icon">👤</span>
                    <input name="name" value="{{ old('name') }}" placeholder="Nama lengkap" required>
                </label>

                <label class="auth-field-wrap">
                    <span class="auth-field-icon">✉️</span>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="E-mail Gmail anda" required>
                </label>

                <label class="auth-field-wrap">
                    <span class="auth-field-icon">🔒</span>
                    <input name="password" type="password" placeholder="Password minimal 6 karakter" required>
                </label>

                <label class="auth-field-wrap">
                    <span class="auth-field-icon">✅</span>
                    <input name="password_confirmation" type="password" placeholder="Konfirmasi password" required>
                </label>

                <button class="auth-submit-btn" type="submit" data-loading-text="Mengirim kode...">Sign Up</button>
            </form>

            <p class="auth-switch-text">Sudah punya akun? <a href="{{ route('login') }}">Sign In</a></p>
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
