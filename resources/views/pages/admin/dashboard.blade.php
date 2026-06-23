@extends('layouts.admin')
@section('title','Admin Dashboard — SobatAnak')
@section('page-title','Dashboard')

@push('styles')
<style>
.adm-kpi-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem}
.adm-kpi-card{background:#fff;border:1px solid var(--adm-border);border-radius:1.25rem;padding:1.2rem 1.3rem;box-shadow:0 6px 22px rgba(38,61,59,.06);position:relative;overflow:hidden;transition:.2s}
.adm-kpi-card:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(38,61,59,.1)}
.adm-kpi-card::after{content:"";position:absolute;right:-1.8rem;bottom:-1.8rem;width:5.5rem;height:5.5rem;border-radius:999px;background:rgba(73,197,182,.07)}
.adm-kpi-card.featured{background:linear-gradient(135deg,#EDFCF9,#FFF8EC);border-color:#C2EDE7}
.adm-kpi-label{font-size:.72rem;font-weight:900;color:var(--adm-muted);text-transform:uppercase;letter-spacing:.08em}
.adm-kpi-value{font-family:'Fredoka',system-ui,sans-serif;font-size:clamp(1.5rem,2.2vw,2.1rem);font-weight:700;color:var(--adm-dark);line-height:1.1;margin:.3rem 0}
.adm-kpi-sub{font-size:.76rem;font-weight:800;color:var(--adm-muted)}

.adm-card{background:#fff;border:1px solid var(--adm-border);border-radius:1.25rem;box-shadow:0 6px 22px rgba(38,61,59,.06);padding:1.4rem}
.adm-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem}
.adm-card-head h2{font-family:'Fredoka',system-ui,sans-serif;font-size:1.15rem;font-weight:700;color:var(--adm-dark);margin:0}
.adm-card-head a{font-size:.8rem;font-weight:900;color:var(--adm-coral);text-decoration:none}
.adm-card-head a:hover{text-decoration:underline}

.adm-chart-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:.7rem}
.adm-chart-total{background:var(--adm-soft);border:1px solid var(--adm-border);border-radius:.8rem;padding:.55rem .85rem;text-align:right}
.adm-chart-total span{display:block;color:var(--adm-muted);font-weight:900;font-size:.7rem}
.adm-chart-total b{color:var(--adm-teal);font-weight:900}
.adm-chart-wrap{position:relative;background:linear-gradient(180deg,#fff,var(--adm-soft));border:1px solid #E8F6F4;border-radius:.9rem;padding:.55rem}
.adm-sales-svg{width:100%;height:190px}
.adm-chart-grid{stroke:#D4EEEC;stroke-width:1}
.adm-sales-area{fill:url(#admGradient)}
.adm-sales-line{fill:none;stroke:#49C5B6;stroke-width:3.5;stroke-linecap:round;stroke-linejoin:round}
.adm-sales-dot{fill:#fff;stroke:#49C5B6;stroke-width:3}
.adm-chart-labels{display:grid;grid-template-columns:repeat(7,1fr);color:var(--adm-muted);font-weight:900;font-size:.7rem;margin-top:.5rem;text-align:center}
.adm-chart-empty{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:rgba(255,255,255,.94);border:1px solid var(--adm-border);border-radius:.85rem;padding:.85rem 1rem;text-align:center;font-size:.82rem}
.adm-chart-empty b{display:block;color:var(--adm-dark);font-weight:900}
.adm-chart-empty span{color:var(--adm-muted);font-weight:800}

.adm-row{display:flex;align-items:center;gap:.8rem;padding:.7rem 0;border-bottom:1px solid #EDF7F5}
.adm-row:last-child{border-bottom:0}
.adm-thumb{width:48px;height:48px;object-fit:cover;border-radius:.85rem;background:#F5FBFA;border:1px solid #E1F2F0;flex:0 0 auto}
.adm-thumb-sm{width:38px;height:38px;border-radius:.7rem}
.adm-row-info{flex:1;min-width:0}
.adm-row-info b{display:block;font-weight:900;color:var(--adm-dark);font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.adm-row-info p{font-size:.76rem;color:var(--adm-muted);font-weight:800;margin-top:.1rem}
.adm-row-val{text-align:right;flex:0 0 auto}
.adm-row-val strong{display:block;font-weight:900;color:var(--adm-dark);font-size:.88rem}
.adm-row-val small{font-size:.73rem;color:var(--adm-muted);font-weight:800}
.adm-user-avatar{width:36px;height:36px;border-radius:999px;background:linear-gradient(135deg,#EEFFFB,#D0F0ED);color:#10A99D;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.88rem;flex:0 0 auto}
.adm-empty{background:#F8FFFD;border:1px dashed #BFECE6;border-radius:.85rem;padding:.9rem 1rem;font-size:.83rem;color:var(--adm-muted);font-weight:800}
.adm-stock-badge{display:inline-block;font-size:.68rem;font-weight:900;padding:.18rem .5rem;border-radius:999px}
.adm-stock-badge.empty{background:#FEE2E2;color:#DC2626}
.adm-stock-badge.low{background:#FEF3C7;color:#D97706}

.adm-bottom-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.2rem;margin-top:1.2rem}
@media(max-width:1100px){.adm-kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){.adm-kpi-grid,.adm-bottom-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('admin-content')

{{-- KPI --}}
<div class="adm-kpi-grid">
    <div class="adm-kpi-card featured">
        <div class="adm-kpi-label">Estimasi Penjualan</div>
        <div class="adm-kpi-value">Rp {{ number_format($stats['estimated_revenue'],0,',','.') }}</div>
        <div class="adm-kpi-sub">Dari order yang sudah paid</div>
    </div>
    <div class="adm-kpi-card">
        <div class="adm-kpi-label">Total Terjual</div>
        <div class="adm-kpi-value">{{ number_format($stats['sales_qty'],0,',','.') }}</div>
        <div class="adm-kpi-sub">Unit produk checkout/paid</div>
    </div>
    <div class="adm-kpi-card">
        <div class="adm-kpi-label">Produk</div>
        <div class="adm-kpi-value">{{ number_format($stats['products'],0,',','.') }}</div>
        <div class="adm-kpi-sub">{{ $stats['low_stock'] }} tipis · {{ $stats['out_stock'] }} habis</div>
    </div>
    <div class="adm-kpi-card">
        <div class="adm-kpi-label">Artikel</div>
        <div class="adm-kpi-value">{{ number_format($stats['articles'],0,',','.') }}</div>
        <div class="adm-kpi-sub">{{ $stats['published_articles'] }} publish · {{ $stats['draft_articles'] }} draft</div>
    </div>
    <div class="adm-kpi-card">
        <div class="adm-kpi-label">Pengguna</div>
        <div class="adm-kpi-value">{{ number_format($stats['users'],0,',','.') }}</div>
        <div class="adm-kpi-sub">Akun terdaftar</div>
    </div>
    <div class="adm-kpi-card">
        <div class="adm-kpi-label">Rating Rata-rata</div>
        <div class="adm-kpi-value">⭐ {{ number_format($stats['average_rating'],1) }}</div>
        <div class="adm-kpi-sub">{{ number_format($stats['reviews'],0,',','.') }} ulasan</div>
    </div>
</div>

{{-- Grafik Penjualan --}}
<div class="adm-card" style="margin-bottom:1.2rem">
    <div class="adm-chart-meta">
        <div>
            <h2 style="font-family:'Fredoka',system-ui,sans-serif;font-size:1.1rem;font-weight:700;color:var(--adm-dark);margin:0">Grafik Penjualan</h2>
            <p style="font-size:.76rem;color:var(--adm-muted);font-weight:800;margin:.15rem 0 0">7 hari terakhir — data real dari order paid</p>
        </div>
        <div class="adm-chart-total">
            <span>7 Hari Terakhir</span>
            <b>Rp {{ number_format(array_sum($salesChart['values']),0,',','.') }}</b>
        </div>
    </div>
    @php
        $pts=[]; $areaPts=[];
        $w=700;$h=190;$pX=28;$pY=20;
        $uW=$w-($pX*2);$uH=$h-($pY*2);
        $cnt=max(count($salesChart['values']),1);
        $mx=$salesChart['max']?:1;
        foreach($salesChart['values'] as $i=>$v){
            $x=$cnt<=1?$pX:$pX+(($uW/($cnt-1))*$i);
            $y=$pY+($uH-(($v/$mx)*$uH));
            $pts[]=round($x,2).','.round($y,2);
        }
        $areaPts=array_merge(["$pX,".($h-$pY)],$pts,[($w-$pX).','.($h-$pY)]);
        $emptyChart=array_sum($salesChart['values'])<=0;
    @endphp
    <div class="adm-chart-wrap">
        <svg class="adm-sales-svg" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none">
            <defs>
                <linearGradient id="admGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#49C5B6" stop-opacity=".26"/>
                    <stop offset="100%" stop-color="#49C5B6" stop-opacity=".02"/>
                </linearGradient>
            </defs>
            <line x1="{{ $pX }}" y1="{{ $h-$pY }}" x2="{{ $w-$pX }}" y2="{{ $h-$pY }}" class="adm-chart-grid"/>
            <line x1="{{ $pX }}" y1="{{ $pY+($uH*.5) }}" x2="{{ $w-$pX }}" y2="{{ $pY+($uH*.5) }}" class="adm-chart-grid"/>
            <line x1="{{ $pX }}" y1="{{ $pY }}" x2="{{ $w-$pX }}" y2="{{ $pY }}" class="adm-chart-grid"/>
            <polygon points="{{ implode(' ',$areaPts) }}" class="adm-sales-area"/>
            <polyline points="{{ implode(' ',$pts) }}" class="adm-sales-line"/>
            @foreach($pts as $pt)
                @php
                    $coord = explode(',', $pt);
                    $cx = $coord[0] ?? 0;
                    $cy = $coord[1] ?? 0;
                @endphp
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3.5" class="adm-sales-dot"/>
            @endforeach
        </svg>
        @if($emptyChart)
        <div class="adm-chart-empty"><b>Belum ada transaksi.</b><span>Grafik terisi otomatis setelah ada order paid.</span></div>
        @endif
    </div>
    <div class="adm-chart-labels">
        @foreach($salesChart['labels'] as $lbl)<span>{{ $lbl }}</span>@endforeach
    </div>
</div>

{{-- Monitoring Penjualan --}}
<div class="adm-card" style="margin-bottom:1.2rem">
    <div class="adm-card-head">
        <h2>Monitoring Penjualan</h2>
        <a href="{{ route('admin.products') }}">Kelola Produk →</a>
    </div>
    @forelse($topSellingProducts as $product)
    <div class="adm-row">
        <img src="{{ $product->image }}" class="adm-thumb" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
        <div class="adm-row-info">
            <b>{{ $product->name }}</b>
            <p>{{ $product->category }} · {{ number_format($product->real_sold??0,0,',','.') }} terjual</p>
        </div>
        <div class="adm-row-val">
            <strong>Rp {{ number_format($product->real_revenue??0,0,',','.') }}</strong>
            <small>Stok {{ $product->stock??0 }}</small>
        </div>
    </div>
    @empty
    <div class="adm-empty">Belum ada penjualan real. Data muncul setelah ada order paid.</div>
    @endforelse
</div>

{{-- 3-col bottom --}}
<div class="adm-bottom-grid">
    <div class="adm-card">
        <div class="adm-card-head"><h2>Stok Perlu Dicek</h2><a href="{{ route('admin.products') }}">Lihat →</a></div>
        @forelse($lowStockProducts as $product)
        <div class="adm-row">
            <img src="{{ $product->image }}" class="adm-thumb adm-thumb-sm" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
            <div class="adm-row-info">
                <b>{{ \Illuminate\Support\Str::limit($product->name,28) }}</b>
                <p>{{ $product->category }}</p>
            </div>
            <span class="adm-stock-badge {{ ($product->stock??0)<=0?'empty':'low' }}">
                {{ ($product->stock??0)<=0?'Habis':'Stok '.($product->stock) }}
            </span>
        </div>
        @empty <div class="adm-empty">Semua stok aman ✅</div>
        @endforelse
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><h2>Artikel Terbaru</h2><a href="{{ route('admin.articles') }}">Kelola →</a></div>
        @forelse($latestArticles as $article)
        <div class="adm-row">
            <img src="{{ $article->image }}" class="adm-thumb adm-thumb-sm" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
            <div class="adm-row-info">
                <b>{{ \Illuminate\Support\Str::limit($article->title,32) }}</b>
                <p>{{ $article->category_name }} · {{ strtoupper($article->status??'draft') }}</p>
            </div>
        </div>
        @empty <div class="adm-empty">Belum ada artikel.</div>
        @endforelse
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><h2>Pengguna Terbaru</h2></div>
        @forelse($latestUsers as $user)
        <div class="adm-row">
            @if(!empty($user->avatar))
                <img src="{{ asset($user->avatar) }}" class="adm-user-avatar" style="object-fit:cover;border-radius:999px" alt="">
            @else
                <span class="adm-user-avatar">{{ strtoupper(substr($user->name,0,1)) }}</span>
            @endif
            <div class="adm-row-info">
                <b>{{ $user->name }}</b>
                <p>{{ \Illuminate\Support\Str::limit($user->email,26) }} · {{ strtoupper($user->role??'user') }}</p>
            </div>
        </div>
        @empty <div class="adm-empty">Belum ada pengguna.</div>
        @endforelse
    </div>
</div>

@endsection
