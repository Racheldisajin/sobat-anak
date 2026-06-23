@extends('layouts.app')
@section('title','Checkout — SobatAnak')
@section('content')
<section class="checkout-hero bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-14">
  <div class="max-w-7xl mx-auto px-6 md:px-12">
    <span class="text-coral font-black uppercase tracking-widest text-xs">Checkout</span>
    <h1 class="font-display hero-title mt-3">Checkout <span class="text-teal">Pesanan</span></h1>
    <p class="text-[#6B8A88] font-bold mt-2">Isi data penerima, ambil lokasi rumah, pilih jasa kirim, lalu lanjut ke halaman konfirmasi pembayaran.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-12 py-12 checkout-official-page">
  @if($errors->any())
    <div class="checkout-alert error">
      <b>Checkout belum bisa dilanjutkan.</b>
      <ul>
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('success'))
    <div class="checkout-alert success">{{ session('success') }}</div>
  @endif

  @php
    $initialShipping = $shippingOptions[0] ?? null;
    $initialShippingKey = old('shipping_option', $initialShipping['key'] ?? '');
    $initialShippingCost = (int) old('shipping_cost', $initialShipping['cost'] ?? 0);
    $initialTotal = (int) $subtotal + $initialShippingCost;
  @endphp

  <form method="POST" action="{{ route('checkout.pay') }}" class="checkout-grid" id="checkoutOfficialForm">
    @csrf
    <input type="hidden" name="shipping_option" id="shippingOptionInput" value="{{ $initialShippingKey }}">
    <input type="hidden" name="shipping_cost" id="shippingCostInput" value="{{ $initialShippingCost }}">
    <input type="hidden" name="destination_id" id="destinationIdInput" value="{{ old('destination_id', $address->destination_id ?? '') }}">

    <div class="checkout-left">
      <div class="checkout-card checkout-address-card">
        <div class="checkout-card-head">
          <span class="checkout-step">1</span>
          <div>
            <p class="checkout-kicker">Alamat Pengiriman</p>
            <h2>Data penerima</h2>
          </div>
        </div>

        <div class="checkout-form-grid">
          <label>
            <span>Nama penerima</span>
            <input name="recipient_name" value="{{ old('recipient_name', $address->recipient_name ?? $user->name) }}" placeholder="Contoh: Felix" required>
          </label>
          <label>
            <span>No. HP</span>
            <input name="phone" value="{{ old('phone', $address->phone ?? '') }}" placeholder="08xxxxxxxxxx" required>
          </label>
          <label class="full address-autocomplete-label">
            <span>Alamat lengkap</span>
            <div class="address-search-wrap">
              <textarea id="addressInput" name="address" rows="4" placeholder="Ketik nama jalan / kelurahan / kota" required autocomplete="off">{{ old('address', $address->address ?? '') }}</textarea>
              <div class="address-suggestions" id="addressSuggestions" hidden></div>
            </div>
            <small class="address-hint">Ketik alamat/daerah, lalu pilih dari dropdown.</small>
          </label>
          <label>
            <span>Kota / Kabupaten</span>
            <input name="city" value="{{ old('city', $address->city ?? '') }}" placeholder="Tangerang" required>
          </label>
          <label>
            <span>Provinsi</span>
            <input name="province" value="{{ old('province', $address->province ?? '') }}" placeholder="Banten" required>
          </label>
          <label>
            <span>Kode pos</span>
            <input name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}" placeholder="15111" required>
          </label>
          <label>
            <span>Nama Kecamatan <small>(untuk bantu cari ongkir otomatis)</small></span>
            <input name="district_name" id="districtNameInput" value="{{ old('district_name', $address->district_name ?? '') }}" placeholder="Contoh: Margaasih / Ciledug / Cipondoh">
          </label>

          <div class="full location-field">
            <span>Share lokasi rumah <small>(opsional)</small></span>
            <div class="location-share-box">
              <input id="locationUrlInput" name="location_url" value="{{ old('location_url', $address->location_url ?? '') }}" placeholder="Tempel link lokasi atau klik tombol ambil lokasi">
              <input type="hidden" id="latitudeInput" name="latitude" value="{{ old('latitude', $address->latitude ?? '') }}">
              <input type="hidden" id="longitudeInput" name="longitude" value="{{ old('longitude', $address->longitude ?? '') }}">
              <div class="location-actions">
                <button type="button" class="location-btn" id="useCurrentLocationBtn">📍 Ambil Lokasi Saya</button>
                <a href="#" class="location-open-link is-hidden" id="openLocationLink" target="_blank" rel="noopener">Buka lokasi</a>
              </div>
              <p class="location-help" id="locationStatus">Klik Ambil Lokasi Saya, geser peta, atau geser pin merah. Alamat akan diisi otomatis jika ditemukan.</p>
              <div class="map-picker-panel map-picker-single" id="mapPickerPanel">
                <div class="map-picker-head">
                  <b>Pilih titik lokasi pengiriman</b>
                  <small>Geser peta atau pin merah sesuai rumah penerima.</small>
                </div>
                <div id="manualMapPicker" class="manual-map-picker"></div>
                <div class="map-picker-actions">
                  <button type="button" class="map-picker-use" id="usePickedMapBtn">Gunakan lokasi ini</button>
                </div>
              </div>
            </div>
          </div>

          <label class="full">
            <span>Catatan untuk pengirim <small>(opsional)</small></span>
            <textarea name="customer_note" rows="3" placeholder="Contoh: tolong dibungkus rapi, rumah pagar putih, telepon dulu sebelum sampai">{{ old('customer_note') }}</textarea>
          </label>
        </div>
      </div>

      <div class="checkout-card">
        <div class="checkout-card-head">
          <span class="checkout-step">2</span>
          <div>
            <p class="checkout-kicker">Jasa Kirim</p>
            <h2>Pilih ongkir</h2>
          </div>
        </div>

        <div class="shipping-toolbar shipping-toolbar-simple">
          <b>Pilih jasa kirim</b>
          <button type="button" id="checkShippingBtn">Cek Ongkir</button>
        </div>

        <div class="shipping-status" id="shippingStatus">{{ !empty($rajaOngkirConfigured) ? 'Klik Cek Ongkir untuk melihat pilihan pengiriman.' : 'RajaOngkir belum aktif.' }}</div>
        <div class="shipping-options" id="shippingOptions"></div>
      </div>

      <div class="checkout-card">
        <div class="checkout-card-head">
          <span class="checkout-step">3</span>
          <div>
            <p class="checkout-kicker">Produk</p>
            <h2>Barang yang dibeli</h2>
          </div>
        </div>

        <div class="checkout-products">
          @foreach($cartItems as $item)
            @php
              $price = (int) ($item->product->price ?? 0);
              $qty = (int) $item->quantity;
              $line = $price * $qty;
            @endphp
            <article class="checkout-product-row">
              <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}">
              <div>
                <h3>{{ $item->product->name }}</h3>
                <p>{{ $item->product->category }} • {{ $qty }} item</p>
              </div>
              <b>Rp {{ number_format($line,0,',','.') }}</b>
            </article>
          @endforeach
        </div>
      </div>

    </div>

    <aside class="checkout-summary-card">
      <p class="checkout-kicker">Konfirmasi Order</p>
      <h2>Total Biaya</h2>

      <div class="summary-line">
        <span>Subtotal produk</span>
        <b>Rp {{ number_format($subtotal,0,',','.') }}</b>
      </div>
      <div class="summary-line">
        <span>Jasa kirim</span>
        <b id="selectedShippingName">{{ $initialShipping ? (($initialShipping['courier_label'] ?? 'Kurir') . ' ' . ($initialShipping['service'] ?? '')) : 'Pilih ongkir dulu' }}</b>
      </div>
      <div class="summary-line">
        <span>Ongkir</span>
        <b id="summaryShippingCost">Rp {{ number_format($initialShippingCost,0,',','.') }}</b>
      </div>
      <div class="summary-grand">
        <span>Total</span>
        <b id="summaryTotal">Rp {{ number_format($initialTotal,0,',','.') }}</b>
      </div>

      <button type="button" class="checkout-pay-btn" id="openConfirmBtn">Lanjut Pembayaran</button>
      <a href="{{ route('cart.index') }}" class="checkout-back-link">← Kembali ke Cart</a>
    </aside>
  </form>
</section>

