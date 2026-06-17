@extends('layouts.app')
@section('title','Admin Dashboard — SobatAnak')
@section('content')
<section class="admin-dashboard-hero simple">
    <div class="max-w-6xl mx-auto px-6 md:px-10">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Admin Dashboard</span>
        <div class="admin-dashboard-hero-row">
            <div>
                <h1 class="font-display hero-title mt-3">Dashboard <span class="text-teal">SobatAnak</span></h1>
                <p class="text-[#6B8A88] font-bold mt-2">Pantau penjualan, produk, stok, artikel, dan user secara ringkas.</p>
            </div>
            
        </div>

        <div class="admin-dashboard-actions">
            <a href="{{ route('admin.products') }}" class="btn-pill btn-teal">CRUD Produk</a>
            <a href="{{ route('admin.articles') }}" class="btn-pill btn-teal">CRUD Artikel</a>
            <a href="{{ route('admin.articles.create') }}" class="btn-pill btn-coral">+ Artikel Baru</a>
            <a href="{{ route('admin.rewards') }}" class="btn-pill bg-white border border-[#D4EEEC]">Reward</a>
            <a href="{{ route('home') }}" class="btn-pill admin-dashboard-visit">Lihat Website →</a>
        </div>
    </div>
</section>

<section class="max-w-6xl mx-auto px-6 md:px-10 py-10 admin-dashboard-page simple">
    <div class="admin-dashboard-kpi simple">
        <div class="admin-kpi-card primary">
            <span>Estimasi Penjualan</span>
            <strong>Rp {{ number_format($stats['estimated_revenue'],0,',','.') }}</strong>
            <small>Data real dari order/payment. Selama payment belum dibuat, nilainya 0.</small>
        </div>
        <div class="admin-kpi-card">
            <span>Total Terjual</span>
            <strong>{{ number_format($stats['sales_qty'],0,',','.') }}</strong>
            <small>Akumulasi produk yang benar-benar checkout/paid.</small>
        </div>
        <div class="admin-kpi-card">
            <span>Produk</span>
            <strong>{{ number_format($stats['products'],0,',','.') }}</strong>
            <small>{{ $stats['low_stock'] }} stok tipis · {{ $stats['out_stock'] }} habis</small>
        </div>
        <div class="admin-kpi-card">
            <span>Artikel</span>
            <strong>{{ number_format($stats['articles'],0,',','.') }}</strong>
            <small>{{ $stats['published_articles'] }} publish · {{ $stats['draft_articles'] }} draft</small>
        </div>
        <div class="admin-kpi-card">
            <span>User</span>
            <strong>{{ number_format($stats['users'],0,',','.') }}</strong>
            <small>Akun terdaftar.</small>
        </div>
        <div class="admin-kpi-card">
            <span>Review</span>
            <strong>{{ number_format($stats['reviews'],0,',','.') }}</strong>
            <small>Rata-rata rating {{ number_format($stats['average_rating'],1) }}</small>
        </div>
    </div>

    <div class="admin-sales-chart-card card p-6 mt-8">
        <div class="admin-chart-head">
            <div>
                <h2 class="font-display text-3xl">Grafik Penjualan</h2>
                <p>Data real dari order/payment. Kalau payment belum dibuat, grafik akan 0 dulu.</p>
            </div>
            <div class="admin-chart-total">
                <span>7 Hari Terakhir</span>
                <b>Rp {{ number_format(array_sum($salesChart['values']),0,',','.') }}</b>
            </div>
        </div>

        @php
            $points = [];
            $areaPoints = [];
            $width = 700;
            $height = 210;
            $paddingX = 34;
            $paddingY = 28;
            $usableW = $width - ($paddingX * 2);
            $usableH = $height - ($paddingY * 2);
            $count = max(count($salesChart['values']), 1);
            foreach($salesChart['values'] as $i => $value){
                $x = $count <= 1 ? $paddingX : $paddingX + (($usableW / ($count - 1)) * $i);
                $y = $paddingY + ($usableH - (($value / $salesChart['max']) * $usableH));
                $points[] = round($x, 2).','.round($y, 2);
            }
            $areaPoints = array_merge(["$paddingX,".($height - $paddingY)], $points, [($width - $paddingX).','.($height - $paddingY)]);
            $isEmptySalesChart = array_sum($salesChart['values']) <= 0;
        @endphp

        <div class="admin-sales-chart-wrap">
            <svg class="admin-sales-svg" viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" role="img" aria-label="Grafik penjualan 7 hari terakhir">
                <defs>
                    <linearGradient id="salesAreaGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#49C5B6" stop-opacity="0.32"/>
                        <stop offset="100%" stop-color="#49C5B6" stop-opacity="0.04"/>
                    </linearGradient>
                </defs>
                <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}" class="chart-grid-line"/>
                <line x1="{{ $paddingX }}" y1="{{ $paddingY + ($usableH * .5) }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY + ($usableH * .5) }}" class="chart-grid-line"/>
                <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY }}" class="chart-grid-line"/>

                <polygon points="{{ implode(' ', $areaPoints) }}" class="sales-area"/>
                <polyline points="{{ implode(' ', $points) }}" class="sales-line"/>
                @foreach($points as $point)
                    @php [$cx,$cy] = explode(',', $point); @endphp
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="4.5" class="sales-dot"/>
                @endforeach
            </svg>

            @if($isEmptySalesChart)
                <div class="admin-chart-empty-note">
                    <b>Belum ada transaksi real.</b>
                    <span>Grafik akan terisi otomatis setelah fitur payment/order aktif dan ada order paid.</span>
                </div>
            @endif
        </div>

        <div class="admin-chart-labels">
            @foreach($salesChart['labels'] as $label)
                <span>{{ $label }}</span>
            @endforeach
        </div>
    </div>

    <div class="admin-sales-grid one-col mt-8">
        <div class="card p-6 admin-dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-3xl">Monitoring Penjualan</h2>
                <a href="{{ route('admin.products') }}" class="text-coral font-black">Kelola →</a>
            </div>

            @forelse($topSellingProducts as $product)
                <div class="admin-sale-row">
                    <img src="{{ $product->image }}" class="admin-thumb" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
                    <div class="admin-sale-info">
                        <b>{{ $product->name }}</b>
                        <p>{{ $product->category }} · {{ number_format($product->real_sold ?? 0,0,',','.') }} terjual real</p>
                    </div>
                    <div class="admin-sale-value">
                        <strong>Rp {{ number_format($product->real_revenue ?? 0,0,',','.') }}</strong>
                        <small>Stok {{ $product->stock ?? 0 }}</small>
                    </div>
                </div>
            @empty
                <div class="admin-empty-sales">
                    <b>Belum ada penjualan real.</b>
                    <p>Karena payment/order belum dibuat, monitoring penjualan akan tampil 0 dulu. Setelah fitur payment aktif, data otomatis masuk dari order yang sudah paid.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mt-8">
        <div class="card p-6 admin-dashboard-card">
            <h2 class="font-display text-2xl mb-4">Stok Perlu Dicek</h2>
            @forelse($lowStockProducts as $product)
                <div class="admin-list compact">
                    <img src="{{ $product->image }}" class="admin-thumb small" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
                    <div>
                        <b>{{ $product->name }}</b>
                        <p class="{{ ($product->stock ?? 0) <= 0 ? 'text-coral' : '' }}">Stok {{ $product->stock ?? 0 }} · {{ $product->category }}</p>
                    </div>
                </div>
            @empty
                <p class="text-[#6B8A88] font-bold">Semua stok masih aman.</p>
            @endforelse
        </div>

        <div class="card p-6 admin-dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-2xl">Artikel Terbaru</h2>
                <a href="{{ route('admin.articles') }}" class="text-coral font-black">Kelola →</a>
            </div>
            @forelse($latestArticles as $article)
                <div class="admin-list compact">
                    <img src="{{ $article->image }}" class="admin-thumb small" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
                    <div>
                        <b>{{ \Illuminate\Support\Str::limit($article->title, 42) }}</b>
                        <p>{{ $article->category_name }} · {{ strtoupper($article->status ?? 'draft') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-[#6B8A88] font-bold">Belum ada artikel.</p>
            @endforelse
        </div>

        <div class="card p-6 admin-dashboard-card">
            <h2 class="font-display text-2xl mb-4">User Terbaru</h2>
            @forelse($latestUsers as $user)
                <div class="admin-list compact">
                    @if(!empty($user->avatar))
                        <img src="{{ asset($user->avatar) }}" class="profile-avatar admin-user-photo" alt="{{ $user->name }}">
                    @else
                        <span class="profile-avatar">{{ strtoupper(substr($user->name,0,1)) }}</span>
                    @endif
                    <div>
                        <b>{{ $user->name }}</b>
                        <p>{{ $user->email }} · {{ strtoupper($user->role ?? 'user') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-[#6B8A88] font-bold">Belum ada user.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
