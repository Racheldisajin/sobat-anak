@extends('layouts.app')
@section('title','Kode Reset Password — SobatAnak')
@section('content')
@include('partials.auth-inline-style')

<section class="auth-split-section auth-split-login auth-page-fast">
    <div class="auth-split-orb auth-split-orb-one"></div>
    <div class="auth-split-orb auth-split-orb-two"></div>

    <div class="auth-split-card auth-animate-in">
        <div class="auth-split-form-panel">
            <div class="auth-mini-badge">Kode Reset</div>
            <h1 class="auth-split-title">Enter Code</h1>
            <p class="auth-split-subtitle">Masukkan kode OTP 6 digit dari Gmail kamu untuk lanjut membuat password baru.</p>

            @if($errors->any())
                <div class="auth-alert-error">{{ $errors->first() }}</div>
            @endif
            @if(session('mail_warning'))
                <div class="auth-alert-soft">{{ session('mail_warning') }}</div>
            @endif

            <form method="POST" action="{{ route('password.code.verify') }}" class="auth-modern-form auth-loading-form">
                @csrf
                <label class="auth-field-wrap auth-code-wrap">
                    <span class="auth-field-icon">🔐</span>
                    <input name="code" inputmode="numeric" maxlength="6" placeholder="Kode OTP 6 digit" required autofocus>
                </label>

                <button class="auth-submit-btn" type="submit" data-loading-text="Mengecek kode...">Confirm Code</button>
            </form>

            <p class="auth-switch-text">Salah email? <a href="{{ route('password.request') }}">Kirim ulang</a></p>
        </div>

        <div class="auth-split-welcome-panel">
            <div class="auth-floating-dot dot-a">💌</div>
            <div class="auth-floating-dot dot-b">🍼</div>
            <div class="auth-floating-dot dot-c">✨</div>
            <h2>Secure Step!</h2>
            <p>Kode OTP ini memastikan hanya pemilik Gmail yang bisa mengganti password akun.</p>
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
