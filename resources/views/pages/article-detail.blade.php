@extends('layouts.app')
@section('title', $article->title . ' — SobatAnak')
@section('content')
<section class="bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-14">
    <div class="max-w-5xl mx-auto px-6 md:px-12">
        <a href="{{ route('articles') }}" class="text-teal font-black">← Kembali ke Artikel</a>
        <div class="mt-6">
            <span class="text-coral font-black uppercase tracking-widest text-xs">{{ $article->category_name }}</span>
            <h1 class="font-display hero-title mt-3 leading-tight">{{ $article->title }}</h1>
            <p class="text-[#6B8A88] font-bold mt-4">📅 {{ optional($article->published_at ?? $article->created_at)->format('d M Y') ?? 'SobatAnak' }} · 👁️ {{ $article->counter }} dibaca · SobatAnak Editorial</p>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-6 md:px-12 py-10">
    @if($article->image)
        <img class="w-full rounded-[2rem] shadow-soft max-h-[420px] object-cover" src="{{ $article->image }}" alt="{{ $article->title }}">
    @endif
    <article class="card p-7 md:p-10 mt-8 prose-sobatanak">
        {!! nl2br(e($article->content)) !!}
    </article>

    @if($related->count())
    <div class="mt-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Artikel Terkait</span>
        <h2 class="font-display text-3xl mt-2">Baca juga</h2>
        <div class="grid md:grid-cols-3 gap-5 mt-5">
            @foreach($related as $item)
                <a href="{{ route('article.show', $item->slug ?: $item->id) }}" class="card overflow-hidden hover:-translate-y-1 transition block">
                    <img class="w-full h-40 object-cover" src="{{ $item->image }}" alt="{{ $item->title }}">
                    <div class="p-5">
                        <span class="text-coral font-black text-xs uppercase">{{ $item->category_name }}</span>
                        <h3 class="font-display text-lg mt-2">{{ $item->title }}</h3>
                        <p class="text-[#6B8A88] text-sm mt-2">{{ $item->excerpt }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</section>
<style>.prose-sobatanak{color:#29413f;font-weight:750;line-height:1.9;font-size:1.05rem}.prose-sobatanak p{margin-bottom:1rem}</style>
@endsection
