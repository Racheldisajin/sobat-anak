<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Admin — SobatAnak')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-aja.png') }}">
    <link rel="stylesheet" href="{{ asset('css/sobatanak.css') }}">
    <style>
    *{box-sizing:border-box}
    body{margin:0;font-family:'Baloo 2',system-ui,sans-serif;background:#EEF7F6;color:#263D3B}
    :root{
        --adm-teal:#49C5B6;--adm-teal2:#3AA89A;
        --adm-dark:#263D3B;--adm-muted:#6B8A88;
        --adm-coral:#EF7168;--adm-coral2:#D05A50;
        --adm-border:#D4EEEC;--adm-soft:#F8FFFD;
        --adm-sidebar-w:224px;
    }

    /* ── WRAP ── */
    .adm-wrap{display:flex;min-height:100vh}

    /* ── SIDEBAR ── */
    .adm-sidebar{
        width:var(--adm-sidebar-w);flex:0 0 var(--adm-sidebar-w);
        background:linear-gradient(180deg,#1b3330 0%,#22403c 100%);
        position:fixed;top:0;left:0;height:100vh;
        display:flex;flex-direction:column;
        overflow-y:auto;overflow-x:hidden;
        z-index:100;
        scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent;
    }
    .adm-sidebar::-webkit-scrollbar{width:4px}
    .adm-sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:4px}

    .adm-brand{display:flex;align-items:center;gap:.75rem;padding:1.35rem 1.1rem 1.05rem;border-bottom:1px solid rgba(255,255,255,.07)}
    .adm-brand-logo{width:38px;height:38px;border-radius:.9rem;overflow:hidden;background:#fff;flex:0 0 38px;display:flex;align-items:center;justify-content:center}
    .adm-brand-logo img{width:32px;height:32px;object-fit:contain}
    .adm-brand-text b{display:block;font-family:'Fredoka',system-ui,sans-serif;font-size:1.08rem;font-weight:700;color:#fff;letter-spacing:.01em;line-height:1.2}
    .adm-brand-text small{display:block;font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.38)}

    .adm-nav-section{padding:.85rem 1.1rem .25rem;font-size:.62rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase;color:rgba(255,255,255,.3)}
    .adm-nav-link{
        display:flex;align-items:center;gap:.7rem;
        margin:.08rem .55rem;padding:.68rem .9rem;
        border-radius:.85rem;
        color:rgba(255,255,255,.65);font-weight:700;font-size:.86rem;
        text-decoration:none;transition:.16s ease;
        white-space:nowrap;overflow:hidden;
    }
    .adm-nav-link .icon{font-size:1rem;width:1.3rem;text-align:center;flex:0 0 auto;line-height:1}
    .adm-nav-link:hover{background:rgba(255,255,255,.09);color:#fff}
    .adm-nav-link.active{background:rgba(73,197,182,.22);color:#fff;border:1px solid rgba(73,197,182,.3)}
    .adm-nav-divider{height:1px;background:rgba(255,255,255,.06);margin:.45rem .8rem}

    .adm-sidebar-footer{margin-top:auto;padding:.9rem .7rem 1.1rem;border-top:1px solid rgba(255,255,255,.07)}
    .adm-visit-btn{
        display:flex;align-items:center;gap:.6rem;
        padding:.6rem .85rem;border-radius:.8rem;
        background:rgba(255,255,255,.08);
        color:rgba(255,255,255,.7);font-size:.82rem;font-weight:700;
        text-decoration:none;transition:.16s;
    }
    .adm-visit-btn:hover{background:rgba(255,255,255,.15);color:#fff}

    /* ── MAIN ── */
    .adm-main{margin-left:var(--adm-sidebar-w);flex:1;min-width:0;display:flex;flex-direction:column}

    /* ── TOPBAR ── */
    .adm-topbar{
        display:flex;align-items:center;justify-content:space-between;
        padding:.85rem 1.75rem;
        background:#fff;border-bottom:1px solid var(--adm-border);
        position:sticky;top:0;z-index:50;gap:1rem;
    }
    .adm-topbar-left{display:flex;align-items:center;gap:.75rem}
    .adm-topbar-title{font-family:'Fredoka',system-ui,sans-serif;font-size:1.1rem;font-weight:700;color:var(--adm-dark)}
    .adm-topbar-breadcrumb{font-size:.8rem;color:var(--adm-muted);font-weight:700}
    .adm-topbar-badge{background:#FEF3C7;color:#D97706;font-size:.68rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase;padding:.28rem .7rem;border-radius:999px;border:1px solid #FDE68A}

    /* ── PAGE CONTENT ── */
    .adm-content{padding:1.6rem 1.75rem 3rem;flex:1}

    /* ── Responsive ── */
    @media(max-width:900px){
        :root{--adm-sidebar-w:200px}
        .adm-topbar{padding:.75rem 1.25rem}
        .adm-content{padding:1.25rem}
    }
    @media(max-width:700px){
        .adm-sidebar{display:none}
        .adm-main{margin-left:0}
    }
    </style>
    @stack('styles')
</head>
<body>
<div class="adm-wrap">

    {{-- ── SIDEBAR ── --}}
    <aside class="adm-sidebar" id="admSidebar">
        <div class="adm-brand">
            <div class="adm-brand-logo">
                <img src="{{ asset('images/logo-cropped.png') }}" alt="SobatAnak">
            </div>
            <div class="adm-brand-text">
                <b>SobatAnak</b>
                <small>Admin Panel</small>
            </div>
        </div>

        <div class="adm-nav-section">Utama</div>
        <a href="{{ route('admin.dashboard') }}" class="adm-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span> Dashboard
        </a>

        <div class="adm-nav-section">Produk</div>
        <a href="{{ route('admin.products') }}" class="adm-nav-link {{ request()->routeIs('admin.products') || request()->routeIs('admin.products.create') ? 'active' : '' }}">
            <span class="icon">🛍️</span> Produk
        </a>

        <div class="adm-nav-section">Konten</div>
        <a href="{{ route('admin.articles') }}" class="adm-nav-link {{ request()->routeIs('admin.articles') || request()->routeIs('admin.articles.create') || request()->routeIs('admin.articles.edit') ? 'active' : '' }}">
            <span class="icon">📝</span> Artikel
        </a>

        <div class="adm-nav-section">Lainnya</div>
        <a href="{{ route('admin.rewards') }}" class="adm-nav-link {{ request()->routeIs('admin.rewards') ? 'active' : '' }}">
            <span class="icon">🎁</span> Reward & Poin
        </a>
        <a href="{{ route('admin.games') }}" class="adm-nav-link {{ request()->routeIs('admin.games') ? 'active' : '' }}">
            <span class="icon">🎮</span> Setting Game
        </a>
        <a href="{{ route('admin.testimonials') }}" class="adm-nav-link {{ request()->routeIs('admin.testimonials') ? 'active' : '' }}">
            <span class="icon">💬</span> Ulasan
        </a>

        <div class="adm-nav-divider"></div>

        <div class="adm-sidebar-footer">
            <a href="{{ route('home') }}" class="adm-visit-btn">
                <span>🌐</span> Lihat Website
            </a>
        </div>
    </aside>

    {{-- ── MAIN ── --}}
    <div class="adm-main">
        {{-- Topbar --}}
        <div class="adm-topbar">
            <div class="adm-topbar-left">
                <span class="adm-topbar-title">@yield('page-title', 'Admin')</span>
                @hasSection('breadcrumb')
                    <span class="adm-topbar-breadcrumb">/ @yield('breadcrumb')</span>
                @endif
            </div>
            <span class="adm-topbar-badge">SobatAnak Admin</span>
        </div>

        {{-- Page Content --}}
        <div class="adm-content">
            @yield('admin-content')
        </div>
    </div>

</div>

<script src="{{ asset('js/sobatanak.js') }}"></script>
@stack('scripts')
</body>
</html>
