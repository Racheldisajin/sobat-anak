@extends('layouts.app')
@section('title','Forgot Password — SobatAnak')
@section('content')
@include('partials.auth-inline-style')

<section class="auth-split-section auth-split-login auth-page-fast">
    <div class="auth-split-orb auth-split-orb-one"></div>
    <div class="auth-split-orb auth-split-orb-two"></div>

    <div class="auth-split-card auth-animate-in">
        <div class="auth-split-form-panel">
            <div class="auth-mini-badge">Reset Password</div>
            <h1 class="auth-split-title">Forgot?</h1>
            <p class="auth-split-subtitle">Masukkan Gmail akun kamu. Kami kirim kode reset untuk konfirmasi di website.</p>

            @if($errors->any())
                <div class="auth-alert-error">{{ $errors->first() }}</div>
            @endif
            @if(session('mail_warning'))
                <div class="auth-alert-soft">{{ session('mail_warning') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="auth-modern-form auth-loading-form">
                @csrf
                <label class="auth-field-wrap">
                    <span class="auth-field-icon">✉️</span>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="E-mail Gmail anda" required>
                </label>

                <button class="auth-submit-btn" type="submit" data-loading-text="Mengirim kode...">Send Code</button>
            </form>

            <p class="auth-switch-text">Ingat password? <a href="{{ route('login') }}">Back to Login</a></p>
        </div>

        <div class="auth-split-welcome-panel">
            <div class="auth-floating-dot dot-a">🔐</div>
            <div class="auth-floating-dot dot-b">🍼</div>
            <div class="auth-floating-dot dot-c">💌</div>
            <h2>No Worries!</h2>
            <p>Kode reset dikirim dulu, lalu kamu bisa buat password baru dan konfirmasi password baru.</p>
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