<style>
.checkout-official-page{background:#F7FBFA}.checkout-alert{margin-bottom:1.25rem;border-radius:1.2rem;padding:1rem 1.15rem;font-weight:900}.checkout-alert.error{background:#FDECEA;border:1px solid #F2C8C3;color:#9E3C34}.checkout-alert.success{background:#E9FAF0;border:1px solid #BFE7CB;color:#1C6E3B}.checkout-alert ul{margin:.45rem 0 0 1.1rem}.checkout-grid{display:grid;grid-template-columns:minmax(0,1fr) 390px;gap:1.4rem;align-items:start}.checkout-left{display:flex;flex-direction:column;gap:1.2rem}.checkout-card,.checkout-summary-card{background:#fff;border:1px solid var(--border);border-radius:1.55rem;box-shadow:0 18px 48px rgba(42,61,60,.07)}.checkout-card{padding:1.35rem}.checkout-card-head{display:flex;align-items:center;gap:.9rem;margin-bottom:1rem}.checkout-step{width:42px;height:42px;border-radius:999px;background:#D0F0ED;color:#2f9e92;display:grid;place-items:center;font-weight:1000;box-shadow:0 10px 24px rgba(75,191,176,.14)}.checkout-kicker{font-size:.72rem;font-weight:1000;letter-spacing:.09em;text-transform:uppercase;color:var(--coral);margin:0}.checkout-card h2,.checkout-summary-card h2{font-size:1.6rem;font-weight:1000;margin:.1rem 0 0;color:var(--fg)}.checkout-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.checkout-form-grid label{display:flex;flex-direction:column;gap:.45rem}.checkout-form-grid label.full,.checkout-form-grid>.full{grid-column:1/-1}.checkout-form-grid span{font-weight:1000;color:var(--fg);font-size:.9rem}.checkout-form-grid small{color:var(--muted);font-weight:900}.checkout-form-grid input,.checkout-form-grid textarea{width:100%;border:1.5px solid var(--border);border-radius:1.05rem;background:#F7FBFA;padding:.92rem 1rem;font-weight:900;color:var(--fg);outline:0;transition:.2s}.checkout-form-grid textarea{resize:vertical}.checkout-form-grid input:focus,.checkout-form-grid textarea:focus{border-color:#4BBFB0;background:#fff;box-shadow:0 0 0 4px rgba(75,191,176,.13)}.checkout-products{display:flex;flex-direction:column;gap:.85rem}.checkout-product-row{display:grid;grid-template-columns:72px minmax(0,1fr) auto;gap:.9rem;align-items:center;border:1px solid #EAF4F3;border-radius:1.2rem;padding:.85rem;background:#FCFFFE}.checkout-product-row img{width:72px;height:72px;border-radius:1rem;object-fit:cover;background:#F6FAFA}.checkout-product-row h3{font-weight:1000;color:var(--fg);line-height:1.25}.checkout-product-row p{color:var(--muted);font-weight:900;margin-top:.25rem;font-size:.86rem}.checkout-product-row b{font-weight:1000;color:var(--teal2);white-space:nowrap}.checkout-summary-card{position:sticky;top:112px;padding:1.45rem}.summary-line{display:flex;justify-content:space-between;gap:1rem;border-bottom:1px solid #EAF4F3;padding:.85rem 0;font-weight:900;color:var(--muted)}.summary-line b{color:var(--fg);text-align:right}.summary-grand{margin:1rem 0;background:linear-gradient(135deg,#D0F0ED,#fff);border:1px solid var(--border);border-radius:1.15rem;padding:1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;font-weight:1000}.summary-grand b{font-size:1.25rem;color:var(--coral)}.midtrans-official-box{display:flex;gap:.85rem;align-items:flex-start;background:#F7FBFA;border:1px solid var(--border);border-radius:1.15rem;padding:1rem;margin:1rem 0}.midtrans-icon{width:46px;height:46px;border-radius:999px;background:#D0F0ED;display:grid;place-items:center;font-size:1.25rem;flex:0 0 auto}.midtrans-official-box b{font-weight:1000;color:var(--fg)}.midtrans-official-box p{font-weight:800;color:var(--muted);line-height:1.45;margin:.25rem 0 0}.checkout-pay-btn{width:100%;border:0;border-radius:999px;background:var(--coral);color:white;font-weight:1000;padding:1rem 1.2rem;box-shadow:0 16px 36px rgba(232,117,106,.23);transition:.22s}.checkout-pay-btn:hover{background:var(--coral2);transform:translateY(-2px)}.checkout-back-link{display:flex;justify-content:center;margin-top:.8rem;color:var(--teal2);font-weight:1000}.checkout-safe-note{font-size:.82rem;line-height:1.45;color:var(--muted);font-weight:800;margin-top:.95rem;text-align:center}.shipping-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;background:#F7FBFA;border:1px solid var(--border);border-radius:1.15rem;padding:1rem;margin-bottom:1rem}.shipping-toolbar b{font-weight:1000;color:var(--fg)}.shipping-toolbar p{font-weight:800;color:var(--muted);font-size:.84rem;line-height:1.45;margin:.2rem 0 0}.shipping-toolbar button{border:0;border-radius:999px;background:#4BBFB0;color:white;font-weight:1000;padding:.8rem 1rem;white-space:nowrap}.shipping-status{font-weight:900;color:var(--muted);margin:.35rem 0 .85rem}.shipping-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}.shipping-group-card{position:relative;border:1.5px solid #EAF4F3;border-radius:1.15rem;background:#FCFFFE;padding:1rem;transition:.2s}.shipping-group-card.is-selected{border-color:#4BBFB0;background:linear-gradient(135deg,#EDFCFA,#fff);box-shadow:0 14px 34px rgba(75,191,176,.12)}.shipping-group-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.75rem}.shipping-group-title b{display:block;color:var(--fg);font-weight:1000}.shipping-group-title small{display:block;color:var(--muted);font-weight:850;line-height:1.35;margin-top:.15rem}.shipping-group-card .check{width:24px;height:24px;border-radius:999px;background:#4BBFB0;color:#fff;font-weight:1000;display:grid;place-items:center;opacity:0;transform:scale(.75);transition:.2s;flex:0 0 auto}.shipping-group-card.is-selected .check{opacity:1;transform:scale(1)}.shipping-service-select{width:100%;border:1.5px solid var(--border);border-radius:.95rem;background:#fff;padding:.82rem .9rem;font-weight:1000;color:var(--fg);outline:0;cursor:pointer}.shipping-service-select:focus{border-color:#4BBFB0;box-shadow:0 0 0 4px rgba(75,191,176,.13)}.shipping-service-info{margin-top:.65rem;border-radius:.9rem;background:#F7FBFA;border:1px solid #EAF4F3;padding:.75rem}.shipping-service-info small{display:block;color:var(--muted);font-weight:850;line-height:1.35}.shipping-service-info strong{display:block;color:var(--coral);font-weight:1000;font-size:1.08rem;margin-top:.35rem}.shipping-service-info .eta{display:inline-flex;margin-top:.25rem;color:#2f9e92;font-weight:900;font-size:.8rem}.shipping-note-small{grid-column:1/-1;margin:.2rem 0 0;color:var(--muted);font-weight:850;font-size:.86rem;line-height:1.45;background:#F7FBFA;border:1px dashed var(--border);border-radius:1rem;padding:.85rem 1rem}.location-field{display:flex;flex-direction:column;gap:.45rem}.location-field>span{font-weight:1000;color:var(--fg);font-size:.9rem}.location-share-box{border:1.5px solid var(--border);border-radius:1.05rem;background:#F7FBFA;padding:.8rem}.location-field{grid-column:1/-1}.location-share-box{width:100%}.location-share-box input{background:#fff!important}.location-actions{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-top:.65rem}.location-btn,.location-open-link{border:0;border-radius:999px;padding:.75rem 1rem;font-weight:1000;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:.2s}.location-btn{background:#4BBFB0;color:#fff;box-shadow:0 12px 24px rgba(75,191,176,.16);cursor:pointer}.location-btn:hover{transform:translateY(-1px);background:#2f9e92}.location-open-link{background:#fff;border:1px solid var(--border);color:#2f9e92}.location-open-link.is-hidden{display:none}.location-help{margin:.65rem 0 0;color:var(--muted);font-size:.82rem;line-height:1.45;font-weight:800}.confirm-overlay{position:fixed;inset:0;background:rgba(26,42,41,.55);z-index:9999;display:grid;place-items:center;padding:1.25rem}.confirm-card{width:min(520px,100%);background:#fff;border-radius:1.5rem;padding:1.4rem;position:relative;box-shadow:0 30px 80px rgba(0,0,0,.18)}.confirm-close{position:absolute;right:1rem;top:1rem;border:1px solid var(--border);background:#fff;border-radius:.8rem;width:38px;height:38px;font-size:1.4rem;font-weight:1000;color:var(--fg)}.confirm-card h2{font-size:1.7rem;font-weight:1000;color:var(--fg);margin:.3rem 0}.confirm-text{font-weight:850;color:var(--muted);line-height:1.5;margin:0 0 1rem}.confirm-list{border:1px solid var(--border);border-radius:1.1rem;overflow:hidden;margin-bottom:1rem}.confirm-list div{display:flex;justify-content:space-between;gap:1rem;padding:.85rem 1rem;border-bottom:1px solid #EAF4F3;font-weight:900;color:var(--muted)}.confirm-list div:last-child{border-bottom:0}.confirm-list b{color:var(--fg);text-align:right}.confirm-list .is-total{background:#D0F0ED;color:var(--fg);font-weight:1000}.confirm-list .is-total b{color:var(--coral);font-size:1.15rem}.confirm-edit{width:100%;border:0;background:transparent;color:#2f9e92;font-weight:1000;margin-top:.75rem;padding:.6rem}@media(max-width:1024px){.checkout-grid{grid-template-columns:1fr}.checkout-summary-card{position:relative;top:auto}}@media(max-width:720px){.shipping-options{grid-template-columns:1fr}.checkout-form-grid{grid-template-columns:1fr}.checkout-product-row{grid-template-columns:62px minmax(0,1fr)}.checkout-product-row b{grid-column:2}.checkout-product-row img{width:62px;height:62px}.checkout-card,.checkout-summary-card{border-radius:1.25rem;padding:1.1rem}.shipping-toolbar{align-items:flex-start;flex-direction:column}.shipping-toolbar button{width:100%}}
.location-map-preview{margin-top:.85rem;border:1px solid var(--border);border-radius:1.15rem;overflow:hidden;background:#F7FBFA;box-shadow:0 12px 28px rgba(42,61,60,.06)}.location-map-preview iframe{display:block;width:100%;height:520px;border:0}.checkout-pay-btn:disabled{opacity:.65;cursor:wait}.midtrans-official-box{background:linear-gradient(135deg,#F7FBFA,#fff)!important}.shipping-group-card.is-selected{border-color:#4BBFB0!important;background:#F0FFFC!important;box-shadow:0 14px 28px rgba(75,191,176,.13)!important}.shipping-group-card.is-selected .check{opacity:1!important;transform:scale(1)!important}
.address-search-wrap{position:relative}.address-suggestions{position:absolute;left:0;right:0;top:calc(100% + .45rem);z-index:40;background:#fff;border:1.5px solid var(--border);border-radius:1rem;box-shadow:0 18px 44px rgba(42,61,60,.14);overflow:hidden;max-height:300px;overflow-y:auto}.address-suggestion-item{width:100%;border:0;background:#fff;text-align:left;padding:.9rem 1rem;cursor:pointer;border-bottom:1px solid #EAF4F3}.address-suggestion-item:last-child{border-bottom:0}.address-suggestion-item:hover{background:#F0FFFC}.address-suggestion-item b{display:block;color:var(--fg);font-weight:1000;line-height:1.3}.address-suggestion-item span{display:block;color:var(--muted);font-weight:850;font-size:.82rem;line-height:1.35;margin-top:.2rem}.address-hint{display:block;margin-top:.45rem;color:var(--muted);font-weight:800;line-height:1.45}.address-mini-loading{padding:.9rem 1rem;color:var(--muted);font-weight:900}.location-map-preview{grid-column:1/-1}.location-map-preview iframe{min-height:520px}

/* PATCH V14: tampilan Google-like, fitur drag Leaflet asli */
.manual-map-picker{height:560px!important;width:100%!important;padding:0!important;background:#fff!important;border:0!important;border-radius:1rem!important;overflow:hidden!important}
.manual-map-picker .leaflet-map-canvas{height:100%!important;width:100%!important;border-radius:1rem!important;overflow:hidden!important;background:#eef7f6!important}
.manual-map-picker .leaflet-container{font-family:inherit!important;background:#eef7f6!important;cursor:grab!important}
.manual-map-picker .leaflet-container.leaflet-dragging{cursor:grabbing!important}
.manual-map-picker .leaflet-tile{filter:saturate(1.18) contrast(.98) brightness(1.06)!important}
.manual-map-picker .leaflet-control-attribution{font-size:9px!important;opacity:.78!important;background:rgba(255,255,255,.75)!important}
.manual-map-picker .leaflet-control-zoom a{color:#2A3D3C!important;border-color:#EAF4F3!important}
.sobat-pin{width:28px;height:28px;background:#EA4335;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 10px 24px rgba(234,67,53,.35);position:relative}
.sobat-pin:after{content:'';position:absolute;width:9px;height:9px;border-radius:50%;background:#B9241A;left:9.5px;top:9.5px}
@media(max-width:720px){.manual-map-picker{height:430px!important;min-height:430px!important}}
</style>

<style>
/* Patch: tampilan ongkir user dibuat ringkas dan hanya menampilkan hasil RajaOngkir asli. */
.shipping-toolbar-simple{padding:.85rem 1rem!important;margin-bottom:.75rem!important}.shipping-toolbar-simple b{font-size:1rem}.shipping-toolbar-simple p{display:none!important}.shipping-status{font-size:.86rem!important;margin:.2rem 0 .7rem!important}.shipping-options{gap:.7rem!important}.shipping-group-card{padding:.85rem!important}.shipping-group-head{margin-bottom:.55rem!important}.shipping-group-title b{font-size:.95rem!important}.shipping-group-title small,.shipping-note-small{display:none!important}.shipping-service-select{padding:.78rem .85rem!important;border-radius:.9rem!important}.shipping-service-info{display:block!important;margin-top:.55rem;padding:.65rem .75rem;border-radius:.8rem;background:#F7FBFA;border:1px solid #EAF4F3}.shipping-service-info small{display:block!important;font-size:.76rem;line-height:1.35;color:#6B8A88;font-weight:850}.shipping-service-info strong{display:none!important}.shipping-service-info .eta{display:none!important}.shipping-empty{grid-column:1/-1;margin:0;color:var(--muted);font-weight:900;background:#F7FBFA;border:1px solid var(--border);border-radius:1rem;padding:.9rem 1rem}.checkout-pay-btn:disabled{opacity:.55!important;cursor:not-allowed!important;transform:none!important}
</style>

<style>
/* Patch clean user: ringkasan checkout dibuat singkat. */
.midtrans-official-box-clean{padding:1rem!important;align-items:center!important}
.midtrans-official-box-clean p{margin:.15rem 0 0!important;font-size:.88rem!important;line-height:1.35!important}
.checkout-summary-card .checkout-pay-btn{margin-top:1rem!important}
.checkout-safe-note{display:none!important}

.address-suggestion-item b{font-size:.94rem}.address-suggestion-item span{font-size:.8rem}.address-hint{font-size:.82rem}.midtrans-official-box-clean{display:none!important}
/* address-indonesia-clean */
</style>
<style>
.location-btn-outline{background:#fff!important;color:#2f9e92!important;border:1.5px solid var(--border)!important;box-shadow:none!important}.map-picker-panel{margin-top:.85rem;border:1.5px solid var(--border);border-radius:1.15rem;background:#fff;padding:.9rem;box-shadow:0 14px 30px rgba(42,61,60,.08)}.map-picker-head{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;margin-bottom:.75rem}.map-picker-head b{font-size:1rem;color:var(--fg);font-weight:1000}.map-picker-head small{color:var(--muted);font-weight:850;text-align:right}.manual-map-picker{height:560px;width:100%;border-radius:1rem;overflow:hidden;border:1px solid #EAF4F3;background:#F7FBFA}.map-picker-actions{display:flex;gap:.65rem;flex-wrap:wrap;margin-top:.8rem}.map-picker-use,.map-picker-close{border:0;border-radius:999px;padding:.75rem 1rem;font-weight:1000;cursor:pointer}.map-picker-use{background:#4BBFB0;color:#fff}.map-picker-close{background:#F7FBFA;color:#2f9e92;border:1px solid var(--border)}.map-picker-note{font-size:.78rem;color:var(--muted);font-weight:850}.shipping-service-note{display:block!important}.courier-note-muted{color:#6B8A88;font-size:.76rem;font-weight:850;margin-top:.35rem;line-height:1.35}
@media(max-width:720px){.manual-map-picker{height:430px}.map-picker-head{display:block}.map-picker-head small{text-align:left;display:block;margin-top:.2rem}}
</style>


<style>
/* Patch: satu peta saja seperti aplikasi marketplace, tanpa Google Maps API key. */
.location-map-preview{display:none!important}
.map-picker-panel.map-picker-single{display:block!important;margin-top:.9rem;background:#fff;border:1.5px solid var(--border);border-radius:1.15rem;padding:.9rem}
.map-picker-panel.map-picker-single[hidden]{display:block!important}
.map-picker-head small{font-size:.78rem}
.map-picker-actions{justify-content:center}
.map-picker-use{min-width:220px}
.shipping-service-info small{font-size:.78rem!important;line-height:1.45!important}
.shipping-service-info .shipping-service-note{display:block!important;color:#6B8A88!important}
.shipping-service-info .shipping-weight-note{display:block!important;color:#2f9e92!important;margin-top:.25rem;font-weight:1000!important}
</style>




<style>
/* Patch ongkir: tampilan lebih mirip marketplace, ringkas, dan tetap muncul saat RajaOngkir limit. */
.shipping-status{padding:.55rem .8rem;border-radius:.9rem;background:#F7FBFA;border:1px solid #EAF4F3;color:#6B8A88;font-weight:900!important;display:inline-block}
.shipping-options{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:.8rem!important}
.shipping-group-card{border:1.5px solid #D6F0ED!important;border-radius:1rem!important;background:#fff!important;cursor:pointer!important;transition:.18s ease!important}
.shipping-group-head{align-items:flex-start!important}.shipping-group-title b{display:block!important;font-size:1rem!important;color:#2A3D3C!important}.courier-note-muted{font-size:.75rem!important;color:#6B8A88!important;margin-top:.18rem!important}
.shipping-service-select{width:100%!important;background:#F7FBFA!important;border:1px solid #DCEFED!important;color:#2A3D3C!important;font-weight:950!important}
.shipping-service-info{background:#F0FFFC!important;border:1px solid #D6F0ED!important}.shipping-service-note{color:#456A68!important;font-size:.8rem!important;line-height:1.45!important}.shipping-mini-price{display:block;color:#F26D6D;font-size:1.05rem;font-weight:1000;margin-bottom:.15rem}.shipping-mini-eta{display:block;color:#2f9e92;font-weight:1000;margin-top:.2rem}.shipping-empty{font-size:.95rem!important}
@media(max-width:720px){.shipping-options{grid-template-columns:1fr!important}.shipping-status{display:block}}
</style>


<style>
/* Patch v2: dropdown alamat dibuat lebih jelas seperti marketplace dan harga merah detail ongkir dihapus. */
.address-search-wrap{position:relative!important}
.address-suggestions{display:block!important;top:calc(100% + .5rem)!important;z-index:9999!important;border:1.5px solid #BFEDEA!important;border-radius:1rem!important;background:#fff!important;box-shadow:0 20px 50px rgba(42,61,60,.18)!important;max-height:340px!important;overflow-y:auto!important}
.address-suggestions[hidden]{display:none!important}
.address-suggestion-item{display:block!important;width:100%!important;padding:.95rem 1rem!important;background:#fff!important;border:0!important;border-bottom:1px solid #EAF4F3!important;text-align:left!important;cursor:pointer!important}
.address-suggestion-item:hover{background:#F0FFFC!important}
.address-suggestion-item b{font-size:.95rem!important;color:#2A3D3C!important;font-weight:1000!important;line-height:1.35!important}
.address-suggestion-item span{font-size:.8rem!important;color:#6B8A88!important;font-weight:850!important;margin-top:.25rem!important;line-height:1.35!important}
.shipping-mini-price{display:none!important}
.shipping-mini-eta{display:block!important;color:#2f9e92!important;font-weight:1000!important;margin-top:0!important}
</style>

<style>
.manual-map-picker{position:relative!important;height:520px!important;min-height:520px!important;width:100%!important;display:block!important;background:#EAF4F3!important;border-radius:1rem!important;overflow:hidden!important}
.manual-map-picker .leaflet-map-canvas{height:100%!important;width:100%!important;border-radius:1rem!important;overflow:hidden!important}
.map-provider-missing{height:100%;display:grid;place-items:center;text-align:center;padding:1.2rem;color:#6B8A88;font-weight:900;line-height:1.55;background:#F7FBFA}
.leaflet-container{font-family:inherit!important}.leaflet-control-attribution{font-size:10px!important}.sobat-pin{width:34px;height:34px;border-radius:999px 999px 999px 0;background:#F04F4F;transform:rotate(-45deg);box-shadow:0 10px 28px rgba(240,79,79,.45);border:3px solid #fff}.sobat-pin:after{content:"";position:absolute;left:50%;top:50%;width:10px;height:10px;background:#fff;border-radius:999px;transform:translate(-50%,-50%)}
.sobat-poi-marker{width:26px;height:26px;border-radius:999px;background:#fff;border:2px solid #fff;box-shadow:0 4px 12px rgba(36,59,58,.22);display:grid;place-items:center;font-size:14px;line-height:1}.sobat-poi-marker.food{background:#FF7043}.sobat-poi-marker.health{background:#EF5350}.sobat-poi-marker.school{background:#78909C}.sobat-poi-marker.shop{background:#42A5F5}.sobat-poi-marker.place{background:#26A69A}.sobat-poi-marker span{filter:drop-shadow(0 1px 1px rgba(0,0,0,.16))}.leaflet-popup-content{font-weight:850;color:#2A3D3C}.map-tone-note{position:absolute;z-index:500;left:14px;bottom:14px;background:rgba(255,255,255,.92);border:1px solid rgba(210,235,233,.9);border-radius:999px;padding:.45rem .7rem;font-size:.76rem;font-weight:900;color:#456A68;box-shadow:0 8px 20px rgba(42,61,60,.12)}
.map-picker-panel.map-picker-single{display:block!important;margin-top:.9rem;background:#fff;border:1.5px solid var(--border);border-radius:1.15rem;padding:.9rem}.map-picker-panel.map-picker-single[hidden]{display:block!important}
.map-picker-actions{justify-content:center}.map-picker-use{min-width:220px}
@media(max-width:720px){.manual-map-picker{height:430px!important;min-height:430px!important}}
</style>


<style>
/* Patch v7: peta embed dibuat full lebar, tidak double, tombol Pilih di peta dihapus. */
#openMapPickerBtn{display:none!important}
.manual-map-picker{height:560px!important;width:100%!important;padding:0!important;background:#fff!important;border:0!important;border-radius:1rem!important;overflow:hidden!important}
.sobat-embed-map{position:relative!important;width:100%!important;height:100%!important;min-height:560px!important;border-radius:1rem!important;overflow:hidden!important;background:#eef7f6!important}
.sobat-embed-map iframe{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;min-height:560px!important;border:0!important;display:block!important;transform:scale(1.18)!important;transform-origin:center center!important}
.sobat-map-click-layer{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;border:0!important;background:transparent!important;z-index:5!important;cursor:grab!important;padding:0!important;margin:0!important;touch-action:none!important}.sobat-map-click-layer.is-dragging{cursor:grabbing!important}
.sobat-google-pin{position:absolute!important;z-index:9!important;width:42px!important;height:42px!important;transform:translate(-50%,-100%)!important;cursor:grab!important;filter:drop-shadow(0 10px 16px rgba(231,76,60,.32))!important}
.sobat-google-pin:active{cursor:grabbing!important}
.sobat-google-pin:before{content:'';position:absolute;left:50%;top:0;width:34px;height:34px;background:#E74C3C;border-radius:50% 50% 50% 0;transform:translateX(-50%) rotate(-45deg);box-shadow:0 8px 18px rgba(231,76,60,.35)}
.sobat-google-pin:after{content:'';position:absolute;left:50%;top:9px;width:12px;height:12px;background:#B83228;border-radius:999px;transform:translateX(-50%);z-index:2}
.sobat-map-loading-v6{position:absolute;inset:0;display:grid;place-items:center;background:#F7FBFA;color:#6B8A88;font-weight:1000;z-index:12}
.sobat-map-note-v6{display:none!important}
.map-picker-panel.map-picker-single{padding:.8rem!important;background:#fff!important}
.map-picker-head{margin-bottom:.7rem!important}
.map-picker-actions{margin-top:.8rem!important}
@media(max-width:720px){.manual-map-picker,.sobat-embed-map,.sobat-embed-map iframe{height:430px!important;min-height:430px!important}.sobat-embed-map iframe{transform:scale(1.12)!important}}
</style>

<style>
/* PATCH V14 FINAL: peta biru/putih tetap, drag asli, klik baru pindah pin */
#openMapPickerBtn{display:none!important}
.manual-map-picker{height:560px!important;width:100%!important;padding:0!important;background:#fff!important;border:0!important;border-radius:1rem!important;overflow:hidden!important}
.manual-map-picker .leaflet-map-canvas{height:100%!important;width:100%!important;min-height:560px!important;border-radius:1rem!important;overflow:hidden!important;background:#eef7f6!important}
.manual-map-picker .leaflet-container{font-family:inherit!important;background:#eef7f6!important;cursor:grab!important}
.manual-map-picker .leaflet-container.leaflet-dragging{cursor:grabbing!important}
.manual-map-picker .leaflet-tile{filter:saturate(1.18) contrast(.98) brightness(1.06)!important}
.manual-map-picker .leaflet-control-attribution{font-size:9px!important;opacity:.75!important;background:rgba(255,255,255,.72)!important}
.manual-map-picker .leaflet-control-zoom a{color:#2A3D3C!important;border-color:#EAF4F3!important}
.sobat-pin{width:34px!important;height:34px!important;border-radius:50% 50% 50% 0!important;background:#E74C3C!important;transform:rotate(-45deg)!important;box-shadow:0 10px 24px rgba(231,76,60,.35)!important;border:0!important}
.sobat-pin:after{content:''!important;position:absolute!important;left:50%!important;top:50%!important;width:10px!important;height:10px!important;background:#B83228!important;border-radius:999px!important;transform:translate(-50%,-50%)!important}
@media(max-width:720px){.manual-map-picker{height:430px!important;min-height:430px!important}.manual-map-picker .leaflet-map-canvas{min-height:430px!important}}
</style>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const subtotal = {{ (int) $subtotal }};
  const rajaConfigured = @json(!empty($rajaOngkirConfigured));
  let shippingOptions = @json($shippingOptions ?? []);
  const selectedOld = @json($initialShippingKey);
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
  const reverseGeocodeUrl = '{{ route('checkout.reverse-geocode') }}';
  
  const optionsBox = document.getElementById('shippingOptions');
  const shippingOptionInput = document.getElementById('shippingOptionInput');
  const shippingCostInput = document.getElementById('shippingCostInput');
  const selectedShippingName = document.getElementById('selectedShippingName');
  const summaryShippingCost = document.getElementById('summaryShippingCost');
  const summaryTotal = document.getElementById('summaryTotal');
  const confirmShippingName = document.getElementById('confirmShippingName');
  const confirmShippingCost = document.getElementById('confirmShippingCost');
  const confirmTotal = document.getElementById('confirmTotal');
  const shippingStatus = document.getElementById('shippingStatus');

  function rupiah(value){ return 'Rp ' + Number(value || 0).toLocaleString('id-ID'); }

  function normalizeCourier(option){
    return (option.courier_label || option.courier || 'Kurir').trim();
  }

  function optionTitle(option){
    return (normalizeCourier(option) + ' ' + (option.service || '')).trim();
  }

  function updateServiceInfo(card, option){
    const info = card.querySelector('.shipping-service-info');
    if(!info || !option) return;
    const price = option.formatted_cost || rupiah(option.cost || 0);
    const note = makeServiceNote(option);
    info.innerHTML = '<small class="shipping-service-note"><span class="shipping-mini-eta">' + note + '</span></small>'; // harga merah detail dihapus, harga cukup tampil di dropdown
  }


  function makeServiceNote(option){
    const etd = option.etd ? option.etd + ' hari' : 'mengikuti kurir';
    const weightKg = Math.max(1, Math.ceil((Number({{ (int) $defaultWeight }}) || 1000) / 1000));
    const service = option.description || option.service || 'Reguler';
    return 'Estimasi ' + etd + ' • Min. ' + weightKg + ' kg • ' + service;
  }

  function selectShipping(option){
    if(!option) return;
    document.querySelectorAll('.shipping-group-card').forEach(el => el.classList.remove('is-selected'));
    const selectedCard = document.querySelector('[data-group-key="' + CSS.escape(normalizeCourier(option)) + '"]');
    if(selectedCard){
      selectedCard.classList.add('is-selected');
      const select = selectedCard.querySelector('.shipping-service-select');
      if(select && select.value !== option.key) select.value = option.key;
      updateServiceInfo(selectedCard, option);
    }

    const name = optionTitle(option);
    const cost = Number(option.cost || 0);
    shippingOptionInput.value = option.key;
    shippingCostInput.value = cost;
    selectedShippingName.textContent = name;
    summaryShippingCost.textContent = rupiah(cost);
    summaryTotal.textContent = rupiah(subtotal + cost);
    if(confirmShippingName) confirmShippingName.textContent = name;
    if(confirmShippingCost) confirmShippingCost.textContent = rupiah(cost);
    if(confirmTotal) confirmTotal.textContent = rupiah(subtotal + cost);
    const payBtn = document.getElementById('openConfirmBtn');
    if(payBtn) payBtn.disabled = false;
  }

  function renderShipping(options, selectedKey){
    optionsBox.innerHTML = '';
    if(!options.length){
      optionsBox.innerHTML = '<p class="shipping-empty">Belum ada ongkir. Isi alamat lalu klik Cek Ongkir.</p>';
      return;
    }

    const grouped = {};
    options.forEach(function(option){
      const groupKey = normalizeCourier(option);
      if(!grouped[groupKey]) grouped[groupKey] = [];
      grouped[groupKey].push(option);
    });

    Object.keys(grouped).forEach(function(groupKey){
      grouped[groupKey].sort((a,b) => Number(a.cost || 0) - Number(b.cost || 0));
      const services = grouped[groupKey];
      const selectedInGroup = services.find(o => o.key === selectedKey) || services[0];
      const minCost = services[0]?.formatted_cost || rupiah(services[0]?.cost || 0);
      const maxCost = services[services.length - 1]?.formatted_cost || minCost;

      const card = document.createElement('div');
      card.className = 'shipping-group-card';
      card.dataset.groupKey = groupKey;
      card.innerHTML = '<div class="shipping-group-head">' +
        '<div class="shipping-group-title"><b>' + groupKey + '</b><div class="courier-note-muted">Pilih layanan sesuai estimasi dan biaya.</div></div>' +
        '<span class="check">✓</span></div>' +
        '<select class="shipping-service-select" aria-label="Pilih layanan ' + groupKey + '"></select>' +
        '<div class="shipping-service-info"><small class="shipping-service-note"></small></div>';

      const select = card.querySelector('.shipping-service-select');
      services.forEach(function(option){
        const opt = document.createElement('option');
        opt.value = option.key;
        const serviceName = option.service || option.description || 'Reguler';
        opt.textContent = serviceName + ' — ' + (option.formatted_cost || rupiah(option.cost || 0)) + (option.etd ? ' • ' + option.etd + ' hari' : '');
        select.appendChild(opt);
      });
      select.value = selectedInGroup.key;
      select.addEventListener('change', function(){
        const picked = services.find(o => o.key === select.value) || services[0];
        selectShipping(picked);
      });
      card.addEventListener('click', function(e){
        if(e.target && e.target.tagName === 'SELECT') return;
        const picked = services.find(o => o.key === select.value) || services[0];
        selectShipping(picked);
      });
      optionsBox.appendChild(card);
      updateServiceInfo(card, selectedInGroup);
    });

    const selected = options.find(o => o.key === selectedKey) || options[0];
    selectShipping(selected);
  }

  renderShipping(shippingOptions, selectedOld);
  if(!selectedOld){
    const payBtn = document.getElementById('openConfirmBtn');
    if(payBtn) payBtn.disabled = true;
  }

  const checkBtn = document.getElementById('checkShippingBtn');
  if(checkBtn){
    checkBtn.addEventListener('click', async function(){
      const destination = document.getElementById('destinationIdInput')?.value || '';
      const city = document.querySelector('[name="city"]')?.value || '';
      const province = document.querySelector('[name="province"]')?.value || '';
      const postalCode = document.querySelector('[name="postal_code"]')?.value || '';
      const districtName = document.querySelector('[name="district_name"]')?.value || '';
      const addressText = document.querySelector('[name="address"]')?.value || '';
      checkBtn.disabled = true;
      checkBtn.textContent = 'Mengecek...';
      shippingStatus.textContent = 'Sedang mengambil pilihan ongkir...';

      try{
        const res = await fetch('{{ route('checkout.shipping-options') }}', {
          method:'POST',
          headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
          body:JSON.stringify({destination_id: destination, city: city, province: province, postal_code: postalCode, district_name: districtName, address: addressText, weight: {{ (int) $defaultWeight }}})
        });
        const data = await res.json();
        if(!data.ok){ throw new Error(data.message || 'Gagal cek ongkir'); }
        shippingOptions = data.options || [];
        if(data.destination_id && document.getElementById('destinationIdInput')){
          document.getElementById('destinationIdInput').value = data.destination_id;
        }
        shippingStatus.textContent = data.message || (data.destination_label ? 'Ongkir tersedia untuk ' + data.destination_label + '.' : 'Ongkir tersedia.');
        renderShipping(shippingOptions, shippingOptions[0]?.key);
      }catch(err){
        shippingStatus.textContent = err.message || 'Gagal cek ongkir. Cek alamat atau API RajaOngkir.';
      }finally{
        checkBtn.disabled = false;
        checkBtn.textContent = 'Cek Ongkir';
      }
    });
  }

  if(rajaConfigured && checkBtn){
    const cityReady = (document.querySelector('[name="city"]')?.value || '').trim();
    const postalReady = (document.querySelector('[name="postal_code"]')?.value || '').trim();
    if(cityReady && postalReady && !shippingOptions.length){
      setTimeout(() => checkBtn.click(), 500);
    }
  }

  document.getElementById('openConfirmBtn')?.addEventListener('click', function(){
    const form = document.getElementById('checkoutOfficialForm');
    if(form && !form.reportValidity()) return;
    this.disabled = true;
    this.textContent = 'Membuat order...';
    form.submit();
  });

  const locationBtn = document.getElementById('useCurrentLocationBtn');
  const locationInput = document.getElementById('locationUrlInput');
  const latitudeInput = document.getElementById('latitudeInput');
  const longitudeInput = document.getElementById('longitudeInput');
  const locationStatus = document.getElementById('locationStatus');
  const openLocationLink = document.getElementById('openLocationLink');
  const locationMapPreview = document.getElementById('locationMapPreview');
  const locationMapFrame = document.getElementById('locationMapFrame');
  const addressInput = document.getElementById('addressInput');
  const addressSuggestions = document.getElementById('addressSuggestions');
  const cityInput = document.querySelector('[name="city"]');
  const provinceInput = document.querySelector('[name="province"]');
  const postalInput = document.querySelector('[name="postal_code"]');
  const districtInput = document.getElementById('districtNameInput');
  const destinationInput = document.getElementById('destinationIdInput');
  let addressSearchTimer = null;
  let addressSearchController = null;

  const openMapPickerBtn = document.getElementById('openMapPickerBtn');
  const mapPickerPanel = document.getElementById('mapPickerPanel');
  const manualMapEl = document.getElementById('manualMapPicker');
  const usePickedMapBtn = document.getElementById('usePickedMapBtn');
  const closeMapPickerBtn = document.getElementById('closeMapPickerBtn');
  let manualMap = null;
  let manualMarker = null;
  let leafletLoading = null;
  let pickedLatLng = null;
  let reverseSearchTimer = null;
  let poiLayer = null;
  let poiTimer = null;
  let lastPoiBbox = '';

  function hasLeaflet(){
    return !!(window.L && window.L.map);
  }

  function loadLeaflet(){
    if(hasLeaflet()) return Promise.resolve(true);
    if(leafletLoading) return leafletLoading;
    leafletLoading = new Promise(function(resolve){
      if(!document.querySelector('link[data-sobat-leaflet]')){
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        link.dataset.sobatLeaflet = '1';
        document.head.appendChild(link);
      }
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      script.async = true;
      script.onload = function(){ resolve(true); };
      script.onerror = function(){ resolve(false); };
      document.head.appendChild(script);
    });
    return leafletLoading;
  }

  function makeSobatPinIcon(){
    return L.divIcon({
      className: '',
      html: '<div class="sobat-pin"></div>',
      iconSize: [34, 34],
      iconAnchor: [17, 34],
      popupAnchor: [0, -30]
    });
  }

  let googleEmbedState = {ready:false, lat:-6.9175, lng:107.6191, zoom:16, dragging:false};

  function googleEmbedSrc(lat, lng, zoom){
    const z = Number(zoom || googleEmbedState.zoom || 16);
    return 'https://maps.google.com/maps?q=' + encodeURIComponent(Number(lat).toFixed(7) + ',' + Number(lng).toFixed(7)) + '&z=' + z + '&output=embed';
  }

  function latLngToWorld(lat, lng, zoom){
    const siny = Math.sin(Number(lat) * Math.PI / 180);
    const boundedSiny = Math.min(Math.max(siny, -0.9999), 0.9999);
    const scale = 256 * Math.pow(2, Number(zoom || 16));
    return {
      x: (Number(lng) + 180) / 360 * scale,
      y: (0.5 - Math.log((1 + boundedSiny) / (1 - boundedSiny)) / (4 * Math.PI)) * scale
    };
  }

  function worldToLatLng(x, y, zoom){
    const scale = 256 * Math.pow(2, Number(zoom || 16));
    const lng = x / scale * 360 - 180;
    const n = Math.PI - 2 * Math.PI * y / scale;
    const lat = 180 / Math.PI * Math.atan(0.5 * (Math.exp(n) - Math.exp(-n)));
    return {lat:lat, lng:lng};
  }

  function pointToGoogleLatLng(x, y){
    const box = manualMapEl.getBoundingClientRect();
    const center = latLngToWorld(googleEmbedState.lat, googleEmbedState.lng, googleEmbedState.zoom);
    return worldToLatLng(center.x + (x - box.width / 2), center.y + (y - box.height / 2), googleEmbedState.zoom);
  }

  function setEmbedPinCenter(){
    const pin = document.getElementById('sobatGoogleLikePin');
    if(!pin || !manualMapEl) return;
    const box = manualMapEl.getBoundingClientRect();
    pin.style.left = (box.width / 2) + 'px';
    pin.style.top = (box.height / 2) + 'px';
  }

  function syncManualMarker(lat, lng, zoom){
    const nLat = Number(lat), nLng = Number(lng);
    if(!manualMapEl || !Number.isFinite(nLat) || !Number.isFinite(nLng)) return;
    googleEmbedState.lat = nLat;
    googleEmbedState.lng = nLng;
    googleEmbedState.zoom = Number(zoom || googleEmbedState.zoom || 17);

    // Mode utama sekarang pakai Leaflet asli supaya peta bisa digeser natural,
    // tidak reset/reload saat selesai drag. Tampilan tile tetap Google-like.
    if(manualMap && manualMarker && hasLeaflet()){
      manualMarker.setLatLng([nLat, nLng]);
      manualMap.setView([nLat, nLng], googleEmbedState.zoom, {animate:true});
      schedulePoiLoad();
      return;
    }

    // Fallback lama kalau Leaflet gagal load.
    const iframe = document.getElementById('sobatGoogleLikeFrame');
    if(iframe) iframe.src = googleEmbedSrc(nLat, nLng, googleEmbedState.zoom);
    setTimeout(setEmbedPinCenter, 40);
  }

  function setPickedLocation(lat, lng, message){
    pickedLatLng = {lat:Number(lat), lng:Number(lng)};
    const mapsUrl = 'https://www.google.com/maps?q=' + pickedLatLng.lat + ',' + pickedLatLng.lng;
    if(latitudeInput) latitudeInput.value = pickedLatLng.lat;
    if(longitudeInput) longitudeInput.value = pickedLatLng.lng;
    if(locationInput) locationInput.value = mapsUrl;
    if(locationStatus) locationStatus.textContent = message || 'Lokasi pengiriman berhasil dipilih.';
    updateLocationPreview();
    syncManualMarker(pickedLatLng.lat, pickedLatLng.lng, 17);
    clearTimeout(reverseSearchTimer);
    reverseSearchTimer = setTimeout(function(){ reverseGeocodeAndFill(pickedLatLng.lat, pickedLatLng.lng); }, 450);
  }

  function poiClass(tags){
    tags = tags || {};
    if(tags.amenity === 'hospital' || tags.amenity === 'clinic' || tags.amenity === 'pharmacy' || tags.healthcare) return 'health';
    if(tags.amenity === 'school' || tags.amenity === 'university' || tags.amenity === 'kindergarten') return 'school';
    if(tags.shop) return 'shop';
    if(tags.amenity === 'restaurant' || tags.amenity === 'cafe' || tags.amenity === 'fast_food' || tags.amenity === 'food_court') return 'food';
    return 'place';
  }

  function poiEmoji(tags){
    tags = tags || {};
    if(tags.amenity === 'hospital' || tags.amenity === 'clinic' || tags.healthcare) return 'H';
    if(tags.amenity === 'pharmacy') return '💊';
    if(tags.amenity === 'school' || tags.amenity === 'university' || tags.amenity === 'kindergarten') return '🎓';
    if(tags.shop) return '🛍️';
    if(tags.amenity === 'cafe') return '☕';
    if(tags.amenity === 'restaurant' || tags.amenity === 'fast_food' || tags.amenity === 'food_court') return '🍽️';
    return '•';
  }

  function makePoiIcon(tags){
    const cls = poiClass(tags);
    const icon = poiEmoji(tags);
    return L.divIcon({
      className: '',
      html: '<div class="sobat-poi-marker ' + cls + '"><span>' + icon + '</span></div>',
      iconSize: [26, 26],
      iconAnchor: [13, 13],
      popupAnchor: [0, -12]
    });
  }

  async function loadNearbyPois(){
    if(!manualMap || !hasLeaflet()) return;
    if(manualMap.getZoom() < 14){ if(poiLayer) poiLayer.clearLayers(); return; }
    const b = manualMap.getBounds();
    if(!b) return;
    const south = b.getSouth().toFixed(4), west = b.getWest().toFixed(4), north = b.getNorth().toFixed(4), east = b.getEast().toFixed(4);
    const bbox = [south, west, north, east].join(',');
    if(bbox === lastPoiBbox) return;
    lastPoiBbox = bbox;
    const query = '[out:json][timeout:8];(' +
      'node["amenity"~"cafe|restaurant|fast_food|food_court|hospital|clinic|pharmacy|school|university|kindergarten"](' + bbox + ');' +
      'node["shop"](' + bbox + ');' +
      'node["tourism"~"attraction|hotel"](' + bbox + ');' +
      ');out center 45;';
    try{
      const res = await fetch('https://overpass-api.de/api/interpreter', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
        body: 'data=' + encodeURIComponent(query)
      });
      if(!res.ok) throw new Error('POI gagal dimuat');
      const data = await res.json();
      if(!poiLayer){ poiLayer = L.layerGroup().addTo(manualMap); }
      poiLayer.clearLayers();
      (data.elements || []).slice(0, 45).forEach(function(el){
        const lat = el.lat || el.center?.lat;
        const lng = el.lon || el.center?.lon;
        const tags = el.tags || {};
        const name = tags.name || tags.brand || tags.amenity || tags.shop || 'Tempat sekitar';
        if(!lat || !lng || !Number.isFinite(Number(lat)) || !Number.isFinite(Number(lng))) return;
        L.marker([lat, lng], {icon: makePoiIcon(tags), interactive:true})
          .bindPopup(String(name).replace(/[<>]/g,''))
          .addTo(poiLayer);
      });
    }catch(e){
      // POI hanya tambahan. Kalau Overpass penuh, peta dan pin tetap jalan.
    }
  }

  function schedulePoiLoad(){
    clearTimeout(poiTimer);
    poiTimer = setTimeout(loadNearbyPois, 650);
  }

  async function initManualMap(){
    if(!manualMapEl) return;
    let lat = Number(latitudeInput?.value || '') || googleEmbedState.lat || -6.9175;
    let lng = Number(longitudeInput?.value || '') || googleEmbedState.lng || 107.6191;
    googleEmbedState.lat = lat;
    googleEmbedState.lng = lng;
    googleEmbedState.zoom = googleEmbedState.zoom || 17;

    const ok = await loadLeaflet();
    if(!ok || !hasLeaflet()){
      manualMapEl.innerHTML = '<div class="map-provider-missing">Peta gagal dimuat. Coba cek koneksi internet, lalu refresh halaman.</div>';
      return;
    }

    if(manualMap){
      syncManualMarker(lat, lng, googleEmbedState.zoom);
      setTimeout(function(){ manualMap.invalidateSize(); }, 80);
      return;
    }

    manualMapEl.innerHTML = '<div class="leaflet-map-canvas" id="sobatLeafletCanvas"></div>';

    manualMap = L.map('sobatLeafletCanvas', {
      zoomControl: true,
      attributionControl: true,
      dragging: true,
      touchZoom: true,
      scrollWheelZoom: true,
      doubleClickZoom: true,
      boxZoom: true,
      keyboard: true
    }).setView([lat, lng], googleEmbedState.zoom);

    // Tile dibuat memakai tampilan Google Maps road map supaya warna dan label tempat sama seperti contoh.
    // Tetap dipasang di Leaflet agar peta bisa digeser natural tanpa reset/reload.
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      subdomains: ['mt0','mt1','mt2','mt3'],
      maxZoom: 20,
      attribution: 'Map data &copy; Google'
    }).addTo(manualMap);

    manualMarker = L.marker([lat, lng], {
      icon: makeSobatPinIcon(),
      draggable: true,
      autoPan: true
    }).addTo(manualMap);

    // Geser peta = hanya cari area. Pin tidak ikut pindah.
    manualMap.on('dragstart', function(){
      if(locationStatus) locationStatus.textContent = 'Geser peta untuk mencari area. Klik titik rumah agar pin merah pindah.';
    });

    manualMap.on('dragend zoomend moveend', function(){
      googleEmbedState.lat = manualMap.getCenter().lat;
      googleEmbedState.lng = manualMap.getCenter().lng;
      googleEmbedState.zoom = manualMap.getZoom();
      schedulePoiLoad();
    });

    // Klik peta = baru pindahkan pin merah dan isi koordinat.
    manualMap.on('click', function(e){
      setPickedLocation(e.latlng.lat.toFixed(7), e.latlng.lng.toFixed(7), 'Titik lokasi dipilih dari peta.');
    });

    // Geser pin merah manual = langsung simpan titik pin.
    manualMarker.on('dragend', function(e){
      const pos = e.target.getLatLng();
      setPickedLocation(pos.lat.toFixed(7), pos.lng.toFixed(7), 'Pin merah berhasil digeser.');
    });

    setTimeout(function(){ manualMap.invalidateSize(); }, 120);
    schedulePoiLoad();
  }

  setTimeout(function(){ if(mapPickerPanel) mapPickerPanel.hidden = false; initManualMap(); }, 300);

  if(openMapPickerBtn){
    openMapPickerBtn.addEventListener('click', function(){
      if(mapPickerPanel) mapPickerPanel.hidden = false;
      initManualMap();
      if(mapPickerPanel) mapPickerPanel.scrollIntoView({behavior:'smooth', block:'center'});
    });
  }
  if(closeMapPickerBtn){
    closeMapPickerBtn.addEventListener('click', function(){
      if(mapPickerPanel) mapPickerPanel.hidden = false;
    });
  }
  if(usePickedMapBtn){
    usePickedMapBtn.addEventListener('click', function(){
      if(!pickedLatLng){
        setPickedLocation(googleEmbedState.lat.toFixed(7), googleEmbedState.lng.toFixed(7), 'Lokasi manual berhasil disimpan.');
      }
      if(mapPickerPanel) mapPickerPanel.hidden = false;
    });
  }

  function titleCase(value){
    return String(value || '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
  }

  function makeMapsUrlFromText(text){
    return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(text || '');
  }

  function makeEmbedFromText(text){
    return 'https://maps.google.com/maps?q=' + encodeURIComponent(text || '') + '&z=16&output=embed';
  }

  function makeMapEmbedUrl(url){
    if(!url) return '';
    const lat = latitudeInput ? latitudeInput.value.trim() : '';
    const lng = longitudeInput ? longitudeInput.value.trim() : '';
    if(lat && lng){ return 'https://maps.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng) + '&z=16&output=embed'; }
    try{
      const parsed = new URL(url);
      const q = parsed.searchParams.get('q') || parsed.searchParams.get('query');
      if(q){ return makeEmbedFromText(q); }
    }catch(e){}
    return makeEmbedFromText(url);
  }

  function updateLocationPreview(){
    const url = locationInput ? locationInput.value.trim() : '';
    if(openLocationLink){
      if(url){ openLocationLink.href = url; openLocationLink.classList.remove('is-hidden'); }
      else{ openLocationLink.href = '#'; openLocationLink.classList.add('is-hidden'); }
    }
    if(locationMapPreview && locationMapFrame){
      if(url){
        locationMapFrame.src = makeMapEmbedUrl(url);
        locationMapPreview.hidden = false;
      }else{
        locationMapFrame.removeAttribute('src');
        locationMapPreview.hidden = true;
      }
    }
  }

  if(locationInput){ locationInput.addEventListener('input', updateLocationPreview); updateLocationPreview(); }

  function fillAddressFromRaja(item){
    if(!item) return;
    const label = item.label || '';
    if(addressInput) addressInput.value = label;
    if(cityInput) cityInput.value = titleCase(item.city || cityInput.value || '');
    if(provinceInput) provinceInput.value = titleCase(item.province || provinceInput.value || '');
    if(postalInput && item.postal_code) postalInput.value = item.postal_code;
    if(districtInput) districtInput.value = titleCase(item.district || item.subdistrict || districtInput.value || '');
    if(destinationInput && item.id) destinationInput.value = item.id;

    const hasCoordinate = item.latitude && item.longitude && Number.isFinite(Number(item.latitude)) && Number.isFinite(Number(item.longitude));
    const mapsUrl = hasCoordinate ? ('https://www.google.com/maps?q=' + Number(item.latitude).toFixed(7) + ',' + Number(item.longitude).toFixed(7)) : (item.maps_url || makeMapsUrlFromText(label));
    if(locationInput) locationInput.value = mapsUrl;
    if(hasCoordinate){
      const lat = Number(item.latitude).toFixed(7);
      const lng = Number(item.longitude).toFixed(7);
      if(latitudeInput) latitudeInput.value = lat;
      if(longitudeInput) longitudeInput.value = lng;
      pickedLatLng = {lat:Number(lat), lng:Number(lng)};
      syncManualMarker(lat, lng, 17);
    }else{
      if(latitudeInput) latitudeInput.value = '';
      if(longitudeInput) longitudeInput.value = '';
      // Data RajaOngkir biasanya tidak punya koordinat. Setelah user klik dropdown,
      // cari koordinat alamat supaya pin peta ikut pindah ke titik tersebut.
      geocodeSelectedAddressForMap(label);
    }
    if(locationStatus) locationStatus.textContent = item.source === 'reverse' ? 'Alamat dari pin berhasil dibaca. Koreksi manual jika belum tepat.' : 'Alamat dipilih. Klik titik atau geser pin merah jika titik rumah perlu diperbaiki.';
    if(addressSuggestions){ addressSuggestions.hidden = true; addressSuggestions.innerHTML = ''; }
    updateLocationPreview();

    if(checkBtn && item.id){
      setTimeout(() => checkBtn.click(), 250);
    }
  }


  async function geocodeSelectedAddressForMap(label){
    const query = String(label || '').trim();
    if(!query || query.length < 3) return;
    try{
      const res = await fetch('{{ route('checkout.address-search') }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
        body:JSON.stringify({q:query, city: cityInput?.value || '', province: provinceInput?.value || '', postal_code: postalInput?.value || '', district_name: districtInput?.value || ''})
      });
      const data = await res.json();
      const found = (data.items || []).find(function(row){
        return row.latitude && row.longitude && Number.isFinite(Number(row.latitude)) && Number.isFinite(Number(row.longitude));
      });
      if(found){
        const lat = Number(found.latitude).toFixed(7);
        const lng = Number(found.longitude).toFixed(7);
        if(latitudeInput) latitudeInput.value = lat;
        if(longitudeInput) longitudeInput.value = lng;
        pickedLatLng = {lat:Number(lat), lng:Number(lng)};
        if(locationInput) locationInput.value = 'https://www.google.com/maps?q=' + lat + ',' + lng;
        syncManualMarker(lat, lng, 17);
        updateLocationPreview();
        if(locationStatus) locationStatus.textContent = 'Alamat dipilih. Pin merah sudah diarahkan ke titik alamat, geser jika belum tepat.';
      }
    }catch(e){
      // Jika geocode gagal, form alamat tetap terisi. User masih bisa geser pin manual.
    }
  }

  async function reverseGeocodeAndFill(lat, lng){
    if(!Number.isFinite(Number(lat)) || !Number.isFinite(Number(lng))) return;
    if(locationStatus) locationStatus.textContent = 'Membaca alamat dari titik pin...';
    try{
      const res = await fetch(reverseGeocodeUrl, {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
        body:JSON.stringify({latitude:lat, longitude:lng})
      });
      const data = await res.json();
      if(!data.ok || !data.item) throw new Error(data.message || 'Alamat titik peta belum ditemukan.');
      data.item.source = 'reverse';
      fillAddressFromRaja(data.item);
    }catch(err){
      if(locationStatus) locationStatus.textContent = 'Pin sudah tersimpan. Alamat belum terbaca otomatis, silakan lengkapi manual.';
    }
  }

  function renderAddressSuggestions(items){
    if(!addressSuggestions) return;
    addressSuggestions.innerHTML = '';
    if(!items.length){
      addressSuggestions.innerHTML = '<div class="address-mini-loading">Alamat tidak ditemukan. Coba tulis kelurahan/kecamatan + kota.</div>';
      addressSuggestions.hidden = false;
      return;
    }
    items.slice(0, 8).forEach(function(item){
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'address-suggestion-item';
      const main = item.label || 'Pilih alamat ini';
      const meta = [item.subdistrict, item.district, item.city, item.province, item.postal_code].filter(Boolean).join(' • ');
      btn.innerHTML = '<b>' + main + '</b><span>' + meta + '</span>';
      btn.addEventListener('click', function(){ fillAddressFromRaja(item); });
      addressSuggestions.appendChild(btn);
    });
    addressSuggestions.hidden = false;
  }

  async function searchAddressSuggestions(){
    if(!addressInput || !addressSuggestions) return;
    const q = addressInput.value.trim();
    if(q.length < 3){ addressSuggestions.hidden = true; return; }
    if(addressSearchController) addressSearchController.abort();
    addressSearchController = new AbortController();
    addressSuggestions.innerHTML = '<div class="address-mini-loading">Mencari alamat...</div>';
    addressSuggestions.hidden = false;
    try{
      const res = await fetch('{{ route('checkout.address-search') }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
        body:JSON.stringify({q:q, city: cityInput?.value || '', province: provinceInput?.value || '', postal_code: postalInput?.value || '', district_name: districtInput?.value || ''}),
        signal: addressSearchController.signal
      });
      const data = await res.json();
      if(!data.ok){ throw new Error(data.message || 'Gagal mencari alamat'); }
      renderAddressSuggestions(data.items || []);
    }catch(err){
      if(err.name === 'AbortError') return;
      addressSuggestions.innerHTML = '<div class="address-mini-loading">Gagal mencari alamat. Isi manual atau pakai Ambil Lokasi Saya.</div>';
      addressSuggestions.hidden = false;
    }
  }

  if(addressInput){
    addressInput.addEventListener('input', function(){
      if(destinationInput) destinationInput.value = '';
      clearTimeout(addressSearchTimer);
      addressSearchTimer = setTimeout(searchAddressSuggestions, 450);
    });
    addressInput.addEventListener('focus', function(){
      if(addressInput.value.trim().length >= 3){
        clearTimeout(addressSearchTimer);
        addressSearchTimer = setTimeout(searchAddressSuggestions, 120);
      }
    });
  }
  document.addEventListener('click', function(e){
    if(addressSuggestions && !e.target.closest('.address-autocomplete-label')){
      addressSuggestions.hidden = true;
    }
  });

  if(locationBtn){
    locationBtn.addEventListener('click', function(){
      if(!navigator.geolocation){
        if(locationStatus) locationStatus.textContent = 'Browser belum mendukung ambil lokasi otomatis. Tempel link lokasi manual.';
        return;
      }
      locationBtn.disabled = true;
      locationBtn.textContent = 'Mengambil lokasi...';
      if(locationStatus) locationStatus.textContent = 'Izinkan akses lokasi dari browser, lalu tunggu sebentar.';

      navigator.geolocation.getCurrentPosition(function(position){
        const lat = position.coords.latitude.toFixed(7);
        const lng = position.coords.longitude.toFixed(7);
        setPickedLocation(lat, lng, 'Lokasi rumah berhasil diambil. Link lokasi sudah masuk ke form.');
        if(manualMap && manualMarker){
          syncManualMarker(lat, lng, 16);
        }
        locationBtn.textContent = '📍 Perbarui Lokasi Saya';
        locationBtn.disabled = false;
      }, function(error){
        let message = 'Gagal mengambil lokasi. ';
        if(error.code === 1) message += 'Akses lokasi ditolak. Tempel link lokasi manual.';
        else message += 'Coba lagi atau tempel link Google Maps manual.';
        if(locationStatus) locationStatus.textContent = message;
        locationBtn.textContent = '📍 Ambil Lokasi Saya';
        locationBtn.disabled = false;
      }, {enableHighAccuracy:true, timeout:12000, maximumAge:0});
    });
  }
});
</script>
<style>
/* Patch v10: kembali ke tampilan maps Google-like sebelumnya, tapi fitur mengikuti v9.
   Geser peta hanya mencari area; pin pindah hanya saat user klik titik baru / geser pin / ambil GPS. */
#openMapPickerBtn{display:none!important}
.manual-map-picker{height:560px!important;width:100%!important;padding:0!important;background:#eef7f6!important;border:0!important;border-radius:1rem!important;overflow:hidden!important}
.sobat-embed-map{position:relative!important;width:100%!important;height:100%!important;min-height:560px!important;border-radius:1rem!important;overflow:hidden!important;background:#eef7f6!important}
.sobat-embed-map iframe{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;min-height:560px!important;border:0!important;display:block!important;transform:scale(1.18)!important;transform-origin:center center!important}
.sobat-map-click-layer{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;border:0!important;background:transparent!important;z-index:5!important;cursor:grab!important;padding:0!important;margin:0!important;touch-action:none!important}
.sobat-map-click-layer.is-dragging{cursor:grabbing!important}
.sobat-google-pin{position:absolute!important;z-index:9!important;width:42px!important;height:42px!important;transform:translate(-50%,-100%)!important;cursor:grab!important;filter:drop-shadow(0 10px 16px rgba(231,76,60,.32))!important}
.sobat-google-pin:active{cursor:grabbing!important}
.sobat-google-pin:before{content:'';position:absolute;left:50%;top:0;width:34px;height:34px;background:#E74C3C;border-radius:50% 50% 50% 0;transform:translateX(-50%) rotate(-45deg);box-shadow:0 8px 18px rgba(231,76,60,.35)}
.sobat-google-pin:after{content:'';position:absolute;left:50%;top:9px;width:12px;height:12px;background:#B83228;border-radius:999px;transform:translateX(-50%);z-index:2}
.sobat-map-loading-v6{position:absolute;inset:0;display:grid;place-items:center;background:#F7FBFA;color:#6B8A88;font-weight:1000;z-index:12}
.sobat-map-note-v6{display:none!important}
@media(max-width:720px){.manual-map-picker,.sobat-embed-map,.sobat-embed-map iframe{height:430px!important;min-height:430px!important}.sobat-embed-map iframe{transform:scale(1.12)!important}}
</style>

<style>
/* PATCH V14 FINAL OVERRIDE */
#openMapPickerBtn{display:none!important}
.manual-map-picker{height:560px!important;width:100%!important;padding:0!important;background:#fff!important;border:0!important;border-radius:1rem!important;overflow:hidden!important}
.manual-map-picker .leaflet-map-canvas{height:100%!important;width:100%!important;min-height:560px!important;border-radius:1rem!important;overflow:hidden!important;background:#eef7f6!important}
.manual-map-picker .leaflet-container{font-family:inherit!important;background:#eef7f6!important;cursor:grab!important}
.manual-map-picker .leaflet-container.leaflet-dragging{cursor:grabbing!important}
.manual-map-picker .leaflet-tile{filter:saturate(1.18) contrast(.98) brightness(1.06)!important}
.manual-map-picker .leaflet-control-attribution{font-size:9px!important;opacity:.75!important;background:rgba(255,255,255,.72)!important}
.manual-map-picker .leaflet-control-zoom a{color:#2A3D3C!important;border-color:#EAF4F3!important}
.sobat-pin{width:34px!important;height:34px!important;border-radius:50% 50% 50% 0!important;background:#E74C3C!important;transform:rotate(-45deg)!important;box-shadow:0 10px 24px rgba(231,76,60,.35)!important;border:0!important}
.sobat-pin:after{content:''!important;position:absolute!important;left:50%!important;top:50%!important;width:10px!important;height:10px!important;background:#B83228!important;border-radius:999px!important;transform:translate(-50%,-50%)!important}
@media(max-width:720px){.manual-map-picker{height:430px!important;min-height:430px!important}.manual-map-picker .leaflet-map-canvas{min-height:430px!important}}
</style>

<style>
/* PATCH V15: tampilan maps kembali seperti Google Maps, fitur drag tetap terbaru */
#openMapPickerBtn{display:none!important}
.manual-map-picker{height:560px!important;width:100%!important;padding:0!important;background:#fff!important;border:0!important;border-radius:1rem!important;overflow:hidden!important}
.manual-map-picker .leaflet-map-canvas{height:100%!important;width:100%!important;min-height:560px!important;border-radius:1rem!important;overflow:hidden!important;background:#f5f8fb!important}
.manual-map-picker .leaflet-container{font-family:inherit!important;background:#f5f8fb!important;cursor:grab!important}
.manual-map-picker .leaflet-container.leaflet-dragging{cursor:grabbing!important}
.manual-map-picker .leaflet-tile{filter:none!important;opacity:1!important}
.manual-map-picker .leaflet-control-attribution{font-size:9px!important;opacity:.62!important;background:rgba(255,255,255,.7)!important}
.manual-map-picker .leaflet-control-zoom{border:0!important;box-shadow:0 2px 8px rgba(0,0,0,.12)!important}
.manual-map-picker .leaflet-control-zoom a{color:#2A3D3C!important;border-color:#EAF4F3!important;background:#fff!important}
.sobat-pin{width:34px!important;height:34px!important;border-radius:50% 50% 50% 0!important;background:#E74C3C!important;transform:rotate(-45deg)!important;box-shadow:0 10px 24px rgba(231,76,60,.35)!important;border:0!important}
.sobat-pin:after{content:''!important;position:absolute!important;left:50%!important;top:50%!important;width:10px!important;height:10px!important;background:#B83228!important;border-radius:999px!important;transform:translate(-50%,-50%)!important}
@media(max-width:720px){.manual-map-picker{height:430px!important;min-height:430px!important}.manual-map-picker .leaflet-map-canvas{min-height:430px!important}}
</style>

@endsection
