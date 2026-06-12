@extends('layouts.app')
@section('title', $product->name . ' — SobatAnak')
@section('content')

<div class="max-w-7xl mx-auto px-4 md:px-8 py-4">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-[#6B8A88] font-bold mb-5 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-teal">Home</a>
        <span>›</span>
        <a href="{{ route('products') }}" class="hover:text-teal">Produk</a>
        <span>›</span>
        <span class="hover:text-teal cursor-pointer" onclick="filterCategory('{{ $product->category }}')">{{ $product->category }}</span>
        <span>›</span>
        <span class="text-[#2A3D3C] line-clamp-1">{{ $product->name }}</span>
    </nav>

    {{-- Main Product Area --}}
    <div class="grid lg:grid-cols-[420px_1fr_300px] gap-6 items-start">

        {{-- Left: Gallery --}}
        <div class="pdp-gallery-wrap">
            <div class="pdp-main-img-wrap">
                <img id="pdp-main-img" src="{{ $product->image }}" alt="{{ $product->name }}" class="pdp-main-img">
                @if($product->badge)
                    <span class="pdp-badge">{{ $product->badge }}</span>
                @endif
                @if((int)($product->stock ?? 0) <= 0)
                    <div class="pdp-sold-overlay"><span>Stok Habis</span></div>
                @endif
            </div>
            {{-- Thumbnails (same image for now, since we only have 1 image per product) --}}
            <div class="flex gap-2 mt-3 flex-wrap">
                @for($i = 0; $i < 4; $i++)
                <button onclick="document.getElementById('pdp-main-img').src='{{ $product->image }}'"
                    class="pdp-thumb {{ $i === 0 ? 'active' : '' }}">
                    <img src="{{ $product->image }}" alt="thumb">
                </button>
                @endfor
            </div>
        </div>

        {{-- Middle: Product Info --}}
        <div class="pdp-info">
            <p class="text-xs text-[#6B8A88] font-black uppercase tracking-widest mb-1">{{ $product->category }}</p>
            <h1 class="font-display text-2xl md:text-3xl leading-tight">{{ $product->name }}</h1>

            {{-- Rating Row --}}
            <div class="flex items-center gap-3 mt-3 flex-wrap">
                <div class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="pdp-star {{ $i <= round($avgRating) ? 'filled' : '' }}">★</span>
                    @endfor
                </div>
                <span id="pdp-avg-rating" class="font-black text-[#2A3D3C]">{{ $avgRating }}</span>
                <span class="text-[#6B8A88] text-sm font-bold">(<span id="pdp-review-count">{{ $reviews->count() }}</span> ulasan)</span>
                <span class="pdp-dot">·</span>
                <span class="text-[#6B8A88] text-sm font-bold">{{ number_format($product->sold, 0, ',', '.') }}+ terjual</span>
            </div>

            {{-- Price --}}
            <div class="mt-4 mb-5">
                <div class="pdp-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                @if($product->badge && str_contains(strtolower($product->badge), 'diskon'))
                    <span class="pdp-orig-price">Rp {{ number_format($product->price * 1.2, 0, ',', '.') }}</span>
                @endif
            </div>

            <hr class="border-[#D4EEEC] mb-5">

            {{-- Stock Info --}}
            @php $stock = (int)($product->stock ?? 0); @endphp
            <div class="flex items-center gap-3 mb-4">
                <span class="text-sm font-black text-[#2A3D3C]">Stok:</span>
                @if($stock <= 0)
                    <span class="text-coral font-black text-sm">Habis</span>
                @elseif($stock <= 5)
                    <span class="text-yellow-600 font-black text-sm">Sisa {{ $stock }}</span>
                @else
                    <span class="text-teal font-black text-sm">Tersedia ({{ $stock }})</span>
                @endif
            </div>

            {{-- Quantity Stepper --}}
            @if($stock > 0)
            <div class="flex items-center gap-4 mb-5">
                <span class="text-sm font-black text-[#2A3D3C]">Jumlah:</span>
                <div class="pdp-stepper">
                    <button id="pdp-qty-minus" onclick="changeQty(-1)" class="pdp-stepper-btn">−</button>
                    <input id="pdp-qty" type="number" value="1" min="1" max="{{ $stock }}" readonly class="pdp-stepper-input">
                    <button id="pdp-qty-plus" onclick="changeQty(1)" class="pdp-stepper-btn">+</button>
                </div>
                <span class="text-xs text-[#6B8A88] font-bold">Stok: {{ $stock }}</span>
            </div>
            @endif

            {{-- Tabs: Detail / Spesifikasi --}}
            <div class="pdp-tabs mt-2">
                <button class="pdp-tab active" onclick="switchTab(this, 'tab-detail')">Detail Produk</button>
                <button class="pdp-tab" onclick="switchTab(this, 'tab-spec')">Spesifikasi</button>
            </div>

            <div id="tab-detail" class="pdp-tab-content active">
                <div class="pdp-detail-grid">
                    <div><span class="pdp-detail-label">Kondisi</span><span class="pdp-detail-val">Baru</span></div>
                    <div><span class="pdp-detail-label">Kategori</span><span class="pdp-detail-val">{{ $product->category }}</span></div>
                    <div><span class="pdp-detail-label">Min. Beli</span><span class="pdp-detail-val">1 Buah</span></div>
                    <div><span class="pdp-detail-label">Dikirim dari</span><span class="pdp-detail-val">Jakarta</span></div>
                </div>
                <p class="text-[#6B8A88] font-bold text-sm mt-3 leading-relaxed">
                    {{ $product->name }} adalah pilihan tepat dari kategori {{ $product->category }}. Produk berkualitas tinggi, aman untuk bayi dan anak, serta telah terjual lebih dari {{ number_format($product->sold, 0, ',', '.') }} unit. Dapatkan produk terbaik untuk si kecil dengan harga terjangkau di SobatAnak.
                </p>
            </div>
            <div id="tab-spec" class="pdp-tab-content hidden">
                <div class="pdp-detail-grid">
                    <div><span class="pdp-detail-label">Berat Satuan</span><span class="pdp-detail-val">200–500 g</span></div>
                    <div><span class="pdp-detail-label">SKU</span><span class="pdp-detail-val">SA-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</span></div>
                    <div><span class="pdp-detail-label">Rating</span><span class="pdp-detail-val">⭐ {{ $product->rating }} / 5.0</span></div>
                    <div><span class="pdp-detail-label">Stok</span><span class="pdp-detail-val">{{ $stock }} unit</span></div>
                </div>
            </div>
        </div>

        {{-- Right: Purchase Panel --}}
        <div class="pdp-purchase-panel sticky top-[90px]">
            {{-- Shipping Address --}}
            <div class="pdp-shipping-box">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-black text-[#2A3D3C] uppercase tracking-wide">📦 Dikirim ke</span>
                    <button onclick="openAddressModal()" class="text-xs font-black text-teal hover:underline">
                        {{ $userAddress ? 'Ubah' : 'Pilih Alamat' }}
                    </button>
                </div>
                @if($userAddress)
                    <p class="text-sm font-bold text-[#2A3D3C]">{{ $userAddress->city }}, {{ $userAddress->province }}</p>
                    <p class="text-xs text-[#6B8A88] mt-0.5">Estimasi tiba: 2–4 hari kerja</p>
                    <p class="text-xs text-teal font-bold">Ongkir: Rp 15.000</p>
                @else
                    <p class="text-sm text-[#6B8A88] font-bold">Pilih alamat untuk cek ongkir</p>
                @endif
            </div>

            {{-- Subtotal --}}
            <div class="pdp-subtotal-box">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#6B8A88]">Subtotal</span>
                    <span id="pdp-subtotal" class="font-black text-[#2A3D3C] text-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- CTA Buttons --}}
            @if($stock > 0)
            <button id="pdp-btn-cart" onclick="pdpAddToCart()" class="pdp-btn-cart w-full">
                🛒 + Keranjang
            </button>
            <button id="pdp-btn-buy" onclick="pdpBuyNow()" class="pdp-btn-buy w-full mt-2">
                ⚡ Beli Langsung
            </button>
            @else
            <button disabled class="pdp-btn-cart w-full opacity-50 cursor-not-allowed">Stok Habis</button>
            @endif

            {{-- Wishlist/Share --}}
            <div class="flex gap-3 mt-3 justify-center">
                <button onclick="wishlistToggle(this)" class="pdp-action-btn" title="Wishlist">
                    <span>🤍</span> Wishlist
                </button>
                <button onclick="shareProduct()" class="pdp-action-btn" title="Share">
                    <span>🔗</span> Bagikan
                </button>
            </div>

            {{-- Store Info --}}
            <div class="pdp-store-box mt-3">
                <div class="flex items-center gap-3">
                    <div class="pdp-store-avatar">S</div>
                    <div>
                        <p class="font-black text-sm">SobatAnak Official</p>
                        <p class="text-xs text-[#6B8A88]">⭐ 4.9 · Jakarta</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reviews Section --}}
    <div class="mt-10">
        <div class="pdp-review-header">
            <h2 class="font-display text-2xl">Ulasan <span class="text-teal">Pembeli</span></h2>
            <div class="flex items-center gap-4 mt-3">
                <div class="pdp-review-score">
                    <span id="pdp-review-score-big">{{ $avgRating }}</span>
                    <span class="pdp-review-score-max">/5</span>
                </div>
                <div>
                    <div class="flex items-center gap-1 mb-1">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="pdp-star text-xl {{ $i <= round($avgRating) ? 'filled' : '' }}">★</span>
                        @endfor
                    </div>
                    <p class="text-sm text-[#6B8A88] font-bold">Berdasarkan {{ $reviews->count() }} ulasan</p>
                </div>
            </div>
        </div>

        {{-- Write Review --}}
        @if(session('user_id'))
            @if(!$userReview)
            <div class="pdp-review-form-box mt-5">
                <h3 class="font-display text-lg mb-3">Tulis Ulasan Anda</h3>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-sm font-bold text-[#2A3D3C]">Rating:</span>
                    <div class="star-picker" id="star-picker">
                        @for($i = 1; $i <= 5; $i++)
                            <span data-star="{{ $i }}" onclick="setReviewStar({{ $i }})" class="star-pick-btn">★</span>
                        @endfor
                    </div>
                    <span id="star-label" class="text-xs text-[#6B8A88] font-bold ml-1">Pilih bintang</span>
                </div>
                <textarea id="review-body" rows="3" placeholder="Ceritakan pengalaman Anda dengan produk ini..."
                    class="w-full border border-[#D4EEEC] rounded-xl p-3 font-bold text-sm resize-none focus:outline-none focus:border-[#4BBFB0]"></textarea>
                <div class="flex justify-end mt-2">
                    <button onclick="submitReview({{ $product->id }})" class="btn-pill btn-teal text-sm py-2">
                        Kirim Ulasan
                    </button>
                </div>
            </div>
            @else
            <div class="pdp-review-form-box mt-5">
                <p class="text-teal font-black">✓ Anda sudah memberikan ulasan untuk produk ini.</p>
                <div class="flex gap-2 mt-2">
                    <button onclick="deleteReview({{ $product->id }})" class="text-sm text-coral font-bold hover:underline">Hapus ulasan saya</button>
                </div>
            </div>
            @endif
        @else
            <div class="pdp-review-form-box mt-5 text-center">
                <p class="text-[#6B8A88] font-bold">
                    <a href="{{ route('login') }}" class="text-teal font-black hover:underline">Login</a> untuk memberikan ulasan
                </p>
            </div>
        @endif

        {{-- Review List --}}
        <div id="pdp-reviews-list" class="mt-5 grid gap-4">
            @forelse($reviews as $r)
            <div class="pdp-review-card" id="review-{{ $r->id }}">
                <div class="flex items-center gap-3 mb-2">
                    <div class="pdp-review-avatar">{{ strtoupper(substr($r->user->name ?? 'U', 0, 1)) }}</div>
                    <div>
                        <p class="font-black text-sm">{{ $r->user->name ?? 'Pengguna' }}</p>
                        <p class="text-xs text-[#6B8A88]">{{ $r->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="ml-auto flex">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-sm {{ $i <= $r->rating ? 'text-yellow-400' : 'text-[#D4EEEC]' }}">★</span>
                        @endfor
                    </div>
                </div>
                <p class="text-sm text-[#2A3D3C] font-bold leading-relaxed">{{ $r->body }}</p>
            </div>
            @empty
            <div id="no-reviews-msg" class="text-center py-10 text-[#6B8A88] font-bold">
                <div class="text-4xl mb-2">💬</div>
                <p>Belum ada ulasan. Jadilah yang pertama!</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Related Products --}}
    @if($related->count())
    <div class="mt-12">
        <h2 class="font-display text-2xl mb-5">Produk <span class="text-teal">Serupa</span></h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($related as $rp)
            <a href="{{ route('product.show', $rp->id) }}" class="prod-card group">
                <div class="relative overflow-hidden prod-card-img-wrap">
                    <img src="{{ $rp->image }}" alt="{{ $rp->name }}" class="prod-card-img group-hover:scale-105 transition-transform duration-500">
                    @if($rp->badge)
                        <span class="prod-badge">{{ $rp->badge }}</span>
                    @endif
                </div>
                <div class="prod-card-body">
                    <h3 class="prod-card-name">{{ $rp->name }}</h3>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="prod-star">★</span>
                        <span class="prod-rating-val">{{ $rp->rating }}</span>
                    </div>
                    <b class="prod-price mt-1 block">Rp {{ number_format($rp->price, 0, ',', '.') }}</b>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Address Modal --}}
