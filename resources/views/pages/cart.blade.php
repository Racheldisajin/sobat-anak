@extends('layouts.app')
@section('title','Cart Checkout — SobatAnak')
@section('content')
<section class="cart-hero bg-gradient-to-br from-[#FDECEA] via-white to-[#D0F0ED] py-12">
  <div class="max-w-7xl mx-auto px-6 md:px-12">
    <span class="text-coral font-black uppercase tracking-widest text-xs">Keranjang</span>
    <h1 class="font-display hero-title mt-3">Cart <span class="text-teal">Checkout</span></h1>
    <p class="text-[#6B8A88] font-bold mt-2">Keranjang ini khusus akun {{ $user->name }} dan terpisah dari halaman profile.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-10 cart-page-modern" data-cart-page>
  @if(session('success'))
    <div class="cart-alert" data-cart-alert>{{ session('success') }}</div>
  @else
    <div class="cart-alert cart-alert-js" data-cart-alert hidden></div>
  @endif

  <div class="cart-layout-modern">
    <div class="cart-main-modern">
      <div class="cart-panel cart-panel-head">
        <div>
          <span class="cart-kicker">Belanjaan Kamu</span>
          <h2 class="font-display text-3xl">Isi Keranjang</h2>
        </div>
        <a href="{{ route('products') }}" class="cart-link-shop">+ Tambah Produk</a>
      </div>

      <div data-active-cart-list>
      @forelse($cartItems as $item)
        @php
          $price = $item->product->price ?? 0;
          $qty = $item->quantity;
          $lineTotal = $price * $qty;
          $stock = $item->product->stock ?? 99;
        @endphp
        <article class="cart-product-card" data-cart-item="{{ $item->id }}">
          <div class="cart-product-store">
            <span class="cart-store-icon">🛒</span>
            <div>
              <b>SobatAnak Official</b>
              <small>Produk mom & baby care pilihan</small>
            </div>
          </div>

          <div class="cart-product-body">
            <img src="{{ $item->product->image }}" class="cart-product-img" alt="{{ $item->product->name }}">

            <div class="cart-product-info">
              <h3>{{ $item->product->name }}</h3>
              <p class="cart-product-meta">Stok tersedia {{ $stock }} • Aman untuk keluarga • Siap checkout</p>
              <p class="cart-product-price">Rp {{ number_format($price,0,',','.') }}</p>
            </div>

            <div class="cart-product-actions">
              <p class="cart-line-total" data-line-total>Rp {{ number_format($lineTotal,0,',','.') }}</p>

              <div class="qty-stepper qty-stepper-live" aria-label="Atur jumlah produk">
                <button type="button" class="qty-btn" data-qty-minus data-url="{{ route('cart.update',$item) }}" {{ $qty <= 1 ? 'disabled' : '' }} aria-label="Kurangi jumlah">−</button>
                <input name="quantity" type="number" min="1" max="{{ max(1,$stock) }}" value="{{ $qty }}" class="qty-input-modern" data-qty-input data-url="{{ route('cart.update',$item) }}" aria-label="Jumlah produk">
                <button type="button" class="qty-btn" data-qty-plus data-url="{{ route('cart.update',$item) }}" {{ $qty >= $stock ? 'disabled' : '' }} aria-label="Tambah jumlah">+</button>
              </div>

              <div class="cart-action-row">
                <form method="POST" action="{{ route('cart.destroy',$item) }}" class="cart-delete-form">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="cart-delete-btn" data-delete-trigger data-product-name="{{ $item->product->name }}">Hapus</button>
                </form>
              </div>
            </div>
          </div>
        </article>
      @empty
        <div class="cart-empty-modern" data-cart-empty>
          <div class="cart-empty-icon">🛍️</div>
          <h2 class="font-display text-3xl">Cart masih kosong</h2>
          <p>Tambahkan produk dulu dari halaman Produk.</p>
          <a href="{{ route('products') }}" class="btn-pill btn-coral mt-5">Belanja Produk</a>
        </div>
      @endforelse
      </div>

    </div>

    <aside class="cart-summary-modern">
      <span class="cart-kicker">Ringkasan Belanja</span>
      <h3>Checkout</h3>

      <div class="summary-row-modern">
        <span>Total Produk</span>
        <b data-summary-items>{{ $cartItems->sum('quantity') }} item</b>
      </div>
      <div class="summary-row-modern">
        <span>Subtotal</span>
        <b data-summary-subtotal>Rp {{ number_format($subtotal,0,',','.') }}</b>
      </div>
      <div class="summary-row-modern muted">
        <span>Ongkir</span>
        <b>Belum dihitung</b>
      </div>

      <div class="summary-total-modern">
        <span>Total sementara</span>
        <b data-summary-total>Rp {{ number_format($subtotal,0,',','.') }}</b>
      </div>

      <p class="summary-note">Quantity langsung update tanpa refresh. Total belanja ikut berubah otomatis.</p>

      @if($cartItems->count())
        <a href="{{ route('checkout') }}" class="checkout-btn-modern" data-checkout-btn>Checkout Sekarang</a>
      @else
        <a href="{{ route('products') }}" class="checkout-btn-modern alt" data-checkout-btn>Belanja Dulu</a>
      @endif
    </aside>
  </div>
