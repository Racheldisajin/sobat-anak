@extends('layouts.app')
@section('title','AI Chat SobatAnak — Gemini')
@section('content')
<section class="gemini-page">
    <aside class="gemini-sidebar">
        <div class="gemini-side-brand">
            <span class="gemini-logo-mark"><img src="{{ asset('assets/logo-sobat-anak-bw.png') }}" alt="SobatAnak"></span>
            <div><b>AI SobatAnak</b><small>Mom & Baby Care</small></div>
        </div>
        <a href="{{ route('home') }}" class="gemini-side-link gemini-back-home">← Kembali ke Beranda</a>
        <a href="{{ route('ai-chat.new') }}" class="gemini-new-chat">＋ Percakapan baru</a>
        <div class="gemini-recent-title">Terbaru</div>
        <div class="gemini-recent-list" id="geminiRecentList">
            @forelse($sessions as $s)
                <div class="gemini-session-row {{ $activeSession && $activeSession->id === $s->id ? 'active' : '' }}" data-session-id="{{ $s->id }}">
                    <a class="gemini-session-link" href="{{ route('ai-chat.session', $s->id) }}" title="{{ $s->title }}">{{ $s->title }}</a>
                    <form class="gemini-delete-session-form" action="{{ route('ai-chat.session.destroy', $s->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="gemini-delete-session" aria-label="Hapus percakapan {{ $s->title }}">×</button>
                    </form>
                </div>
            @empty
                <p class="gemini-empty-history">Belum ada percakapan.</p>
            @endforelse
        </div>
    </aside>

    <main class="gemini-chat-main">
        <div class="gemini-chat-bg"></div>
        <div class="gemini-chat-top">
            <div class="gemini-chat-heading">
                <div>
                    <span>AI Chat SobatAnak</span>
                    <h1>Asisten Mom & Baby Care</h1>
                </div>
            </div>
            <a href="{{ route('products') }}">Lihat Produk</a>
        </div>

        <div class="gemini-chat-area" id="geminiChatArea">
            @if($messages->isEmpty())
                <div class="gemini-welcome" id="geminiWelcome">
                    <h2>Apa yang ingin kamu tanyakan?</h2>
                    <p>Tanya kebutuhan bayi, parenting, kesehatan anak umum, produk, artikel, MPASI, atau rekomendasi belanja SobatAnak.</p>
                    <div class="gemini-prompt-grid">
                        <button type="button">Halo, aku mau tanya kebutuhan bayi</button>
                        <button type="button">Cara memilih popok newborn yang nyaman?</button>
                        <button type="button">Anak susah makan sebaiknya bagaimana?</button>
                        <button type="button">Mainan edukatif yang cocok untuk anak 3 tahun</button>
                    </div>
                </div>
            @else
                @foreach($messages as $m)
                    @php
                        $storedProducts = $messageRecommendations[$m->id]['products'] ?? collect();
                        $storedArticles = $messageRecommendations[$m->id]['articles'] ?? collect();
                        $storedChoices = $messageRecommendations[$m->id]['quick_choices'] ?? collect();
                    @endphp
                    <div class="gemini-message {{ $m->role === 'user' ? 'user' : 'assistant' }}">
                        <div class="gemini-avatar {{ $m->role === 'assistant' ? 'ai-logo' : '' }}">
                            @if($m->role === 'user')
                                {{ strtoupper(substr($authUser?->name ?? 'U', 0, 1)) }}
                            @else
                                <img src="{{ asset('assets/logo-sobat-anak-bw.png') }}" alt="AI SobatAnak">
                            @endif
                        </div>
                        <div class="gemini-bubble">
                            {!! nl2br(e($m->message)) !!}

                            @if($m->role === 'assistant' && $storedProducts->count())
                                <div class="gemini-rec-wrap">
                                    <div class="gemini-rec-title">Rekomendasi produk SobatAnak</div>
                                    <div class="gemini-rec-grid">
                                        @foreach($storedProducts->sortByDesc(fn($product) => ((float) ($product->rating ?? 0) * 100000) + (int) ($product->sold ?? 0)) as $product)
                                            <a class="gemini-product-card" href="{{ route('product.show', $product->id) }}">
                                                <img src="{{ $product->image }}" alt="{{ $product->name }}">
                                                <span>
                                                    <b>{{ $product->name }}</b>
                                                    <small>{{ $product->category }} · ⭐ {{ $product->rating }}</small>
                                                    <strong>Rp {{ number_format((int) $product->price, 0, ',', '.') }}</strong>
                                                    <small>{{ ((int)($product->stock ?? 0) <= 0) ? 'Stok habis' : (((int)($product->stock ?? 0) <= 3) ? 'Stok tinggal sedikit' : 'Stok tersedia') }}</small>
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($m->role === 'assistant' && $storedArticles->count())
                                <div class="gemini-rec-wrap">
                                    <div class="gemini-rec-title">Artikel pendukung</div>
                                    @foreach($storedArticles as $article)
                                        <a class="gemini-article-card" href="{{ route('article.show', $article->slug ?: $article->id) }}">
                                            <small>{{ $article->category_name }}</small>
                                            <b>{{ $article->title }}</b>
                                            <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $article->content), 110) }}</p>
                                            <span class="gemini-article-cta">Baca artikel →</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if($m->role === 'assistant' && $storedChoices->count())
                                <div class="gemini-choice-wrap">
                                    <div class="gemini-choice-title">Pilih lanjutan cepat</div>
                                    <div class="gemini-choice-grid">
                                        @foreach($storedChoices as $choice)
                                            <button type="button" class="gemini-choice-btn" data-choice="{{ $choice }}">{{ $choice }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <form class="gemini-compose" id="geminiChatForm" autocomplete="off">
            <button type="button" class="gemini-plus" aria-label="Tambah">＋</button>
            <input id="geminiChatInput" value="{{ $initialQuestion }}" placeholder="Minta AI SobatAnak" aria-label="Tanya AI SobatAnak">
            <button type="submit" class="gemini-send">Kirim</button>
        </form>
    </main>
</section>

<style>
:root{
    --sa-bg:#fffaf3;
    --sa-panel:#ffffff;
    --sa-mint:#4fc7b7;
    --sa-mint-soft:#e8fbf8;
    --sa-coral:#f3716b;
    --sa-coral-soft:#fff1ef;
    --sa-blue:#2f76d6;
    --sa-yellow:#ffe8a3;
    --sa-text:#263d3b;
    --sa-muted:#6e8b88;
    --sa-line:#c9efeb;
}
html,body{overflow-x:hidden}body{background:var(--sa-bg);color:var(--sa-text)}
.site-header-inner,.mobile-menu{display:none!important}footer{display:none!important}
.gemini-page{min-height:100vh;background:linear-gradient(135deg,#fffaf3 0%,#effdf9 48%,#fff4f1 100%);color:var(--sa-text);display:grid;grid-template-columns:minmax(260px,300px) minmax(0,1fr);font-family:Nunito,system-ui,sans-serif;overflow-x:hidden}.gemini-page *{box-sizing:border-box}
.gemini-sidebar{background:linear-gradient(180deg,#ffffff 0%,#f1fffc 100%);border-right:1px solid var(--sa-line);padding:1rem .75rem;display:flex;flex-direction:column;gap:.75rem;position:sticky;top:0;height:100vh;box-shadow:12px 0 35px rgba(79,199,183,.10);z-index:20;overflow-x:hidden;max-width:100%}
.gemini-side-brand{display:flex;align-items:center;gap:.65rem;padding:.45rem .45rem;margin-bottom:.25rem;color:var(--sa-text);min-width:0}
.gemini-star{width:2.35rem;height:2.35rem;border-radius:999px;display:grid;place-items:center;background:linear-gradient(135deg,#53c8bb,#f3716b);font-weight:1000;color:white;box-shadow:0 10px 24px rgba(243,113,107,.22)}.gemini-logo-mark{width:2.65rem;height:2.65rem;border-radius:1rem;display:grid;place-items:center;background:linear-gradient(135deg,#e8fbf8,#fff);border:1px solid var(--sa-line);overflow:hidden;flex:0 0 auto}.gemini-logo-mark img{width:2.05rem;height:2.05rem;object-fit:contain}
.gemini-side-brand b{display:block;font-size:.98rem;color:var(--sa-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.gemini-side-brand small{display:block;color:var(--sa-coral);font-weight:1000;letter-spacing:.05em;text-transform:uppercase;font-size:.72rem}
.gemini-new-chat,.gemini-side-link{display:block;text-decoration:none;color:var(--sa-text);border-radius:999px;padding:.74rem .82rem;font-weight:1000;transition:.2s ease;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.gemini-session-link{min-width:0;flex:1;display:block;text-decoration:none;color:var(--sa-text);font-weight:1000;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding:.66rem .2rem .66rem .72rem;font-size:.82rem;line-height:1.25}
.gemini-new-chat{background:linear-gradient(135deg,var(--sa-mint-soft),#fff);border:1px solid var(--sa-line)}.gemini-side-link{color:#41615e;background:#fff;border:1px solid transparent}.gemini-back-home{background:linear-gradient(135deg,#fff6d8,#fff);border-color:#ffd6d2;color:#41615e}.gemini-new-chat:hover,.gemini-side-link:hover,.gemini-session-row:hover,.gemini-session-row.active{background:linear-gradient(135deg,#fff6d8,#fff);border-color:#ffd6d2;transform:translateX(2px)}
.gemini-recent-title{color:var(--sa-muted);font-weight:1000;font-size:.76rem;margin:.85rem .55rem .08rem;letter-spacing:.08em;text-transform:uppercase}.gemini-recent-list{display:grid;gap:.3rem;overflow-y:auto;overflow-x:hidden;padding:0 .05rem 1rem;max-width:100%;scrollbar-width:thin;scrollbar-color:#9de8df transparent}.gemini-recent-list::-webkit-scrollbar{width:6px;height:0}.gemini-recent-list::-webkit-scrollbar-thumb{background:#9de8df;border-radius:999px}.gemini-session-row{display:flex;align-items:center;gap:.18rem;border-radius:.95rem;border:1px solid transparent;transition:.2s ease;min-width:0;max-width:100%;overflow:hidden}.gemini-session-row.active .gemini-session-link{color:#f3716b}.gemini-delete-session-form{margin:0;display:flex;align-items:center}.gemini-delete-session{width:1.75rem;height:1.75rem;border:0;border-radius:999px;background:rgba(243,113,107,.12);color:#f3716b;font-size:1rem;font-weight:1000;line-height:1;cursor:pointer;opacity:.72;transition:.2s ease;margin-right:.25rem;flex:0 0 auto}.gemini-delete-session:hover{opacity:1;background:#f3716b;color:#fff;transform:scale(1.08)}.gemini-empty-history{color:var(--sa-muted);font-weight:800;padding:.6rem}
.gemini-chat-main{position:relative;min-height:100vh;display:flex;flex-direction:column;overflow:hidden}.gemini-chat-bg{position:absolute;inset:0;background:radial-gradient(circle at 16% 18%,rgba(255,232,163,.55),transparent 22%),radial-gradient(circle at 85% 12%,rgba(79,199,183,.20),transparent 28%),radial-gradient(circle at 70% 78%,rgba(243,113,107,.13),transparent 34%);pointer-events:none}.gemini-chat-bg:after{content:'';position:absolute;inset:auto 0 0 0;height:220px;background:linear-gradient(0deg,rgba(79,199,183,.13),transparent)}
.gemini-chat-top{position:sticky;top:0;z-index:6;padding:1.05rem clamp(1rem,2.5vw,2rem);display:flex;align-items:center;justify-content:space-between;color:var(--sa-text);background:rgba(255,250,243,.78);backdrop-filter:blur(16px);border-bottom:1px solid rgba(201,239,235,.72)}.gemini-chat-top span{font-weight:1000;color:var(--sa-mint);letter-spacing:.14em;text-transform:uppercase;font-size:.74rem}.gemini-chat-top h1{font-size:1.35rem;font-weight:1000;margin:.1rem 0 0;letter-spacing:-.02em}.gemini-chat-top a{color:#fff;background:linear-gradient(135deg,var(--sa-blue),#52a6ff);text-decoration:none;padding:.8rem 1.18rem;border-radius:999px;font-weight:1000;box-shadow:0 12px 28px rgba(47,118,214,.22);white-space:nowrap}
.gemini-chat-area{position:relative;z-index:1;width:min(1160px,calc(100% - 2rem));margin:0 auto;padding:2rem 0 8.8rem;height:calc(100vh - 78px);overflow-y:auto;overflow-x:hidden;scroll-behavior:smooth;scrollbar-width:thin;scrollbar-color:#8ee4da transparent}.gemini-chat-area::-webkit-scrollbar,.gemini-recent-list::-webkit-scrollbar{width:8px}.gemini-chat-area::-webkit-scrollbar-track,.gemini-recent-list::-webkit-scrollbar-track{background:transparent}.gemini-chat-area::-webkit-scrollbar-thumb,.gemini-recent-list::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#8ee4da,#ffd6d2);border-radius:999px;border:2px solid transparent;background-clip:content-box}.gemini-chat-area::-webkit-scrollbar-thumb:hover{background:#4fc7b7}
.gemini-welcome{min-height:58vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}.gemini-welcome h2{font-size:clamp(2rem,4vw,3rem);font-weight:1000;letter-spacing:-.04em;color:var(--sa-text)}.gemini-welcome p{color:var(--sa-muted);font-weight:900;margin-top:.9rem;max-width:640px}.gemini-prompt-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem;margin-top:2rem;width:min(760px,100%)}.gemini-prompt-grid button{background:#fff;border:1px solid var(--sa-line);border-radius:1.25rem;color:var(--sa-text);text-align:left;padding:1rem;font-weight:1000;transition:.2s;box-shadow:0 12px 30px rgba(79,199,183,.08)}.gemini-prompt-grid button:hover{background:var(--sa-mint-soft);transform:translateY(-2px)}
.gemini-message{display:grid;grid-template-columns:46px minmax(0,1fr);gap:1rem;margin:1rem 0 1.3rem;animation:geminiIn .28s ease both}.gemini-message.user{grid-template-columns:minmax(0,1fr) 46px}.gemini-message.user .gemini-avatar{grid-column:2}.gemini-message.user .gemini-bubble{grid-column:1;grid-row:1;justify-self:end;max-width:min(72%,760px);background:linear-gradient(135deg,#2f76d6,#4da4ff);color:#fff;border-color:rgba(255,255,255,.15);box-shadow:0 16px 32px rgba(47,118,214,.18)}.gemini-avatar{width:46px;height:46px;border-radius:999px;display:grid;place-items:center;background:linear-gradient(135deg,#4fc7b7,#f3716b);font-weight:1000;color:white;box-shadow:0 12px 26px rgba(243,113,107,.18);flex:0 0 auto}.gemini-bubble{background:rgba(255,255,255,.92);border:1px solid var(--sa-line);border-radius:1.45rem;padding:1.15rem 1.25rem;color:var(--sa-text);font-weight:850;line-height:1.72;white-space:normal;box-shadow:0 18px 44px rgba(79,199,183,.13)}
.gemini-rec-wrap{display:grid;gap:.85rem;margin-top:1.1rem}.gemini-rec-title{color:#269e91;font-weight:1000}.gemini-rec-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}.gemini-product-card{display:flex;gap:.8rem;align-items:center;background:linear-gradient(135deg,#ffffff,#f0fffc);border:1px solid var(--sa-line);border-radius:1.15rem;padding:.82rem;text-decoration:none;color:var(--sa-text);transition:.2s;min-height:96px}.gemini-product-card:hover,.gemini-article-card:hover{transform:translateY(-3px);border-color:#8ee4da;box-shadow:0 16px 34px rgba(79,199,183,.15)}.gemini-product-card img{width:68px;height:68px;border-radius:1rem;object-fit:cover;background:#fff}.gemini-product-card b{display:block;font-weight:1000}.gemini-product-card small{display:block;color:var(--sa-muted);font-weight:900}.gemini-product-card strong{display:block;color:#16a395;font-weight:1000}.gemini-article-card{display:block;text-decoration:none;background:linear-gradient(135deg,#fff,#fff7f5);border:1px solid #ffd6d2;border-radius:1.15rem;padding:.9rem;color:var(--sa-text);transition:.2s}.gemini-article-card small{display:block;color:var(--sa-coral);font-weight:1000}.gemini-article-card b{font-weight:1000}.gemini-article-card p{color:var(--sa-muted);font-weight:850;margin-top:.3rem}.gemini-typing{display:inline-flex;gap:.28rem}.gemini-typing i{width:.5rem;height:.5rem;background:#4fc7b7;border-radius:999px;animation:geminiDot 1s infinite}.gemini-typing i:nth-child(2){animation-delay:.12s}.gemini-typing i:nth-child(3){animation-delay:.24s}

.gemini-choice-wrap{margin-top:1.15rem;padding-top:.95rem;border-top:1px dashed rgba(79,199,183,.45)}
.gemini-choice-title{font-size:.9rem;font-weight:1000;color:#269e91;margin-bottom:.65rem}
.gemini-choice-grid{display:flex;flex-wrap:wrap;gap:.6rem}
.gemini-choice-btn{border:1px solid #bdece7;background:linear-gradient(135deg,#ffffff,#effdfa);color:#263d3b;border-radius:999px;padding:.68rem .9rem;font-weight:1000;cursor:pointer;box-shadow:0 10px 22px rgba(79,199,183,.10);transition:.2s ease;text-align:left;line-height:1.25}
.gemini-choice-btn:hover{transform:translateY(-2px);border-color:#4fc7b7;background:linear-gradient(135deg,#e8fbf8,#fff7dc)}
.gemini-compose{position:fixed;z-index:10;left:300px;right:0;bottom:1rem;width:min(1060px,calc(100vw - 340px));margin:0 auto;display:flex;align-items:center;gap:.7rem;background:rgba(255,255,255,.95);border:1px solid var(--sa-line);border-radius:999px;padding:.68rem;box-shadow:0 18px 60px rgba(79,199,183,.18);backdrop-filter:blur(12px)}.gemini-compose input{flex:1;background:transparent;border:0;outline:0;color:var(--sa-text);font-size:1rem;font-weight:900;min-width:0}.gemini-compose input::placeholder{color:#8fa5a2}.gemini-plus,.gemini-send{border:0;border-radius:999px;color:#fff;font-weight:1000}.gemini-plus{background:linear-gradient(135deg,#f3716b,#ff9c93);font-size:1.35rem;width:2.55rem;height:2.55rem}.gemini-send{background:linear-gradient(135deg,#2f76d6,#51a8ff);padding:.86rem 1.35rem;box-shadow:0 10px 24px rgba(47,118,214,.2)}.gemini-send:disabled{opacity:.6}.gemini-fade-out{opacity:0;transform:translateY(8px);transition:.2s}
@keyframes geminiIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}@keyframes geminiDot{0%,80%,100%{opacity:.28;transform:translateY(0)}40%{opacity:1;transform:translateY(-5px)}}
@media(max-width:1080px){.gemini-page{grid-template-columns:260px minmax(0,1fr)}.gemini-compose{left:260px;width:min(900px,calc(100vw - 300px))}.gemini-chat-area{width:min(980px,calc(100% - 1.4rem))}.gemini-rec-grid{grid-template-columns:1fr}}
@media(max-width:820px){.gemini-page{grid-template-columns:1fr}.gemini-sidebar{display:none}.gemini-compose{left:0;right:0;width:min(760px,92vw)}.gemini-chat-area{width:min(760px,92vw);height:calc(100vh - 74px);padding-bottom:7rem}.gemini-rec-grid,.gemini-prompt-grid{grid-template-columns:1fr}.gemini-chat-top{padding:1rem}.gemini-message.user .gemini-bubble{max-width:86%}}

/* Patch: sidebar no horizontal scroll + SobatAnak logo + tidy header */
html,body{max-width:100%;overflow-x:hidden!important}
.gemini-page{overflow-x:hidden!important;grid-template-columns:minmax(250px,300px) minmax(0,1fr)!important}
.gemini-sidebar{overflow-x:hidden!important;max-width:100%!important;padding-left:.75rem!important;padding-right:.75rem!important}
.gemini-side-brand{min-width:0!important}
.gemini-logo-mark{width:2.7rem;height:2.7rem;border-radius:1rem;display:grid;place-items:center;background:#fff;border:1px solid var(--sa-line);box-shadow:0 10px 22px rgba(79,199,183,.14);overflow:hidden;flex:0 0 auto}.gemini-logo-mark img{width:2.05rem;height:2.05rem;object-fit:contain}
.gemini-side-brand b{font-size:.98rem!important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.gemini-side-brand small{font-size:.72rem!important;color:var(--sa-coral)!important}
.gemini-back-home{order:1!important;background:linear-gradient(135deg,#fff7dc,#fff)!important;border:1px solid #ffd6d2!important;color:#45615f!important}.gemini-new-chat{order:2!important}.gemini-recent-title{order:3!important}.gemini-recent-list{order:4!important;overflow-y:auto!important;overflow-x:hidden!important;max-width:100%;scrollbar-width:thin;scrollbar-color:#9de8df transparent}.gemini-recent-list::-webkit-scrollbar{width:6px;height:0}.gemini-recent-list::-webkit-scrollbar-thumb{background:#9de8df;border-radius:999px}
.gemini-session-row{min-width:0!important;max-width:100%!important;width:100%!important;overflow:hidden!important;display:flex!important}.gemini-session-link{font-size:.82rem!important;line-height:1.25!important;min-width:0!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;padding:.64rem .16rem .64rem .72rem!important}.gemini-delete-session-form{flex:0 0 auto!important}.gemini-delete-session{width:1.72rem!important;height:1.72rem!important;font-size:1rem!important;margin-right:.22rem!important}
.gemini-chat-main{min-width:0!important;overflow-x:hidden!important}.gemini-chat-top{padding:.85rem clamp(1rem,2.5vw,2rem)!important;gap:1rem!important}.gemini-chat-heading{display:flex;align-items:center;gap:.8rem;min-width:0}.gemini-header-logo{width:5rem;height:3.05rem;border-radius:1rem;background:#fff;border:1px solid var(--sa-line);display:flex;align-items:center;justify-content:center;padding:.22rem;box-shadow:0 12px 24px rgba(79,199,183,.12);overflow:hidden;flex:0 0 auto}.gemini-header-logo img{max-width:100%;max-height:100%;object-fit:contain}.gemini-chat-heading h1{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.gemini-chat-heading span:not(.gemini-header-logo){display:block}
.gemini-avatar.ai-logo{background:#fff!important;border:1px solid var(--sa-line);padding:.38rem;overflow:hidden}.gemini-avatar.ai-logo img{width:100%;height:100%;object-fit:contain}
@media(max-width:820px){.gemini-page{grid-template-columns:1fr!important}.gemini-header-logo{width:3.8rem;height:2.55rem}.gemini-chat-heading h1{font-size:1.05rem!important}}


/* PATCH: chat area dibuat lebih responsif, scrollbar mentok kanan, dan jawaban AI tetap nyaman dibaca */
html, body{
    width:100%;
    min-height:100%;
    overflow:hidden!important;
}
.gemini-chat-main{
    height:100vh!important;
    min-width:0!important;
    overflow:hidden!important;
}
.gemini-chat-area{
    width:100%!important;
    max-width:none!important;
    margin:0!important;
    height:calc(100vh - 78px)!important;
    padding:2rem clamp(1rem,3vw,3rem) 8.9rem clamp(1rem,3vw,3rem)!important;
    overflow-y:auto!important;
    overflow-x:hidden!important;
    scrollbar-gutter:stable;
    scrollbar-width:thin;
    scrollbar-color:#4fc7b7 rgba(255,255,255,.45);
}
.gemini-chat-area::-webkit-scrollbar{
    width:12px!important;
}
.gemini-chat-area::-webkit-scrollbar-track{
    background:rgba(255,255,255,.58)!important;
    border-left:1px solid rgba(79,199,183,.22);
}
.gemini-chat-area::-webkit-scrollbar-thumb{
    background:linear-gradient(180deg,#4fc7b7,#8ee4da,#f9b0aa)!important;
    border-radius:999px!important;
    border:3px solid rgba(255,255,255,.72)!important;
    background-clip:padding-box!important;
}
.gemini-message{
    width:min(100%,1180px)!important;
    margin-left:auto!important;
    margin-right:auto!important;
}
.gemini-message.assistant .gemini-bubble{
    max-width:100%!important;
}
.gemini-message.user .gemini-bubble{
    max-width:min(82%,820px)!important;
}
.gemini-rec-grid{
    grid-template-columns:repeat(2,minmax(0,1fr))!important;
}
.gemini-compose{
    left:calc(300px + clamp(1rem,3vw,3rem))!important;
    right:clamp(1rem,3vw,3rem)!important;
    bottom:1rem!important;
    width:auto!important;
    max-width:none!important;
    margin:0!important;
}
.gemini-chat-top{
    min-height:78px!important;
    padding:.85rem clamp(1rem,3vw,3rem)!important;
}
.gemini-bubble{
    font-weight:800!important;
    line-height:1.78!important;
}
@media(max-width:1080px){
    .gemini-compose{left:calc(260px + 1rem)!important;right:1rem!important;width:auto!important;}
    .gemini-chat-area{padding-left:1rem!important;padding-right:1rem!important;}
}
@media(max-width:820px){
    html,body{overflow:auto!important;}
    .gemini-chat-main{height:100vh!important;}
    .gemini-chat-area{width:100%!important;height:calc(100vh - 74px)!important;padding:1rem 1rem 7.2rem!important;}
    .gemini-compose{left:1rem!important;right:1rem!important;width:auto!important;}
    .gemini-rec-grid{grid-template-columns:1fr!important;}
    .gemini-message.user .gemini-bubble{max-width:88%!important;}
}


/* PATCH: logo header dihapus + ikon AI dibuat simple supaya lebih rapi */
.gemini-chat-heading{gap:0!important}
.gemini-header-logo{display:none!important}
.gemini-logo-mark{background:linear-gradient(135deg,#4fc7b7,#f3716b)!important;color:#fff!important;font-size:1.15rem!important;font-weight:1000!important}
.gemini-avatar.ai-logo{background:linear-gradient(135deg,#4fc7b7,#f3716b)!important;color:#fff!important;border:0!important;padding:0!important;font-size:1.1rem!important;font-weight:1000!important}
.gemini-avatar.ai-logo img{display:none!important}
.gemini-bubble{font-weight:760!important;line-height:1.85!important}
.gemini-bubble strong,.gemini-bubble b{font-weight:1000!important}



/* PATCH: logo SobatAnak hitam-putih + artikel pendukung lebih relevan dan jelas bisa diklik */
.gemini-logo-mark{
    background:linear-gradient(135deg,#0f2624,#203d3a)!important;
    border-color:rgba(255,255,255,.18)!important;
    box-shadow:0 12px 28px rgba(38,61,59,.20)!important;
}
.gemini-logo-mark img{
    width:2.35rem!important;
    height:2.35rem!important;
    object-fit:contain!important;
    filter:drop-shadow(0 2px 4px rgba(0,0,0,.20));
}
.gemini-avatar.ai-logo{
    background:linear-gradient(135deg,#0f2624,#203d3a)!important;
    border:2px solid rgba(79,199,183,.42)!important;
    padding:.28rem!important;
    box-shadow:0 14px 30px rgba(38,61,59,.18)!important;
}
.gemini-avatar.ai-logo img{
    width:100%!important;
    height:100%!important;
    object-fit:contain!important;
    display:block!important;
}
.gemini-article-card{
    position:relative;
    overflow:hidden;
}
.gemini-article-card::after{
    content:"";
    position:absolute;
    inset:auto 0 0 0;
    height:4px;
    background:linear-gradient(90deg,#4fc7b7,#ffe8a3,#f3716b);
    opacity:.75;
}
.gemini-article-cta{
    display:inline-flex;
    margin-top:.55rem;
    color:#16a395;
    font-weight:1000;
    font-size:.9rem;
}
.gemini-article-card:hover .gemini-article-cta{
    transform:translateX(4px);
    transition:.18s ease;
}



/* PATCH: ganti logo AI Chat memakai logo bulat SobatAnak yang dikirim user */
.gemini-logo-mark{
    background:#ffffff!important;
    border:1.5px solid rgba(79,199,183,.38)!important;
    box-shadow:0 12px 28px rgba(79,199,183,.16)!important;
    border-radius:1rem!important;
    padding:.18rem!important;
}
.gemini-logo-mark img{
    width:2.35rem!important;
    height:2.35rem!important;
    object-fit:contain!important;
    filter:none!important;
    display:block!important;
}
.gemini-avatar.ai-logo{
    background:#ffffff!important;
    border:1.5px solid rgba(79,199,183,.42)!important;
    padding:.32rem!important;
    box-shadow:0 14px 30px rgba(79,199,183,.16)!important;
}
.gemini-avatar.ai-logo img{
    width:100%!important;
    height:100%!important;
    object-fit:contain!important;
    display:block!important;
    filter:none!important;
}
</style>

<script>
(() => {
    const form = document.getElementById('geminiChatForm');
    if (!form) return;
    const input = document.getElementById('geminiChatInput');
    const area = document.getElementById('geminiChatArea');
    const welcome = document.getElementById('geminiWelcome');
    const promptGrid = document.querySelector('.gemini-prompt-grid');
    const recentList = document.getElementById('geminiRecentList');
    const userInitial = @json(strtoupper(substr($authUser?->name ?? 'U', 0, 1)));
    let sessionId = @json($activeSession?->id);
    let sending = false;

    function csrf(){ return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
    function esc(text){ return String(text || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
    function hideWelcome(){ if (welcome) { welcome.classList.add('gemini-fade-out'); setTimeout(()=>welcome.remove(), 180); } }
    function scrollBottom(){ area.scrollTo({top: area.scrollHeight, behavior: 'smooth'}); }
    function addMessage(role, html){
        hideWelcome();
        const wrap = document.createElement('div');
        wrap.className = 'gemini-message ' + role;
        wrap.innerHTML = role === 'user' ? `<div class="gemini-avatar">${esc(userInitial)}</div><div class="gemini-bubble">${html}</div>` : `<div class="gemini-avatar ai-logo"><img src="{{ asset('assets/logo-sobat-anak-bw.png') }}" alt="SobatAnak AI"></div><div class="gemini-bubble">${html}</div>`;
        area.appendChild(wrap);
        scrollBottom();
        return wrap.querySelector('.gemini-bubble');
    }

    function syncSessionLane(data){
        if (!recentList || !data.session_id) return;
        const url = data.session_url || ('/ai-chat/session/' + data.session_id);
        let row = recentList.querySelector(`[data-session-id="${data.session_id}"]`);
        let link = row ? row.querySelector('.gemini-session-link') : null;
        if (!row) {
            const empty = recentList.querySelector('.gemini-empty-history');
            if (empty) empty.remove();
            row = document.createElement('div');
            row.className = 'gemini-session-row active';
            row.dataset.sessionId = data.session_id;
            row.innerHTML = `<a class="gemini-session-link" href="${url}"></a><form class="gemini-delete-session-form" action="${url}" method="POST"><input type="hidden" name="_token" value="${csrf()}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="gemini-delete-session" aria-label="Hapus percakapan">×</button></form>`;
            recentList.prepend(row);
            link = row.querySelector('.gemini-session-link');
        }
        recentList.querySelectorAll('.gemini-session-row').forEach(a => a.classList.remove('active'));
        row.classList.add('active');
        link.href = url;
        const title = data.session_title || 'Percakapan baru';
        link.textContent = title;
        link.title = title;
        const form = row.querySelector('.gemini-delete-session-form');
        if (form) form.action = url;
    }
    function productCard(p){
        return `<a class="gemini-product-card" href="${p.url}"><img src="${esc(p.image)}" alt=""><span><b>${esc(p.name)}</b><small>${esc(p.category)} · ⭐ ${esc(p.rating)}</small><strong>${esc(p.price)}</strong><small>${esc(p.stock_status)}</small></span></a>`;
    }
    function articleCard(a){
        return `<a class="gemini-article-card" href="${esc(a.url || '#')}"><small>${esc(a.category)}</small><b>${esc(a.title)}</b><p>${esc(a.excerpt)}</p></a>`;
    }
    function choiceButtons(choices){
        if (!choices || !choices.length) return '';
        return `<div class="gemini-choice-wrap"><div class="gemini-choice-title">Pilih lanjutan cepat</div><div class="gemini-choice-grid">${choices.map(c => `<button type="button" class="gemini-choice-btn" data-choice="${esc(c)}">${esc(c)}</button>`).join('')}</div></div>`;
    }
    async function ask(text){
        if (!text || sending) return;
        sending = true;
        form.querySelector('.gemini-send').disabled = true;
        addMessage('user', esc(text));
        const botBubble = addMessage('assistant', '<span class="gemini-typing"><i></i><i></i><i></i></span>');
        try {
            const response = await fetch('{{ route('ai-chat.ask') }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf()},
                body: JSON.stringify({message: text, session_id: sessionId})
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'Gagal memproses chat.');
            sessionId = data.session_id || sessionId;
            syncSessionLane(data);
            if (data.session_url && !location.pathname.includes('/ai-chat/session/')) {
                history.replaceState({}, '', data.session_url);
            }
            let html = esc(data.answer || 'Maaf, AI belum memberi jawaban.').replace(/\n/g, '<br>');
            if (data.products && data.products.length) {
                data.products.sort((a,b) => (parseFloat(b.rating || 0) - parseFloat(a.rating || 0)) || ((parseInt(b.sold || 0) || 0) - (parseInt(a.sold || 0) || 0)));
                html += `<div class="gemini-rec-wrap"><div class="gemini-rec-title">Rekomendasi produk SobatAnak</div><div class="gemini-rec-grid">${data.products.map(productCard).join('')}</div></div>`;
            }
            if (data.articles && data.articles.length) html += `<div class="gemini-rec-wrap"><div class="gemini-rec-title">Artikel pendukung</div>${data.articles.map(articleCard).join('')}</div>`;
            html += choiceButtons(data.quick_choices || []);
            botBubble.innerHTML = html;
            scrollBottom();
        } catch (e) {
            botBubble.innerHTML = 'Maaf, chat belum bisa diproses. Pastikan GEMINI_API_KEY sudah diisi di file .env, lalu jalankan php artisan config:clear.';
        } finally {
            sending = false;
            form.querySelector('.gemini-send').disabled = false;
            input.focus();
        }
    }
    form.addEventListener('submit', e => {
        e.preventDefault();
        const text = input.value.trim();
        input.value = '';
        ask(text);
    });

    area.addEventListener('click', e => {
        const choice = e.target.closest('.gemini-choice-btn');
        if (!choice) return;
        const text = choice.dataset.choice || choice.textContent.trim();
        ask(text);
    });

    if (recentList) {
        recentList.addEventListener('submit', async (e) => {
            const deleteForm = e.target.closest('.gemini-delete-session-form');
            if (!deleteForm) return;
            e.preventDefault();
            const row = deleteForm.closest('.gemini-session-row');
            const deleteUrl = deleteForm.action;
            if (!confirm('Hapus percakapan ini?')) return;
            try {
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {'Accept':'application/json','X-CSRF-TOKEN': csrf()}
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.ok === false) throw new Error(data.message || 'Gagal menghapus percakapan.');
                const isActive = row && row.classList.contains('active');
                if (row) row.remove();
                if (!recentList.querySelector('.gemini-session-row')) {
                    recentList.innerHTML = '<p class="gemini-empty-history">Belum ada percakapan.</p>';
                }
                if (isActive) window.location.href = data.redirect || '{{ route('ai-chat.page') }}';
            } catch (err) {
                alert('Percakapan belum bisa dihapus. Coba refresh lalu ulangi lagi.');
            }
        });
    }

    if (promptGrid) {
        promptGrid.addEventListener('click', e => {
            const btn = e.target.closest('button');
            if (!btn) return;
            ask(btn.textContent.trim());
        });
    }
    if (input.value.trim()) {
        const first = input.value.trim();
        input.value = '';
        setTimeout(() => ask(first), 350);
    }
    scrollBottom();
})();
</script>
@endsection