<div id="address-modal" class="pdp-modal-overlay hidden">
    <div class="pdp-modal-box">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-xl">📍 Alamat Pengiriman</h3>
            <button onclick="closeAddressModal()" class="text-2xl text-[#6B8A88] hover:text-coral font-black leading-none">×</button>
        </div>
        <form id="address-form" onsubmit="saveAddress(event)">
            <div class="grid gap-3">
                <input name="recipient_name" value="{{ $userAddress?->recipient_name }}" placeholder="Nama Penerima *" required
                    class="auth-input">
                <input name="phone" value="{{ $userAddress?->phone }}" placeholder="No. HP Penerima *" required
                    class="auth-input">
                <textarea name="address" rows="2" placeholder="Alamat Lengkap (Jalan, No. Rumah, RT/RW) *" required
                    class="auth-input resize-none">{{ $userAddress?->address }}</textarea>
                <div class="grid grid-cols-2 gap-3">
                    <input name="city" value="{{ $userAddress?->city }}" placeholder="Kota/Kabupaten *" required
                        class="auth-input">
                    <input name="province" value="{{ $userAddress?->province }}" placeholder="Provinsi *" required
                        class="auth-input">
                </div>
                <input name="postal_code" value="{{ $userAddress?->postal_code }}" placeholder="Kode Pos"
                    class="auth-input">
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="closeAddressModal()" class="flex-1 btn-pill border border-[#D4EEEC] text-[#6B8A88] justify-center">Batal</button>
                <button type="submit" class="flex-1 btn-pill btn-teal justify-center">Simpan Alamat</button>
            </div>
        </form>
    </div>
