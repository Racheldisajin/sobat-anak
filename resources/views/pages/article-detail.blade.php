@extends('layouts.app')
@section('title', $article->title . ' — SobatAnak')
@section('content')
@php
    $articleText = trim(strip_tags((string) $article->content));
    $preparedArticle = \App\Support\SobatArticleContent::find($article->slug);
@endphp

<section class="article-detail-hero bg-gradient-to-br from-[#D0F0ED] via-white to-[#FDECEA] py-8 md:py-9">
    <div class="max-w-4xl mx-auto px-6 md:px-10">
        <span class="text-coral font-black uppercase tracking-widest text-xs">{{ $article->category_name }}</span>
        <h1 class="font-display article-detail-title mt-3 leading-tight">{{ $article->title }}</h1>
        <p class="article-meta mt-3">📅 {{ optional($article->published_at ?? $article->created_at)->format('d M Y') ?? 'SobatAnak' }} · 👁️ {{ $article->counter }} dibaca · SobatAnak Editorial</p>
    </div>
</section>

<section class="max-w-4xl mx-auto px-6 md:px-10 py-9 article-detail-page">
    @if($article->image)
        <img class="w-full rounded-[1.7rem] shadow-soft max-h-[380px] object-cover" src="{{ $article->image }}" alt="{{ $article->title }}">
    @endif

    @php
        $relatedSafe = isset($related) ? $related : collect();
        $sections = $preparedArticle['sections'] ?? [];
        $displayLead = $preparedArticle['lead'] ?? ($articleText ?: 'Artikel ini berisi panduan ringan dari SobatAnak untuk membantu orang tua memilih kebutuhan anak dengan lebih tenang.');
    @endphp

    @if($displayLead)
        <p class="article-caption">{{ $displayLead }}</p>
        <div class="article-caption-line"></div>
    @endif

    <article class="article-body p-0 mt-7">
        @if($preparedArticle)
            @if(count($sections))
                <div class="article-toc" id="pembahasan-artikel">
                    <div class="article-toc-title">Pembahasan dalam artikel</div>
                    <ul>
                        @foreach($sections as $section)
                            <li><a href="#subbab-{{ $loop->iteration }}">{{ $section['title'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $readAlsoLimit = min(2, max(0, (int) ($preparedArticle['read_also_limit'] ?? 1)));
                $readAlsoSlots = [];
                if ($readAlsoLimit === 1) {
                    $readAlsoSlots = [max(1, (int) ceil(count($sections) / 2))];
                } elseif ($readAlsoLimit === 2) {
                    $readAlsoSlots = [2, max(3, count($sections))];
                }
                $readAlsoShown = 0;
            @endphp

            @foreach($sections as $section)
                <section id="subbab-{{ $loop->iteration }}" class="article-section">
                    <h2>{{ $section['title'] }}</h2>
                    @php
                        $bodyParagraphs = is_array($section['body']) ? $section['body'] : [$section['body']];
                    @endphp
                    @foreach($bodyParagraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach

                    @php
                        $shouldShowReadAlso = in_array($loop->iteration, $readAlsoSlots, true) && $readAlsoShown < $readAlsoLimit && $relatedSafe->count();
                        $readMore = $shouldShowReadAlso ? $relatedSafe->values()->get($readAlsoShown % max($relatedSafe->count(), 1)) : null;
                        if ($readMore) { $readAlsoShown++; }
                    @endphp

                    @if($readMore)
                        <div class="article-read-also">
                            <span>Baca juga:</span>
                            <a href="{{ route('article.show', $readMore->slug ?: $readMore->id) }}">{{ $readMore->title }}</a>
                        </div>
                    @endif
                </section>
            @endforeach

            <div class="article-note-box">
                <h3>Catatan kecil sebelum membeli</h3>
                <ul>
                    @foreach($preparedArticle['tips'] as $tip)
                        <li>{{ $tip }}</li>
                    @endforeach
                </ul>
            </div>

            <p>{{ $preparedArticle['closing'] }}</p>
        @elseif(strlen($articleText) > 160)
            @php
                $paragraphs = collect(preg_split('/\r\n|\r|\n/', trim((string) $article->content)))->filter(fn($line) => trim($line) !== '')->values();
                $chunks = $paragraphs->chunk(max(1, ceil(max($paragraphs->count(), 1) / 3)))->values();
                $fallbackTitles = ['Hal yang Perlu Diperhatikan', 'Cara Menerapkannya di Rumah', 'Tips agar Lebih Mudah Dilakukan'];
            @endphp

            <div class="article-toc" id="pembahasan-artikel">
                <div class="article-toc-title">Pembahasan dalam artikel</div>
                <ul>
                    @foreach($chunks as $chunk)
                        <li><a href="#subbab-{{ $loop->iteration }}">{{ $fallbackTitles[$loop->index] ?? 'Pembahasan ' . $loop->iteration }}</a></li>
                    @endforeach
                </ul>
            </div>

            @foreach($chunks as $chunk)
                <section id="subbab-{{ $loop->iteration }}" class="article-section">
                    <h2>{{ $fallbackTitles[$loop->index] ?? 'Pembahasan' }}</h2>
                    @foreach($chunk as $line)
                        <p>{{ $line }}</p>
                    @endforeach

                    @php
                        $fallbackReadLimit = (crc32((string) $article->slug) % 2) + 1;
                        $fallbackShouldShow = $relatedSafe->count() && $loop->iteration <= $fallbackReadLimit;
                        $readMore = $fallbackShouldShow ? $relatedSafe->values()->get(($loop->iteration - 1) % max($relatedSafe->count(), 1)) : null;
                    @endphp
                    @if($readMore)
                        <div class="article-read-also">
                            <span>Baca juga:</span>
                            <a href="{{ route('article.show', $readMore->slug ?: $readMore->id) }}">{{ $readMore->title }}</a>
                        </div>
                    @endif
                </section>
            @endforeach
        @else
            <div class="article-toc" id="pembahasan-artikel">
                <div class="article-toc-title">Pembahasan dalam artikel</div>
                <ul>
                    <li><a href="#subbab-1">Mulai dari kebutuhan harian</a></li>
                    <li><a href="#subbab-2">Utamakan aman dan nyaman</a></li>
                    <li><a href="#subbab-3">Belanja secukupnya dulu</a></li>
                </ul>
            </div>

            <section id="subbab-1" class="article-section">
                <h2>Mulai dari kebutuhan harian</h2>
                <p>Untuk memilih perlengkapan anak, mulai dulu dari rutinitas yang paling sering dilakukan di rumah. Barang yang dipakai setiap hari biasanya lebih penting diprioritaskan dibanding perlengkapan tambahan yang hanya dipakai sesekali.</p>
                @if($relatedSafe->count())
                    <div class="article-read-also"><span>Baca juga:</span> <a href="{{ route('article.show', $relatedSafe->first()->slug ?: $relatedSafe->first()->id) }}">{{ $relatedSafe->first()->title }}</a></div>
                @endif
            </section>
            <section id="subbab-2" class="article-section">
                <h2>Utamakan aman dan nyaman</h2>
                <p>Pilih produk yang bahannya aman, mudah dibersihkan, dan nyaman digunakan si kecil. Jangan hanya melihat tampilan, karena yang paling terasa manfaatnya justru fungsi dan kemudahan dipakai sehari-hari.</p>
                @if($relatedSafe->count() > 1)
                    <div class="article-read-also"><span>Baca juga:</span> <a href="{{ route('article.show', $relatedSafe->values()->get(1)->slug ?: $relatedSafe->values()->get(1)->id) }}">{{ $relatedSafe->values()->get(1)->title }}</a></div>
                @endif
            </section>
            <section id="subbab-3" class="article-section">
                <h2>Belanja secukupnya dulu</h2>
                <p>Mulai dari jumlah kecil dulu, terutama untuk produk yang baru pertama kali dicoba. Dengan begitu, orang tua bisa melihat apakah barang tersebut benar-benar cocok sebelum membeli lebih banyak.</p>
            </section>

            <div class="article-note-box">
                <h3>Catatan kecil sebelum membeli</h3>
                <ul>
                    <li>Sesuaikan dengan usia dan kebutuhan anak.</li>
                    <li>Pilih barang yang mudah dirawat.</li>
                    <li>Beli secukupnya dulu agar tidak menumpuk.</li>
                </ul>
            </div>
            <p>Dengan cara ini, belanja kebutuhan anak bisa lebih rapi, hemat, dan tidak membuat orang tua bingung memilih.</p>
        @endif
    </article>

    @if($relatedSafe->count())
    <div class="mt-12">
        <span class="text-coral font-black uppercase tracking-widest text-xs">Artikel Terkait</span>
        <h2 class="font-display text-3xl mt-2">Baca juga</h2>
        <div class="grid md:grid-cols-3 gap-5 mt-5">
            @foreach($relatedSafe as $item)
                <a href="{{ route('article.show', $item->slug ?: $item->id) }}" class="card overflow-hidden block article-related-card">
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

<style>
html{scroll-behavior:smooth}
.article-detail-title{font-size:clamp(2rem,4.6vw,4.1rem);color:#29413f;letter-spacing:-.04em}
.article-meta{color:#6B8A88;font-weight:700;font-size:.95rem}
.article-detail-page{color:#263f3d}
.article-caption{max-width:820px;margin:1.45rem auto .7rem;text-align:center;font-style:italic;font-weight:400;font-size:clamp(.92rem,1.35vw,1.08rem);line-height:1.65;color:#2f3f3d}
.article-caption-line{width:32px;height:2px;background:#263f3d;border-radius:999px;margin:1.25rem auto 1.8rem;opacity:.75}
.article-body{background:transparent!important;border:none!important;box-shadow:none!important;outline:none!important;color:#263f3d;font-weight:400;line-height:1.85;font-size:1.07rem;transform:none!important}
.article-body:hover,.article-body:focus,.article-body:focus-within{background:transparent!important;border:none!important;box-shadow:none!important;outline:none!important;transform:none!important}
.article-body,.article-body *{transition:none!important;animation:none!important}
.article-body p{margin-bottom:1.05rem;font-weight:400}
.article-body strong,.article-body b{font-weight:400}
.article-body h2,.article-body h3,.article-toc-title,.article-read-also span{font-weight:900}
.article-body h2{font-family:var(--font-display,inherit);font-size:1.45rem;line-height:1.25;margin-top:2rem;margin-bottom:.85rem;color:#263f3d;scroll-margin-top:120px}
.article-body h3{font-family:var(--font-display,inherit);font-size:1.25rem;margin-bottom:.65rem;color:#263f3d}
.article-lead{font-size:1.14rem;color:#52706d;line-height:1.85;margin-bottom:1.4rem;font-weight:400}
.article-toc{background:#fff;border:1px solid #e6efee;border-radius:0;padding:1.15rem 1.25rem;margin:1.35rem 0 2rem;box-shadow:none;transform:none!important}
.article-toc:hover,.article-toc:focus-within{border-color:#e6efee;box-shadow:none;transform:none!important;outline:none}
.article-toc-title{color:#263f3d;margin-bottom:.65rem}
.article-toc ul{padding-left:1.25rem;margin:0;list-style:disc}
.article-toc li{margin:.35rem 0;color:#4a706d}
.article-toc a,.article-read-also a{color:#159fe8;text-decoration:underline;text-underline-offset:3px;font-weight:500;transform:none!important}
.article-toc a:hover,.article-read-also a:hover{text-decoration:underline;transform:none!important}
.article-section{border-top:1px solid #edf3f2;padding-top:.65rem;margin-top:1.25rem;box-shadow:none!important;outline:none!important;transform:none!important}
.article-section:hover,.article-section:focus-within{box-shadow:none!important;outline:none!important;transform:none!important;border-top-color:#edf3f2}
.article-read-also{background:transparent;border-left:none;border-radius:0;padding:.55rem 0;margin:1.05rem 0 1.55rem;color:#263f3d;box-shadow:none!important;transform:none!important}
.article-read-also span{margin-right:.35rem}
.article-note-box{background:#effaf8;border:1px solid #c8efea;border-radius:1.5rem;padding:1.25rem 1.5rem;margin:1.8rem 0;box-shadow:none!important;transform:none!important}
.article-note-box:hover,.article-note-box:focus-within{box-shadow:none!important;transform:none!important;outline:none}
.article-related-card,.article-related-card:hover,.article-related-card:focus{transform:none!important;box-shadow:none!important;outline:none!important}
.article-note-box ul{padding-left:1.2rem;margin:0}
.article-note-box li{margin:.35rem 0;color:#52706d;font-weight:400}
@media(max-width:768px){.article-detail-hero{padding-top:2rem;padding-bottom:2rem}.article-body{font-size:1rem}.article-body h2{font-size:1.28rem}.article-toc{padding:1rem}.article-caption{font-size:.95rem;margin-top:1.1rem}}
</style>
@endsection
