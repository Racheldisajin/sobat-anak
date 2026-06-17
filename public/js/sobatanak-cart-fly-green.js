
// Global SobatAnak cart fly animation.
// Revisi: animasi HANYA muncul saat user MENAMBAHKAN produk ke keranjang.
// Tidak muncul saat hapus cart, update qty, checkout, atau tombol lain.
(function(){
    if (window.__sobatCartFlyGreenGlobalLoaded) return;
    window.__sobatCartFlyGreenGlobalLoaded = true;

    let lastFlyAt = 0;

    function findCartTarget(){
        return document.querySelector('[data-cart-link]')
            || document.querySelector('.cart-nav-icon')
            || document.querySelector('.nav-cart')
            || document.querySelector('.cart-btn')
            || document.querySelector('a[href$="/cart"]')
            || document.querySelector('a[href*="/cart"]');
    }

    function textOf(el){
        return ((el && el.textContent) || '').trim().toLowerCase();
    }

    function isBadCartAction(el){
        if(!el) return true;

        const text = textOf(el);
        const aria = (el.getAttribute('aria-label') || '').toLowerCase();
        const name = (el.getAttribute('name') || '').toLowerCase();
        const value = (el.getAttribute('value') || '').toLowerCase();
        const form = el.closest('form');
        const action = form ? (form.getAttribute('action') || '').toLowerCase() : '';
        const method = form ? (form.querySelector('input[name="_method"]')?.value || '').toLowerCase() : '';

        const combined = [text, aria, name, value, action, method].join(' ');

        return (
            combined.includes('hapus') ||
            combined.includes('delete') ||
            combined.includes('remove') ||
            combined.includes('trash') ||
            combined.includes('kurang') ||
            combined.includes('minus') ||
            combined.includes('decrease') ||
            combined.includes('update') ||
            combined.includes('checkout') ||
            combined.includes('bayar') ||
            combined.includes('simpan') ||
            combined.includes('wishlist')
        );
    }

    function isAddToCartButton(el){
        if (!el || isBadCartAction(el)) return false;

        const text = textOf(el);
        const aria = (el.getAttribute('aria-label') || '').toLowerCase();
        const form = el.closest('form');
        const action = form ? (form.getAttribute('action') || '').toLowerCase() : '';

        // Selector yang memang dipakai untuk tambah keranjang.
        if (el.matches('[data-buy], .prod-quick-add, .pdp-btn-cart, [data-add-cart], [data-add-to-cart]')) {
            return true;
        }

        // Fallback: tombol berisi + Keranjang / Tambah Keranjang / Add to Cart.
        const combined = [text, aria, action].join(' ');
        const hasCartWord = (
            combined.includes('keranjang') ||
            combined.includes('cart')
        );
        const hasAddWord = (
            combined.includes('+') ||
            combined.includes('tambah') ||
            combined.includes('add') ||
            combined.includes('masukkan')
        );

        return hasCartWord && hasAddWord;
    }

    function fly(source){
        const cart = findCartTarget();
        if (!source || !cart) return;

        const now = Date.now();
        if (now - lastFlyAt < 180) return;
        lastFlyAt = now;

        const start = source.getBoundingClientRect();
        const end = cart.getBoundingClientRect();

        if (!start.width || !start.height || !end.width || !end.height) return;

        const size = window.innerWidth < 640 ? 40 : 46;
        const x1 = start.left + start.width / 2 - size / 2;
        const y1 = start.top + start.height / 2 - size / 2;
        const x2 = end.left + end.width / 2 - size / 2;
        const y2 = end.top + end.height / 2 - size / 2;

        const flyer = document.createElement('div');
        flyer.className = 'sobat-cart-flyer-green';
        flyer.style.left = x1 + 'px';
        flyer.style.top = y1 + 'px';
        flyer.style.setProperty('--fly-x', (x2 - x1) + 'px');
        flyer.style.setProperty('--fly-y', (y2 - y1) + 'px');

        document.body.appendChild(flyer);

        cart.classList.remove('sobat-cart-target-pop');
        requestAnimationFrame(function(){
            flyer.classList.add('is-flying');
        });

        setTimeout(function(){
            flyer.remove();
            cart.classList.add('sobat-cart-target-pop');
            setTimeout(function(){
                cart.classList.remove('sobat-cart-target-pop');
            }, 850);
        }, 1570);
    }

    window.sobatCartFlyGreen = fly;
    window.sobatCartFlyAnimation = fly;

    document.addEventListener('click', function(e){
        const clicked = e.target.closest('button, a, [role="button"], input[type="submit"]');
        if (!clicked || clicked.disabled || clicked.classList.contains('disabled')) return;

        if (!isAddToCartButton(clicked)) return;

        fly(clicked);
    }, true);
})();