</div>

<script>
const PRODUCT_ID = {{ $product->id }};
const PRODUCT_PRICE = {{ $product->price }};
const MAX_STOCK = {{ $stock }};
let selectedRating = 0;

// --- QTY STEPPER ---
function changeQty(delta) {
    const input = document.getElementById('pdp-qty');
    if (!input) return;
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(MAX_STOCK, val));
    input.value = val;
    updateSubtotal(val);
}
function updateSubtotal(qty) {
    const el = document.getElementById('pdp-subtotal');
    if (el) el.textContent = 'Rp ' + (PRODUCT_PRICE * qty).toLocaleString('id-ID');
}

// --- TAB SWITCHING ---
function switchTab(btn, tabId) {
    document.querySelectorAll('.pdp-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.pdp-tab-content').forEach(t => t.classList.add('hidden'));
    btn.classList.add('active');
    document.getElementById(tabId)?.classList.remove('hidden');
    document.getElementById(tabId)?.classList.add('active');
}

// --- ADD TO CART ---
async function pdpAddToCart() {
    const qty = parseInt(document.getElementById('pdp-qty')?.value || 1);
    const btn = document.getElementById('pdp-btn-cart');
    btn.disabled = true;
    btn.textContent = 'Menambahkan...';

    // Add qty times (add one by one since API adds 1 per call)
    let success = false;
    for (let i = 0; i < qty; i++) {
        const res = await postJson('/cart/add', { product_id: PRODUCT_ID });
        if (!res) break;
        if (res.ok) {
            success = true;
            document.querySelectorAll('[data-cart-count]').forEach(e => e.textContent = res.cart_count);
        } else {
            if (res.redirect) { window.location.href = res.redirect; return; }
            showToast(res.message || 'Gagal menambah ke keranjang');
            break;
        }
    }

    if (success) showToast('🛒 Produk berhasil masuk keranjang!', 'success');
    btn.textContent = '🛒 + Keranjang';
    btn.disabled = false;
}

