@extends('layouts.app')
@section('title','Profile — SobatAnak')
@section('content')
@php
    $avatarUrl = !empty($user->avatar) ? asset($user->avatar) : null;
    $initial = strtoupper(substr($user->name ?? 'U',0,1));
@endphp

<section class="bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-14">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Profile</span>
        <h1 class="font-display hero-title mt-3">Halo, <span class="text-teal">{{ $user->name }}</span></h1>
        <p class="text-[#6B8A88] font-bold mt-2">Data akun, poin, cart, reward, dan pengaturan profile ini khusus akun kamu.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-12 profile-page-polish profile-page-v2">
    @if(session('success'))
        <div class="profile-alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="profile-alert-error">{{ $errors->first() }}</div>
    @endif

    <div class="profile-main-grid">
        <div class="card profile-account-card p-6">
            <div class="profile-avatar-wrap">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" class="profile-photo-large" alt="{{ $user->name }}">
                @else
                    <div class="profile-avatar big">{{ $initial }}</div>
                @endif
            </div>

            <h2 class="font-display text-2xl mt-4">{{ $user->name }}</h2>
            <p class="text-[#6B8A88] font-bold">{{ $user->email }}</p>

            <div class="profile-mini-info mt-4">
                <span>👤 Nama bisa diganti</span>
                <span>🖼️ Foto profile bisa diupload</span>
                <span>🔒 Email tetap aman</span>
            </div>

            <div class="flex flex-wrap gap-3 mt-5">
                <a href="{{ route('cart.index') }}" class="btn-pill btn-teal">🛒 Buka Cart</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-pill btn-coral">Logout</button></form>
            </div>
        </div>

        <div class="card profile-edit-card p-7">
            <div class="profile-edit-head">
                <span class="text-coral font-black uppercase text-xs">Edit Profile</span>
                <h2 class="font-display text-3xl mt-2">Atur Identitas Kamu</h2>
                <p class="profile-muted mt-2">Ubah nama/nickname dan foto profile supaya akun SobatAnak lebih personal.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-edit-form mt-6">
                @csrf
                @method('PATCH')

                <label class="profile-field">
                    <span>Nama / Nickname</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required>
                </label>

                <label class="profile-field">
                    <span>Email</span>
                    <input type="email" value="{{ $user->email }}" disabled>
                    <small>Email dipakai untuk login, jadi tidak bisa diganti dari halaman ini.</small>
                </label>

                <label class="profile-field">
                    <span>Ganti Foto Profile</span>
                    <div class="profile-file-wrap">
                        <input type="file" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp">
                    </div>
                    <small>Format JPG, PNG, WEBP. Maksimal 2MB.</small>
                </label>

                @if($avatarUrl)
                    <label class="profile-remove-avatar">
                        <input type="checkbox" name="remove_avatar" value="1">
                        <span>Hapus foto profile sekarang</span>
                    </label>
                @endif

                <button class="btn-pill btn-teal profile-save-btn">Simpan Profile</button>
            </form>
        </div>

        <div class="profile-side-stack">
            <a href="{{ route('profile.rewards') }}" class="card p-6 profile-point-link">
                <span class="text-coral font-black uppercase text-xs">Poin Kamu</span>
                <h2 class="font-display text-5xl mt-3">⭐ {{ number_format($point->points,0,',','.') }}</h2>
                <p class="text-[#6B8A88] font-bold mt-3">Klik kartu ini untuk menukar poin dengan reward voucher.</p>
                <div class="profile-point-cta">Tukar Poin →</div>
            </a>

            <div class="card p-6">
                <span class="text-coral font-black uppercase text-xs">Cart Kamu</span>
                <h2 class="font-display text-5xl mt-3">🛍️ {{ $cartItems->sum('quantity') }}</h2>
                <p class="text-[#6B8A88] font-bold mt-3">Keranjang dipisah di halaman Cart supaya checkout tidak gabung dengan profile.</p>
                <a href="{{ route('cart.index') }}" class="btn-pill btn-coral mt-5">Lihat Cart</a>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-1 gap-5 mt-8">
        <div class="card p-6">
            <h2 class="font-display text-3xl mb-5">Reward Ditukar</h2>
            @forelse($claims as $claim)
                <div class="border-b border-[#D4EEEC] py-3">
                    <b>{{ $claim->reward_name }}</b>
                    <p class="text-sm text-[#6B8A88]">-{{ number_format($claim->points_used,0,',','.') }} poin · {{ $claim->created_at->format('d M Y H:i') }}</p>
                </div>
            @empty
                <p class="text-[#6B8A88] font-bold">Belum ada reward yang ditukar.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
