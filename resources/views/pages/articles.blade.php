@extends('layouts.app')
@section('title','Artikel — SobatAnak')
@section('content')
<section class="bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-16">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Artikel</span>
        <h1 class="font-display hero-title mt-3">Tips Parenting & <span class="text-teal">Mom Care</span></h1>
    </div>
</section>
<section class="max-w-7xl mx-auto px-6 md:px-12 py-12 grid md:grid-cols-3 gap-5">
    @foreach($articles as $a)
    <article class="card modal-card cursor-pointer" data-open-article data-title="{{ $a->title }}" data-category="{{ $a->category_name }}" data-excerpt="{{ $a->excerpt }}" data-image="{{ $a->image }}" data-date="{{ $a->created_at ? $a->created_at->format('d M Y') : 'SobatAnak' }}">
        <img class="product-img w-full" src="{{ $a->image }}">
        <div class="p-5">
            <span class="text-coral font-black text-xs uppercase">{{ $a->category_name }}</span>
            <h3 class="font-display text-xl mt-2">{{ $a->title }}</h3>
            <p class="text-[#6B8A88] mt-2">{{ $a->excerpt }}</p>
            <button type="button" class="btn-pill btn-teal mt-4 text-sm">Baca Artikel</button>
        </div>
    </article>
    @endforeach
</section>

<div class="info-modal" data-article-modal aria-hidden="true">
    <div class="info-modal-backdrop" data-modal-close></div>
    <div class="info-modal-panel article-detail-panel" role="dialog" aria-modal="true">
        <button class="modal-close" data-modal-close aria-label="Tutup">×</button>
        <div class="modal-article-hero"><img data-modal-article-image src="" alt="Detail artikel"></div>
        <div class="modal-content article-modal-content">
            <span class="modal-kicker" data-modal-article-category></span>
            <h2 class="font-display modal-title" data-modal-article-title></h2>
            <p class="modal-meta">📅 <span data-modal-article-date></span> · ⏱️ 3 menit baca · SobatAnak Editorial</p>
            <p class="modal-desc" data-modal-article-excerpt></p>
            <div class="article-pop-grid">
                <div><b>Ringkasan</b><p data-modal-article-summary></p></div>
                <div><b>Yang Dibahas</b><ul><li>Tips praktis untuk orang tua.</li><li>Hal penting yang perlu diperhatikan.</li><li>Rekomendasi kebiasaan baik di rumah.</li></ul></div>
                <div><b>Catatan SobatAnak</b><p>Gunakan artikel ini sebagai panduan awal. Sesuaikan kembali dengan kebutuhan si kecil dan kondisi keluarga.</p></div>
            </div>
        </div>
    </div>
</div>
@endsection
