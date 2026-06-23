<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','SobatAnak — Mom & Baby Care')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-aja.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-aja.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-aja.png') }}">

    <style>
        html{background:#F6FFFD}body{margin:0;background:#F6FFFD;font-family:'Baloo 2',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#263f3e}#sa-page-loader{position:fixed;inset:0;z-index:999999;display:grid;place-items:center;background:linear-gradient(135deg,#f7fffd 0%,#eefcf9 45%,#fff7ef 100%);transition:opacity .28s ease,visibility .28s ease}.sa-loader-card{display:flex;align-items:center;gap:14px;padding:16px 22px;border-radius:999px;background:rgba(255,255,255,.86);border:1px solid rgba(75,191,176,.28);box-shadow:0 22px 60px rgba(75,191,176,.18);font-weight:900;color:#263f3e}.sa-loader-logo{width:38px;height:38px;border-radius:16px;display:grid;place-items:center;background:#D0F0ED;animation:saBounce .85s ease-in-out infinite alternate}.sa-loader-dots{display:flex;gap:5px}.sa-loader-dots i{width:6px;height:6px;border-radius:999px;background:#4BBFB0;display:block;animation:saDot 1s ease-in-out infinite}.sa-loader-dots i:nth-child(2){animation-delay:.15s}.sa-loader-dots i:nth-child(3){animation-delay:.3s}.sa-page-loading #sa-page-shell,.sa-page-leaving #sa-page-shell{opacity:0;transform:translateY(8px)}#sa-page-shell{opacity:1;transform:none;transition:opacity .22s ease,transform .22s ease}.sa-page-ready #sa-page-loader{opacity:0;visibility:hidden;pointer-events:none}.sa-page-leaving #sa-page-loader{opacity:1;visibility:visible;pointer-events:auto}@keyframes saBounce{from{transform:translateY(0) rotate(-4deg)}to{transform:translateY(-5px) rotate(4deg)}}@keyframes saDot{0%,100%{opacity:.35;transform:translateY(0)}50%{opacity:1;transform:translateY(-3px)}}
    </style>

    <link rel="stylesheet" href="{{ asset('css/sobatanak.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sobatanak-ai-polish.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sobatanak-cart-fly-green.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sobatanak-page-transition.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sobatanak-confirm.css') }}">

    @stack('game_styles')
    @stack('game_assets')
</head>
<body class="sa-page-loading">
    <div id="sa-page-loader" aria-hidden="true">
        <div class="sa-loader-card">
            <span class="sa-loader-logo">🧸</span>
            <span>Memuat halaman SobatAnak</span>
            <span class="sa-loader-dots"><i></i><i></i><i></i></span>
        </div>
    </div>

    <main id="sa-page-shell" class="min-h-screen overflow-x-hidden">
        @includeWhen(!(request()->is('admin') || request()->is('admin/*')), 'partials.header')

        @if(session('success'))
            <div class="max-w-7xl mx-auto px-6 md:px-12 pt-4">
                <div class="rounded-2xl bg-[#D0F0ED] border border-[#4BBFB0] px-5 py-3 font-black text-[#2A3D3C]">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @yield('content')

        @include('partials.footer')
    </main>

    <script src="{{ asset('js/sobatanak.js') }}"></script>
    <script src="{{ asset('js/sobatanak-cart-fly-green.js') }}"></script>
    <script src="{{ asset('js/sobatanak-page-transition.js') }}"></script>
    <script src="{{ asset('js/sobatanak-confirm.js') }}"></script>
</body>
</html>
