@extends('layouts.app')
@section('title','Verifikasi Register — SobatAnak')
@section('content')
@include('partials.auth-inline-style')

@php($email = session('register_verify_email'))
<section class="auth-split-section auth-split-register auth-page-fast">
    <div class="auth-split-orb auth-split-orb-one"></div>
    <div class="auth-split-orb auth-split-orb-two"></div>

    <div class="auth-split-card auth-animate-in">
        <div class="auth-split-form-panel">
            <div class="auth-mini-badge">Verifikasi Gmail</div>
            <h1 class="auth-split-title">Verifikasi Akun</h1>
            <p class="auth-split-subtitle">Akun kamu sudah tercatat sebagai <b>Pending</b>. Cek Gmail{{ $email ? ': '.$email : '' }} lalu masukkan OTP 6 digit agar akun aktif sebagai user SobatAnak.</p>

            @if($errors->any())
                <div class="auth-alert-error">{{ $errors->first() }}</div>
            @endif
            @if(session('success'))
                <div class="auth-alert-soft">{{ session('success') }}</div>
            @endif
            @if(session('mail_warning'))
                <div class="auth-alert-soft">{{ session('mail_warning') }}</div>
            @endif

            <form method="POST" action="{{ route('register.verify.post') }}" class="auth-modern-form auth-loading-form">
                @csrf
                <label class="auth-field-wrap auth-code-wrap">
                    <span class="auth-field-icon">💌</span>
                    <input name="code" inputmode="numeric" maxlength="6" placeholder="Kode OTP 6 digit" required autofocus>
                </label>

                <button class="auth-submit-btn" type="submit" data-loading-text="Memverifikasi...">Verify Account</button>
            </form>

            <form method="POST" action="{{ route('register.resend') }}" class="auth-resend-form auth-loading-form">
                @csrf
                <button type="submit" data-loading-text="Mengirim ulang...">Kirim ulang kode</button>
            </form>
        </div>

        <div class="auth-split-welcome-panel">
            <div class="auth-floating-dot dot-a">✅</div>
            <div class="auth-floating-dot dot-b">🧸</div>
            <div class="auth-floating-dot dot-c">🌈</div>
            <h2>Almost Done!</h2>
            <p>Kalau akun masih Pending lalu kamu coba login, SobatAnak akan mengarahkan kamu ke halaman ini dan mengirim ulang OTP ke Gmail.</p>
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