// --- BUY NOW ---
async function pdpBuyNow() {
    const qty = parseInt(document.getElementById('pdp-qty')?.value || 1);
    const btn = document.getElementById('pdp-btn-buy');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    let success = false;
    for (let i = 0; i < qty; i++) {
        const res = await postJson('/cart/add', { product_id: PRODUCT_ID });
        if (!res) { btn.disabled = false; btn.textContent = '⚡ Beli Langsung'; return; }
        if (res.ok) {
            success = true;
            document.querySelectorAll('[data-cart-count]').forEach(e => e.textContent = res.cart_count);
        } else {
            if (res.redirect) { window.location.href = res.redirect; return; }
            showToast(res.message || 'Gagal'); break;
        }
    }

    btn.disabled = false;
    btn.textContent = '⚡ Beli Langsung';
    if (success) window.location.href = '/checkout';
}

// --- STAR RATING ---
function setReviewStar(n) {
    selectedRating = n;
    const labels = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Bagus', 'Sangat Bagus'];
    const picker = document.getElementById('star-picker');
    if (picker) picker.querySelectorAll('[data-star]').forEach(s => {
        s.classList.toggle('selected', parseInt(s.dataset.star) <= n);
    });
    const lbl = document.getElementById('star-label');
    if (lbl) lbl.textContent = labels[n] || '';
}

