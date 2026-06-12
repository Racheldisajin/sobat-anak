@extends('layouts.app')
@section('title','Produk — SobatAnak')
@section('content')

{{-- Hero Banner --}}
<section class="prod-hero">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-1">
                <p class="text-coral font-black text-xs uppercase tracking-widest">Katalog Produk</p>
                <h1 class="font-display text-3xl md:text-5xl mt-2">Belanja Kebutuhan <span class="text-teal">Si Kecil</span></h1>
                <p class="text-[#6B8A88] font-bold mt-2 text-sm">{{ $products->count() }} produk tersedia untuk bayi & anak</p>
            </div>
        </div>
    </div>
</section>

{{-- Filter & Search Bar --}}
<section class="sticky top-[76px] z-40 bg-white/96 backdrop-blur border-b border-[#D4EEEC] py-3 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="flex flex-col sm:flex-row gap-3 mb-3">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-3 text-[#6B8A88]">🔍</span>
                <input data-product-search class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-[#D4EEEC] bg-[#F6FAFA] font-bold text-sm focus:outline-none focus:border-[#4BBFB0]" placeholder="Cari produk bayi & anak...">
            </div>
            <select data-product-sort class="px-4 py-2.5 rounded-xl border border-[#D4EEEC] bg-white font-bold text-sm focus:outline-none focus:border-[#4BBFB0]">
                <option>Terlaris</option>
                <option>Terbaru</option>
                <option>Harga Terendah</option>
                <option>Harga Tertinggi</option>
                <option>Rating Tertinggi</option>
            </select>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button data-filter-cat="Semua" class="prod-cat-btn active">Semua</button>
            @foreach($categories as $cat)
                <button data-filter-cat="{{ $cat }}" class="prod-cat-btn">{{ $cat }}</button>
            @endforeach
        </div>
    </div>
</section>

{{-- Product Grid --}}
<section class="max-w-7xl mx-auto px-4 md:px-8 py-6">
    <p class="text-sm text-[#6B8A88] font-bold mb-4">Menampilkan <b data-product-count class="text-[#2A3D3C]">{{ $products->count() }}</b> produk</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
        @foreach($products as $p)
        @php $stock = (int) ($p->stock ?? 0); @endphp
        <a href="{{ route('product.show', $p->id) }}"
           id="product-{{ $p->id }}"
           class="prod-card group {{ $stock <= 0 ? 'prod-card-sold' : '' }}"
           data-product-card
           data-name="{{ $p->name }}"
           data-category="{{ $p->category }}"
           data-price="{{ $p->price }}"
           data-rating="{{ $p->rating }}"
           data-sold="{{ $p->sold }}"
           data-id="{{ $p->id }}">

            {{-- Image --}}
            <div class="relative overflow-hidden prod-card-img-wrap">
                <img src="{{ $p->image }}" alt="{{ $p->name }}" class="prod-card-img group-hover:scale-105 transition-transform duration-500">
                @if($p->badge)
                    <span class="prod-badge">{{ $p->badge }}</span>
                @endif
                @if($stock <= 0)
                    <div class="prod-sold-overlay"><span>Stok Habis</span></div>
                @elseif($stock <= 5)
                    <span class="prod-stock-warn">Stok Terbatas</span>
                @endif
                {{-- Quick add button on hover --}}
                @if($stock > 0)
                <button data-quick-add data-product-id="{{ $p->id }}"
                    class="prod-quick-add opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                    onclick="event.preventDefault(); quickAddToCart({{ $p->id }}, this)">
                    + Keranjang
                </button>
                @endif
            </div>

            {{-- Info --}}
            <div class="prod-card-body">
                <p class="prod-card-cat">{{ $p->category }}</p>
                <h3 class="prod-card-name">{{ $p->name }}</h3>
                <div class="flex items-center gap-1 mt-1">
                    <span class="prod-star">★</span>
                    <span class="prod-rating-val">{{ number_format($p->rating, 1) }}</span>
                    <span class="prod-sold-count">· {{ number_format($p->sold, 0, ',', '.') }}+ terjual</span>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <b class="prod-price">Rp {{ number_format($p->price, 0, ',', '.') }}</b>
                    @if($stock <= 0)
                        <span class="prod-habis-badge">Habis</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Empty State --}}
    <div id="prod-empty" class="hidden text-center py-20">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="font-display text-2xl text-[#6B8A88]">Produk tidak ditemukan</h3>
        <p class="text-[#6B8A88] mt-2">Coba kata kunci atau kategori lain</p>
    </div>
</section>

<script>
function quickAddToCart(productId, btn) {
    btn.disabled = true;
    btn.textContent = '...';
    fetch('/cart/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
        body: JSON.stringify({product_id: productId})
    }).then(r => r.json()).then(data => {
        if (data.ok) {
            document.querySelectorAll('[data-cart-count]').forEach(e => e.textContent = data.cart_count);
            btn.textContent = '✓ Ditambahkan';
            btn.classList.add('added');
            setTimeout(() => { btn.textContent = '+ Keranjang'; btn.classList.remove('added'); btn.disabled = false; }, 1800);
        } else {
            if (data.redirect) { window.location.href = data.redirect; return; }
            btn.textContent = '+ Keranjang'; btn.disabled = false;
            alert(data.message || 'Gagal menambahkan ke keranjang.');
        }
    }).catch(() => { btn.textContent = '+ Keranjang'; btn.disabled = false; });
}

document.addEventListener('DOMContentLoaded', () => {
    const search = document.querySelector('[data-product-search]');
    const sort = document.querySelector('[data-product-sort]');
    const catBtns = document.querySelectorAll('[data-filter-cat]');
    const cards = document.querySelectorAll('[data-product-card]');
    const countEl = document.querySelector('[data-product-count]');
    const emptyEl = document.getElementById('prod-empty');
    let activeCat = 'Semua';

    function filterProducts() {
        const q = (search?.value || '').toLowerCase().trim();
        let visible = 0;
        cards.forEach(c => {
            const matchCat = activeCat === 'Semua' || c.dataset.category === activeCat;
            const matchQ = !q || c.dataset.name.toLowerCase().includes(q);
            const show = matchCat && matchQ;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) countEl.textContent = visible;
        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);

        if (sort) {
            const vis = [...cards].filter(c => c.style.display !== 'none');
            vis.sort((a, b) => {
                const s = sort.value;
                if (s === 'Harga Terendah') return +a.dataset.price - +b.dataset.price;
                if (s === 'Harga Tertinggi') return +b.dataset.price - +a.dataset.price;
                if (s === 'Rating Tertinggi') return +b.dataset.rating - +a.dataset.rating;
                if (s === 'Terbaru') return +b.dataset.id - +a.dataset.id;
                return +b.dataset.sold - +a.dataset.sold;
            });
            vis.forEach(c => c.parentNode.appendChild(c));
        }
    }

    catBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            catBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeCat = btn.dataset.filterCat;
            filterProducts();
        });
    });

    search?.addEventListener('input', filterProducts);
    sort?.addEventListener('change', filterProducts);
    filterProducts();
});
</script>
@endsection