</section>

<div class="cart-confirm-overlay" id="cartConfirmOverlay" aria-hidden="true">
  <div class="cart-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="cartConfirmTitle">
    <button type="button" class="cart-confirm-close" id="cartConfirmClose" aria-label="Tutup">×</button>
    <div class="cart-confirm-icon">🛒</div>
    <p class="cart-confirm-kicker">Konfirmasi Keranjang</p>
    <h3 id="cartConfirmTitle">Hapus produk ini?</h3>
    <p id="cartConfirmText">Produk akan dihapus dari keranjang kamu.</p>
    <div class="cart-confirm-actions">
      <button type="button" class="cart-confirm-cancel" id="cartConfirmCancel">Batal</button>
      <button type="button" class="cart-confirm-ok" id="cartConfirmOk">Ya, Hapus</button>
    </div>
  </div>
</div>

<style>
.qty-stepper-live{display:flex;align-items:center;gap:8px;padding:8px;border-radius:999px;background:#f4fbfa;border:1px solid #d4eeec;box-shadow:inset 0 1px 0 rgba(255,255,255,.8)}
.qty-stepper-live .qty-btn{width:46px;height:46px;border-radius:50%;border:0;background:#fff;color:#38b8aa;font-size:26px;font-weight:1000;line-height:1;box-shadow:0 10px 24px rgba(37,77,73,.08);transition:.22s ease;display:grid;place-items:center}.qty-stepper-live .qty-btn:hover:not(:disabled){transform:translateY(-2px) scale(1.05);background:#48c2b6;color:#fff}.qty-stepper-live .qty-btn:disabled{opacity:.35;cursor:not-allowed}.qty-stepper-live .qty-input-modern{width:68px;text-align:center;border:0;background:transparent;font-size:22px;font-weight:1000;color:#263f3d;outline:0}.cart-alert-js{display:block}.cart-product-card.is-updating{opacity:.75;transform:scale(.995)}.cart-product-card.is-removing{animation:cartRemove .32s ease forwards}@keyframes cartRemove{to{opacity:0;transform:translateX(24px) scale(.96);max-height:0;margin:0;padding-top:0;padding-bottom:0}}.cart-toast-pop{animation:cartToastPop .34s ease}@keyframes cartToastPop{0%{opacity:0;transform:translateY(-10px) scale(.96)}100%{opacity:1;transform:translateY(0) scale(1)}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
  const alertBox = document.querySelector('[data-cart-alert]');
  const summaryItems = document.querySelector('[data-summary-items]');
  const summarySubtotal = document.querySelector('[data-summary-subtotal]');
  const summaryTotal = document.querySelector('[data-summary-total]');
  const headerCartCount = document.querySelector('[data-cart-count]');
  const checkoutBtn = document.querySelector('[data-checkout-btn]');

  function rupiah(num){ return 'Rp ' + Number(num || 0).toLocaleString('id-ID'); }
  function showMessage(message){
    if(!alertBox) return;
    alertBox.hidden = false;
    alertBox.textContent = message || 'Keranjang berhasil diperbarui.';
    alertBox.classList.remove('cart-toast-pop');
    void alertBox.offsetWidth;
    alertBox.classList.add('cart-toast-pop');
  }
  function updateSummary(data){
    if(!data) return;
    if(summaryItems) summaryItems.textContent = data.total_items_label || ((data.cart_count || 0) + ' item');
    if(summarySubtotal) summarySubtotal.textContent = data.subtotal_formatted || rupiah(data.subtotal);
    if(summaryTotal) summaryTotal.textContent = data.subtotal_formatted || rupiah(data.subtotal);
    if(headerCartCount) headerCartCount.textContent = data.cart_count ?? 0;
    if(checkoutBtn && Number(data.cart_count || 0) <= 0){
      checkoutBtn.textContent = 'Belanja Dulu';
      checkoutBtn.href = '{{ route('products') }}';
      checkoutBtn.classList.add('alt');
    }
  }
  async function sendCart(url, method, body){
    const response = await fetch(url, {
      method: method || 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: body ? JSON.stringify(body) : null
    });
    const data = await response.json().catch(() => ({}));
    if(!response.ok || data.ok === false){ throw new Error(data.message || 'Keranjang gagal diperbarui.'); }
    return data;
  }
  async function updateQty(card, nextQty){
    const input = card.querySelector('[data-qty-input]');
    const url = input?.dataset.url;
    if(!url) return;
    const max = Number(input.getAttribute('max') || 99);
    const qty = Math.max(1, Math.min(max, Number(nextQty || 1)));
    card.classList.add('is-updating');
    try{
      const data = await sendCart(url, 'PATCH', {quantity: qty});
      if(data.removed){
        removeCard(card);
      } else {
        input.value = data.quantity || qty;
        const minus = card.querySelector('[data-qty-minus]');
        const plus = card.querySelector('[data-qty-plus]');
        if(minus) minus.disabled = Number(input.value) <= 1;
        if(plus) plus.disabled = Number(input.value) >= max;
        const lineTotal = card.querySelector('[data-line-total]');
        if(lineTotal) lineTotal.textContent = data.line_total_formatted || rupiah(data.line_total);
      }
      updateSummary(data);
      showMessage(data.message);
    }catch(error){
      showMessage(error.message);
    }finally{
      card.classList.remove('is-updating');
    }
  }
  function removeCard(card){
    card.classList.add('is-removing');
    setTimeout(() => card.remove(), 320);
  }

  document.querySelectorAll('[data-cart-item]').forEach(function(card){
    const input = card.querySelector('[data-qty-input]');
    const minus = card.querySelector('[data-qty-minus]');
    const plus = card.querySelector('[data-qty-plus]');
    if(minus) minus.addEventListener('click', () => updateQty(card, Number(input.value) - 1));
    if(plus) plus.addEventListener('click', () => updateQty(card, Number(input.value) + 1));
    if(input){
      input.addEventListener('change', () => updateQty(card, input.value));
      input.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); updateQty(card, input.value); } });
    }
  });


  const overlay = document.getElementById('cartConfirmOverlay');
  const okBtn = document.getElementById('cartConfirmOk');
  const cancelBtn = document.getElementById('cartConfirmCancel');
  const closeBtn = document.getElementById('cartConfirmClose');
  const text = document.getElementById('cartConfirmText');
  let activeForm = null;

  function openConfirm(form, productName) {
    activeForm = form;
    text.textContent = productName ? 'Produk "' + productName + '" akan dihapus dari keranjang kamu.' : 'Produk ini akan dihapus dari keranjang kamu.';
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('cart-modal-open');
  }
  function closeConfirm() {
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cart-modal-open');
    activeForm = null;
  }
  document.querySelectorAll('[data-delete-trigger]').forEach(function (button) {
    button.addEventListener('click', function () { openConfirm(button.closest('form'), button.dataset.productName || ''); });
  });
  okBtn.addEventListener('click', async function () {
    if (!activeForm) return;
    const card = activeForm.closest('[data-cart-item]');
    okBtn.disabled = true;
    okBtn.textContent = 'Menghapus...';
    try{
      const data = await sendCart(activeForm.action, 'DELETE');
      closeConfirm();
      removeCard(card);
      updateSummary(data);
      showMessage(data.message);
    }catch(error){ showMessage(error.message); }
    finally{ okBtn.disabled = false; okBtn.textContent = 'Ya, Hapus'; }
  });
  cancelBtn.addEventListener('click', closeConfirm);
  closeBtn.addEventListener('click', closeConfirm);
  overlay.addEventListener('click', function (event) { if (event.target === overlay) closeConfirm(); });
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && overlay.classList.contains('show')) closeConfirm(); });
});
</script>

@endsection