// --- SUBMIT REVIEW ---
async function submitReview(productId) {
    if (!selectedRating) { showToast('Pilih rating bintang dulu'); return; }
    const body = document.getElementById('review-body')?.value?.trim();
    if (!body) { showToast('Tulis ulasan dulu ya'); return; }

    const res = await postJson(`/products/${productId}/reviews`, { rating: selectedRating, body });
    if (!res) return;
    if (res.ok) {
        showToast('✅ ' + res.message, 'success');
        // Add new review card to list
        const list = document.getElementById('pdp-reviews-list');
        const noMsg = document.getElementById('no-reviews-msg');
        if (noMsg) noMsg.remove();
        const card = document.createElement('div');
        card.className = 'pdp-review-card';
        card.innerHTML = `
            <div class="flex items-center gap-3 mb-2">
                <div class="pdp-review-avatar">${res.review.user_name.charAt(0).toUpperCase()}</div>
                <div>
                    <p class="font-black text-sm">${res.review.user_name}</p>
                    <p class="text-xs text-[#6B8A88]">${res.review.created_at}</p>
                </div>
                <div class="ml-auto flex">
                    ${'★'.repeat(res.review.rating).split('').map((s,i) => `<span class="text-sm ${i < res.review.rating ? 'text-yellow-400' : 'text-[#D4EEEC]'}">${s}</span>`).join('')}
                </div>
            </div>
            <p class="text-sm text-[#2A3D3C] font-bold leading-relaxed">${res.review.body}</p>
        `;
        list.prepend(card);

        // Update avg rating display
        document.querySelectorAll('#pdp-avg-rating, #pdp-review-score-big').forEach(e => e.textContent = res.avg_rating);
        document.querySelectorAll('#pdp-review-count').forEach(e => e.textContent = res.review_count);

        // Hide form
        document.querySelector('.pdp-review-form-box').innerHTML = '<p class="text-teal font-black">✓ Ulasan Anda berhasil disimpan. Terima kasih!</p>';
    } else {
        if (res.redirect) { window.location.href = res.redirect; return; }
        showToast(res.message || 'Gagal menyimpan ulasan');
    }
}

