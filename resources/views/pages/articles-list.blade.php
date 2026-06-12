<section id="articles" class="max-w-7xl mx-auto px-6 py-12">
  <div class="mb-8"><p class="text-coral font-bold">Artikel Parenting</p><h2 class="text-4xl font-black">Bacaan untuk Orang Tua</h2></div>
  <div class="grid md:grid-cols-3 gap-5">
    @foreach ([['Tips Memilih Skincare Bayi','Cara memilih produk lembut dan aman untuk kulit sensitif bayi.'],['Ide MPASI Praktis','Menu sederhana bergizi untuk si kecil yang mulai belajar makan.'],['Main Edukatif di Rumah','Aktivitas seru untuk melatih motorik dan kreativitas anak.']] as $a)
    <article class="bg-white rounded-3xl p-6 border card"><span class="text-4xl">🍼</span><h3 class="font-black text-xl mt-4">{{ $a[0] }}</h3><p class="text-gray-600 mt-2">{{ $a[1] }}</p><a class="inline-block mt-4 text-teal font-bold" href="{{ route('articles') }}">Baca artikel →</a></article>
    @endforeach
  </div>
</section>
