// Patch v2: animasi keranjang hijau smooth untuk kartu produk dan halaman detail produk.
(function(){
  let lastFlyAt = 0;

  function getCartTarget(){
    return document.querySelector('[data-cart-link]') || document.querySelector('.cart-nav-icon') || document.querySelector('a[href$="/cart"]');
  }

  function fly(source){
    const cart = getCartTarget();
    if(!source || !cart) return;

    const now = Date.now();
    if (now - lastFlyAt < 280) return;
    lastFlyAt = now;

    const start = source.getBoundingClientRect();
    const end = cart.getBoundingClientRect();
    const flyer = document.createElement('div');
    flyer.className = 'sobat-green-cart-flyer';

    const size = window.innerWidth < 640 ? 38 : 42;
    const x1 = start.left + start.width / 2 - size / 2;
    const y1 = start.top + start.height / 2 - size / 2;
    const x2 = end.left + end.width / 2 - size / 2;
    const y2 = end.top + end.height / 2 - size / 2;

    flyer.style.left = x1 + 'px';
    flyer.style.top = y1 + 'px';
    flyer.style.setProperty('--fly-x', (x2 - x1) + 'px');
    flyer.style.setProperty('--fly-y', (y2 - y1) + 'px');
    document.body.appendChild(flyer);

    cart.classList.remove('cart-pop');
    requestAnimationFrame(function(){ flyer.classList.add('is-flying'); });
    setTimeout(function(){
      flyer.remove();
      cart.classList.add('cart-pop');
      setTimeout(function(){ cart.classList.remove('cart-pop'); }, 1150);
    }, 2280);
  }

  window.sobatCartFlyGreen = fly;

  // Fallback untuk tombol produk di katalog/landing yang belum memanggil fungsi fly.
  document.addEventListener('click', function(e){
    const btn = e.target.closest('[data-buy], .prod-quick-add');
    if(!btn || btn.disabled || btn.classList.contains('disabled')) return;
    // sedikit delay agar terasa natural setelah klik, tapi tetap responsif
    setTimeout(function(){ fly(btn); }, 120);
  }, true);
})();