// --- DELETE REVIEW ---
async function deleteReview(productId) {
    if (!confirm('Hapus ulasan Anda?')) return;
    const res = await postJson(`/products/${productId}/reviews`, {}, 'DELETE');
    if (res?.ok) {
        showToast('Ulasan dihapus', 'success');
        location.reload();
    }
}

// --- ADDRESS MODAL ---
function openAddressModal() {
    document.getElementById('address-modal')?.classList.remove('hidden');
    document.body.classList.add('modal-lock');
}
function closeAddressModal() {
    document.getElementById('address-modal')?.classList.add('hidden');
    document.body.classList.remove('modal-lock');
}
async function saveAddress(e) {
    e.preventDefault();
    const form = document.getElementById('address-form');
    const data = Object.fromEntries(new FormData(form));
    const res = await postJson('/address', data);
    if (res?.ok) {
        showToast('📍 ' + res.message, 'success');
        closeAddressModal();
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(res?.message || 'Gagal menyimpan alamat');
    }
}

// --- WISHLIST & SHARE ---
function wishlistToggle(btn) {
    btn.querySelector('span').textContent = btn.querySelector('span').textContent === '🤍' ? '❤️' : '🤍';
    showToast(btn.querySelector('span').textContent === '❤️' ? '❤️ Ditambahkan ke wishlist' : 'Dihapus dari wishlist', 'success');
}
function shareProduct() {
    if (navigator.share) {
        navigator.share({ title: '{{ addslashes($product->name) }}', url: window.location.href });
    } else {
        navigator.clipboard?.writeText(window.location.href);
        showToast('🔗 Link berhasil disalin!', 'success');
    }
}

// --- HELPERS ---
async function postJson(url, data = {}, method = 'POST') {
    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
        return await res.json();
    } catch { return null; }
}
function showToast(msg, type = 'error') {
    const existing = document.querySelector('.pdp-toast');
    if (existing) existing.remove();
    const el = document.createElement('div');
    el.className = 'pdp-toast ' + (type === 'success' ? 'pdp-toast-success' : 'pdp-toast-error');
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.classList.add('show'), 10);
    setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 300); }, 2500);
}

// Close address modal on backdrop click
document.getElementById('address-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddressModal();
});
</script>
@endsection
