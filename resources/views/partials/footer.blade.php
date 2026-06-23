<footer class="site-footer mt-12">
  <div class="site-footer-glow site-footer-glow-left"></div>
  <div class="site-footer-glow site-footer-glow-right"></div>

  <div class="max-w-7xl mx-auto px-6 md:px-12 py-12 md:py-14 relative z-10">
    <div class="grid md:grid-cols-4 gap-9 md:gap-10 items-start">
      <div class="md:col-span-2">
        <a href="{{ route('home') }}" class="footer-logo-card inline-flex mb-6" aria-label="SobatAnak Home">
          <img src="{{ asset('images/logo-cropped.png') }}" alt="SobatAnak" class="footer-logo-img">
        </a>

        <p class="footer-desc max-w-xl leading-relaxed">
          SobatAnak.com — Mom &amp; Baby Care. Toko produk bayi, balita, anak, artikel parenting,
          dan mini game edukatif untuk kumpulkan poin.
        </p>
      </div>

      <div>
        <h4 class="footer-title font-display text-lg mb-4">Menu</h4>
        <nav class="space-y-2.5">
          <a class="footer-link" href="{{ route('products') }}">Produk</a>
          <a class="footer-link" href="{{ route('articles') }}">Artikel</a>
          <a class="footer-link" href="{{ route('mini-games') }}">Mini Game</a>
        </nav>
      </div>

      <div>
        <h4 class="footer-title font-display text-lg mb-4">Tagline</h4>
        <p class="footer-tagline">Mom &amp; Baby Care</p>
        <p class="footer-note mt-3">Belanja nyaman, belajar seru, kumpulkan poin bersama SobatAnak.</p>
      </div>
    </div>
  </div>
</footer>
