@extends('layouts.app')
@section('title','Konfirmasi Pembayaran — SobatAnak')
@section('content')
@php
  $shipping = is_array($order->shipping_snapshot) ? $order->shipping_snapshot : [];
  $isPayable = in_array($order->status, ['pending','challenge'], true) && !empty($order->snap_token);
  $expiredAtIso = optional($order->expired_at)->toIso8601String();
  $statusIcon = $order->status === 'paid' ? '✅' : ($order->status === 'expired' ? '⏰' : '⌛');
  $shipName = trim(($shipping['courier_label'] ?? 'Kurir') . ' ' . ($shipping['service'] ?? ''));
  $locationUrl = $shipping['location_url'] ?? '';
  $locationLat = $shipping['latitude'] ?? '';
  $locationLng = $shipping['longitude'] ?? '';
  $mapQuery = trim($locationLat . ',' . $locationLng, ',');
  if (!$mapQuery && $locationUrl) { $mapQuery = $locationUrl; }
  $mapEmbedUrl = $mapQuery ? 'https://maps.google.com/maps?q=' . urlencode($mapQuery) . '&z=16&output=embed' : '';
@endphp

<section class="payment-hero bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-14">
  <div class="max-w-7xl mx-auto px-6 md:px-12">
    <span class="text-coral font-black uppercase tracking-widest text-xs">Payment</span>
    <h1 class="font-display hero-title mt-3">Konfirmasi <span class="text-teal">Pembayaran</span></h1>
    <p class="text-[#6B8A88] font-bold mt-2">Cek pesananmu, lalu lanjut bayar lewat Midtrans.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-12 payment-clean-page">
  @if(session('success'))
    <div class="payment-alert success">{{ session('success') }}</div>
  @endif

  <div class="payment-clean-grid">
    <div class="payment-clean-left">
      <div class="clean-status {{ $order->status_tone }}">
        <span>{{ $statusIcon }}</span>
        <div>
          <small>Status Pesanan</small>
          <b id="paymentStatusTitle">{{ $order->status_label }}</b>
          @if(in_array($order->status, ['pending','challenge'], true))
            <em>Bayar sebelum {{ optional($order->expired_at)->format('d M Y H:i') }} • <strong id="countdownText">--:--</strong></em>
          @endif
        </div>
      </div>

      <div class="clean-card">
        <p class="clean-kicker">Produk</p>
        <h2>Barang yang dibeli</h2>
        <div class="clean-products">
          @foreach($order->items as $item)
            <article>
              <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}">
              <div>
                <b>{{ $item->product_name }}</b>
                <span>{{ $item->quantity }} x Rp {{ number_format($item->price,0,',','.') }}</span>
              </div>
              <strong>Rp {{ number_format($item->line_total,0,',','.') }}</strong>
            </article>
          @endforeach
        </div>
      </div>

      <div class="clean-card clean-shipping-card">
        <p class="clean-kicker">Pengiriman</p>
        <h2>{{ $shipName ?: 'Jasa kirim' }}</h2>
        <div class="shipping-mini-grid">
          <div>
            <span>Ongkir</span>
            <b>Rp {{ number_format($order->shipping_cost,0,',','.') }}</b>
          </div>
          @if(!empty($shipping['etd']))
            <div>
              <span>Estimasi</span>
              <b>{{ $shipping['etd'] }}</b>
            </div>
          @endif
        </div>
        <div class="address-mini">
          <b>{{ $shipping['recipient_name'] ?? '-' }} • {{ $shipping['phone'] ?? '-' }}</b>
          <p>{{ $shipping['address'] ?? '-' }}, {{ $shipping['city'] ?? '-' }}, {{ $shipping['province'] ?? '-' }} {{ $shipping['postal_code'] ?? '' }}</p>
          @if(!empty($locationUrl))
            <a href="{{ $locationUrl }}" target="_blank" rel="noopener">📍 Buka lokasi rumah</a>
          @endif
        </div>
        @if(!empty($mapEmbedUrl))
          <div class="payment-map-preview">
            <iframe src="{{ $mapEmbedUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi rumah"></iframe>
          </div>
        @endif
      </div>
    </div>

    <aside class="clean-summary">
      <p class="clean-kicker">Total Pembayaran</p>
      <h2>Rp {{ number_format($order->total_amount,0,',','.') }}</h2>
      <div class="clean-lines">
        <div><span>Subtotal</span><b>Rp {{ number_format($order->subtotal,0,',','.') }}</b></div>
        <div><span>Ongkir</span><b>Rp {{ number_format($order->shipping_cost,0,',','.') }}</b></div>
        <div class="grand"><span>Total</span><b>Rp {{ number_format($order->total_amount,0,',','.') }}</b></div>
      </div>

      @if($order->status === 'paid')
        <div class="paid-box">Pembayaran berhasil.</div>
      @elseif(in_array($order->status, ['expired','failed','cancelled'], true))
        <div class="expired-box">Payment sudah tidak aktif.</div>
        <a href="{{ route('checkout') }}" class="clean-main-btn secondary">Buat payment baru</a>
      @else
        <button type="button" class="clean-main-btn" id="payNowBtn" {{ $isPayable ? '' : 'disabled' }}>Konfirmasi Bayar</button>
      @endif

      <button type="button" class="clean-check-btn" id="checkStatusBtn">Cek Status</button>
      <a href="{{ route('checkout') }}" class="clean-link">← Kembali ke Checkout</a>
      <a href="{{ route('products') }}" class="clean-products-link">Lihat Produk Lain</a>
      <div id="statusResult" class="status-result"></div>
    </aside>
  </div>
</section>

@if($isPayable)
  <script src="{{ $snapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
@endif

<style>
.payment-clean-page{background:#F7FBFA}.payment-alert{margin-bottom:1.25rem;border-radius:1.2rem;padding:1rem 1.15rem;font-weight:900}.payment-alert.success{background:#E9FAF0;border:1px solid #BFE7CB;color:#1C6E3B}.payment-clean-grid{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:1.4rem;align-items:start}.payment-clean-left{display:flex;flex-direction:column;gap:1rem}.clean-card,.clean-summary,.clean-status{background:#fff;border:1px solid var(--border);border-radius:1.4rem;box-shadow:0 18px 48px rgba(42,61,60,.07)}.clean-card,.clean-summary{padding:1.25rem}.clean-status{display:flex;align-items:center;gap:.85rem;padding:1rem;background:#FFF8E8;border-color:#F3D58B}.clean-status.paid{background:#E9FAF0;border-color:#BFE7CB}.clean-status.failed{background:#FDECEA;border-color:#F2C8C3}.clean-status>span{width:48px;height:48px;border-radius:999px;background:#fff;display:grid;place-items:center;font-size:1.35rem;box-shadow:0 10px 24px rgba(42,61,60,.08)}.clean-status small,.clean-kicker{display:block;font-size:.72rem;font-weight:1000;letter-spacing:.09em;text-transform:uppercase;color:var(--coral);margin:0}.clean-status b{display:block;font-size:1.45rem;font-weight:1000;color:var(--fg);line-height:1.15}.clean-status em{display:block;font-style:normal;color:var(--muted);font-weight:850;margin-top:.2rem}.clean-card h2,.clean-summary h2{font-size:1.55rem;font-weight:1000;color:var(--fg);margin:.2rem 0 1rem}.clean-summary h2{font-size:1.9rem;color:var(--coral)}.clean-products{display:flex;flex-direction:column;gap:.75rem}.clean-products article{display:grid;grid-template-columns:72px 1fr auto;gap:.8rem;align-items:center;border:1px solid #EAF4F3;border-radius:1rem;padding:.7rem;background:#FCFFFE}.clean-products img{width:72px;height:72px;border-radius:.85rem;object-fit:cover;background:#F6FAFA}.clean-products b{display:block;color:var(--fg);font-weight:1000}.clean-products span{display:block;color:var(--muted);font-weight:850;margin-top:.15rem}.clean-products strong{color:var(--teal2);font-weight:1000;white-space:nowrap}.shipping-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;margin-bottom:.85rem}.shipping-mini-grid div{border:1px solid #EAF4F3;border-radius:1rem;background:#FCFFFE;padding:.85rem}.shipping-mini-grid span{display:block;color:var(--muted);font-weight:850}.shipping-mini-grid b{display:block;color:var(--fg);font-weight:1000;margin-top:.15rem}.address-mini{border:1px solid #EAF4F3;border-radius:1rem;background:#F7FBFA;padding:.9rem}.address-mini b{display:block;color:var(--fg);font-weight:1000}.address-mini p{color:var(--muted);font-weight:850;line-height:1.45;margin:.35rem 0 0}.address-mini a{display:inline-flex;margin-top:.5rem;color:#2f9e92;font-weight:1000;text-decoration:none}.clean-summary{position:sticky;top:112px}.clean-lines{border:1px solid #EAF4F3;border-radius:1.1rem;overflow:hidden;margin:1rem 0}.clean-lines div{display:flex;justify-content:space-between;gap:1rem;padding:.9rem 1rem;border-bottom:1px solid #EAF4F3;font-weight:900;color:var(--muted)}.clean-lines div:last-child{border-bottom:0}.clean-lines b{text-align:right;color:var(--fg)}.clean-lines .grand{background:linear-gradient(135deg,#D0F0ED,#fff);font-weight:1000;color:var(--fg)}.clean-lines .grand b{color:var(--coral);font-size:1.15rem}.clean-main-btn,.clean-check-btn,.clean-products-link{width:100%;border:0;border-radius:999px;background:var(--coral);color:white!important;font-weight:1000;padding:1rem 1.15rem;box-shadow:0 16px 36px rgba(232,117,106,.23);transition:.22s;text-align:center;text-decoration:none;display:block}.clean-main-btn:disabled{opacity:.55;cursor:not-allowed}.clean-main-btn.secondary,.clean-check-btn,.clean-products-link{background:#4BBFB0}.clean-check-btn{margin-top:.75rem}.clean-link{display:flex;justify-content:center;margin:.85rem 0;color:#2f9e92;font-weight:1000;text-decoration:none}.status-result{font-weight:900;color:var(--muted);text-align:center;margin-top:.8rem;line-height:1.4}.paid-box,.expired-box{border-radius:1rem;padding:1rem;font-weight:1000;margin-bottom:.8rem}.paid-box{background:#E9FAF0;color:#1C6E3B;border:1px solid #BFE7CB}.expired-box{background:#FDECEA;color:#9E3C34;border:1px solid #F2C8C3}.payment-map-preview{margin-top:.9rem;border:1px solid #EAF4F3;border-radius:1rem;overflow:hidden;background:#F7FBFA;box-shadow:0 12px 28px rgba(42,61,60,.06)}.payment-map-preview iframe{display:block;width:100%;height:430px;border:0}@media(max-width:1024px){.payment-clean-grid{grid-template-columns:1fr}.clean-summary{position:relative;top:auto}}@media(max-width:640px){.clean-products article{grid-template-columns:62px 1fr}.clean-products strong{grid-column:2}.clean-products img{width:62px;height:62px}.shipping-mini-grid{grid-template-columns:1fr}.clean-card,.clean-summary,.clean-status{border-radius:1.2rem;padding:1rem}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const orderStatus = @json($order->status);
  const snapToken = @json($order->snap_token);
  const expiredAt = @json($expiredAtIso);
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
  const payBtn = document.getElementById('payNowBtn');
  const checkBtn = document.getElementById('checkStatusBtn');
  const resultBox = document.getElementById('statusResult');
  const countdownText = document.getElementById('countdownText');

  function writeResult(message){ if(resultBox) resultBox.textContent = message || ''; }

  async function refreshStatus(manual){
    if(checkBtn && manual){ checkBtn.disabled = true; checkBtn.textContent = 'Mengecek...'; }
    try{
      const response = await fetch('{{ route('orders.check-status', $order) }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({})
      });
      const data = await response.json();
      writeResult(data.message || 'Status diperbarui.');
      if(data.ok && ['paid','expired','failed','cancelled'].includes(data.status)){
        window.location.reload();
      }
    }catch(err){
      writeResult('Gagal cek status. Coba refresh halaman.');
    }finally{
      if(checkBtn && manual){ checkBtn.disabled = false; checkBtn.textContent = 'Cek Status'; }
    }
  }

  if(payBtn){
    payBtn.addEventListener('click', function(){
      if(!snapToken){ writeResult('Snap token belum tersedia. Kembali ke checkout dan buat order ulang.'); return; }
      if(!window.snap){ writeResult('Snap JS belum termuat. Cek koneksi internet / Client Key Midtrans.'); return; }
      window.snap.pay(snapToken, {
        language: 'id',
        onSuccess: function(){ writeResult('Pembayaran berhasil. Mengecek status...'); refreshStatus(false); },
        onPending: function(){ writeResult('Pembayaran masih pending. Selesaikan di popup Midtrans.'); refreshStatus(false); },
        onError: function(){ writeResult('Pembayaran gagal / belum selesai. Silakan coba lagi.'); refreshStatus(false); },
        onClose: function(){ writeResult('Popup Midtrans ditutup. Klik Konfirmasi Bayar lagi jika ingin lanjut.'); }
      });
    });
  }

  if(checkBtn){ checkBtn.addEventListener('click', function(){ refreshStatus(true); }); }

  function updateCountdown(){
    if(!expiredAt || !countdownText) return;
    const end = new Date(expiredAt).getTime();
    const diff = Math.max(0, end - Date.now());
    const minutes = Math.floor(diff / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);
    countdownText.textContent = String(minutes).padStart(2,'0') + ':' + String(seconds).padStart(2,'0');
    if(diff <= 0 && ['pending','challenge'].includes(orderStatus)){
      writeResult('Waktu pembayaran habis. Mengecek status...');
      refreshStatus(false);
      return;
    }
    setTimeout(updateCountdown, 1000);
  }
  updateCountdown();

  if(['pending','challenge'].includes(orderStatus)){
    setInterval(function(){ refreshStatus(false); }, 15000);
  }
});
</script>
@endsection
