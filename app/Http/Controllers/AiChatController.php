<?php
namespace App\Http\Controllers;

use App\Models\{AiChatMessage, AiChatSession, Product, Post, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function newSession(Request $request)
    {
        $authUser = session('user_id') ? User::find(session('user_id')) : null;

        if (!$authUser) {
            session(['ai_chat_after_login' => route('ai-chat.new')]);
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Silakan login dulu untuk memakai AI Mom & Baby SobatAnak.']);
        }

        $session = AiChatSession::create([
            'user_id' => $authUser->id,
            'title' => 'Percakapan baru',
            'source' => 'gemini_chat_session',
        ]);

        return redirect()->route('ai-chat.session', $session->id);
    }

    public function page(Request $request, ?AiChatSession $session = null)
    {
        $authUser = session('user_id') ? User::find(session('user_id')) : null;

        if (!$authUser) {
            session(['ai_chat_after_login' => $request->fullUrl()]);
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Silakan login dulu untuk memakai AI Mom & Baby SobatAnak.']);
        }

        $sessions = AiChatSession::query()
            ->where('user_id', $authUser->id)
            ->latest('updated_at')
            ->take(12)
            ->get();

        $messages = collect();
        $messageRecommendations = [];

        if ($session) {
            if ((int) $session->user_id !== (int) $authUser->id) {
                abort(403);
            }

            $messages = $session->messages()->oldest()->get();

            $productIds = collect();
            $articleIds = collect();
            foreach ($messages as $message) {
                $recommendations = is_array($message->recommendations) ? $message->recommendations : [];
                $productIds = $productIds->merge($recommendations['products'] ?? []);
                $articleIds = $articleIds->merge($recommendations['articles'] ?? []);
            }

            $productsById = Product::whereIn('id', $productIds->unique()->filter()->values())->get()->keyBy('id');
            $articlesById = Post::with('category')->whereIn('id', $articleIds->unique()->filter()->values())->get()->keyBy('id');

            foreach ($messages as $message) {
                $recommendations = is_array($message->recommendations) ? $message->recommendations : [];
                $messageRecommendations[$message->id] = [
                    'products' => collect($recommendations['products'] ?? [])->map(fn ($id) => $productsById->get($id))->filter()->sortByDesc(fn ($product) => ((float) ($product->rating ?? 0) * 100000) + (int) ($product->sold ?? 0))->values(),
                    'articles' => collect($recommendations['articles'] ?? [])->map(fn ($id) => $articlesById->get($id))->filter()->values(),
                    'quick_choices' => collect($recommendations['quick_choices'] ?? [])->filter()->take(4)->values(),
                ];
            }
        }

        return view('pages.ai-chat-session', [
            'authUser' => $authUser,
            'sessions' => $sessions,
            'activeSession' => $session,
            'messages' => $messages,
            'messageRecommendations' => $messageRecommendations,
            'initialQuestion' => trim((string) $request->query('q', '')),
        ]);
    }


    public function destroySession(Request $request, AiChatSession $session)
    {
        $authUser = session('user_id') ? User::find(session('user_id')) : null;

        if (!$authUser) {
            return response()->json(['ok' => false, 'message' => 'Silakan login dulu.'], 401);
        }

        if ((int) $session->user_id !== (int) $authUser->id) {
            return response()->json(['ok' => false, 'message' => 'Session tidak valid.'], 403);
        }

        $session->messages()->delete();
        $session->delete();

        return response()->json([
            'ok' => true,
            'redirect' => route('ai-chat.page'),
        ]);
    }

    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['ok' => true, 'suggestions' => []]);
        }

        $base = collect([
            'aloe vera bayi', 'anak susah makan', 'aktivitas edukatif anak', 'anti kolik',
            'botol susu anti kolik', 'baju bayi muslim', 'boneka edukatif sensorik', 'buku cerita anak bergambar',
            'cek perlengkapan bayi baru lahir', 'cara pilih popok newborn', 'edukasi anak 3 tahun',
            'lotion bayi', 'mainan edukatif', 'mainan sensorik', 'minyak telon', 'mpasi bayi',
            'pakaian bayi', 'perawatan bayi', 'popok newborn', 'sabun bayi', 'shampoo bayi', 'stroller ringan'
        ]);

        $productNames = Product::query()->pluck('name');
        $postTitles = Post::query()->where('status', 'published')->pluck('title');

        $items = $base->merge($productNames)->merge($postTitles)
            ->filter(fn($item) => Str::contains(Str::lower($item), Str::lower($q)))
            ->unique()
            ->take(7)
            ->values();

        return response()->json(['ok' => true, 'suggestions' => $items]);
    }

    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|min:1|max:1000',
            'session_id' => 'nullable|integer',
        ]);

        $message = trim($data['message']);
        $authUser = session('user_id') ? User::find(session('user_id')) : null;

        if (!$authUser) {
            return response()->json([
                'ok' => false,
                'message' => 'Silakan login dulu untuk memakai AI Mom & Baby SobatAnak.',
                'redirect' => route('login'),
            ], 401);
        }

        $limitCheck = $this->checkUserAiLimit($authUser->id);
        if (!$limitCheck['allowed']) {
            return response()->json([
                'ok' => false,
                'message' => 'Limit pertanyaan AI kamu hari ini sudah habis. Coba lagi besok ya, atau hubungi admin untuk menambah kuota.',
                'limit_reached' => true,
                'limit' => $limitCheck,
            ], 429);
        }

        $session = null;
        if (!empty($data['session_id'])) {
            $session = AiChatSession::find($data['session_id']);
            if ($session && (int) $session->user_id !== (int) $authUser->id) {
                return response()->json(['ok' => false, 'message' => 'Session tidak valid.'], 403);
            }
        }

        if (!$session) {
            $session = AiChatSession::create([
                'user_id' => $authUser->id,
                'title' => Str::limit($message, 80, ''),
                'source' => 'gemini_chat_session',
            ]);
        }

        $shouldRenameSession = $session->messages()->count() === 0 || $session->title === 'Percakapan baru';

        AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'message' => $message,
        ]);

        [$intent, $intentLabel] = $this->detectIntent($message);
        $isGreetingOnly = $this->isGreetingOnly($message);
        $products = (!$isGreetingOnly && $this->shouldRecommendProducts($message, $intent))
            ? $this->searchProducts($message, $intent)
            : collect();
        $articles = (!$isGreetingOnly && $this->shouldRecommendArticles($message, $intent))
            ? $this->searchArticles($message, $intent)
            : collect();
        $history = $session->messages()->oldest()->take(16)->get();

        $userName = $authUser?->name ?: 'SobatAnak';

        $cached = $this->getCachedAnswer($message, $intent, $products, $articles);
        if ($cached) {
            [$answer, $provider, $providerNotice] = [$cached['answer'], 'cache', null];
        } else {
            [$answer, $provider, $providerNotice] = $this->askSmartProvider($message, $intentLabel, $products, $articles, $history, $userName, $authUser->id);
            if (!$answer) {
                $answer = $this->buildLocalFallbackAnswer($message, $intentLabel, $products, $articles, $userName);
                $provider = 'local_fallback';
            }

            if ($providerNotice) {
                $answer = $providerNotice . "\n\n" . $answer;
            }

            $this->storeCachedAnswer($message, $intent, $answer, $provider, $products, $articles);
        }

        $this->incrementUserAiLimit($authUser->id);

        $quickChoices = ($provider !== 'local_fallback')
            ? $this->buildQuickChoices($message, $answer, $intent, $products, $articles)
            : [];

        $assistantMessage = AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'message' => $answer,
            'recommendations' => [
                'intent' => $intent,
                'products' => $products->pluck('id')->all(),
                'articles' => $articles->pluck('id')->all(),
                'quick_choices' => $quickChoices,
                'provider' => $provider ?? (config('services.gemini.api_key') ? 'gemini' : 'local_fallback'),
                'provider_notice' => $providerNotice,
            ],
        ]);

        if ($shouldRenameSession) {
            $session->update(['title' => Str::limit($message, 80, '')]);
        }
        $session->touch();

        return response()->json([
            'ok' => true,
            'session_id' => $session->id,
            'session_url' => route('ai-chat.session', $session->id),
            'session_title' => $session->title,
            'answer' => $answer,
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category,
                'price' => 'Rp ' . number_format((int) $p->price, 0, ',', '.'),
                'rating' => (string) $p->rating,
                'sold' => (int) ($p->sold ?? 0),
                'image' => $p->image,
                'url' => route('product.show', $p->id),
                'stock_status' => ((int)($p->stock ?? 0) <= 0) ? 'Stok habis' : (((int)($p->stock ?? 0) <= 3) ? 'Stok tinggal sedikit' : 'Stok tersedia'),
            ])->values(),
            'articles' => $articles->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category_name,
                'excerpt' => Str::limit(strip_tags((string) $a->content), 110),
                'url' => route('article.show', $a->slug ?: $a->id),
            ])->values(),
            'quick_choices' => $quickChoices,
            'message_id' => $assistantMessage->id,
        ]);
    }

    private function askSmartProvider(string $message, string $intentLabel, $products, $articles, $history, string $userName, ?int $userId = null): array
    {
        $providerOrder = collect(explode(',', (string) env('AI_PROVIDER_ORDER', 'groq,gemini,openrouter')))
            ->map(fn ($provider) => trim(Str::lower($provider)))
            ->filter()
            ->unique()
            ->values();

        if ($providerOrder->isEmpty()) {
            $providerOrder = collect(['groq']);
        }

        $runners = [
            'groq' => fn () => $this->askGroq($message, $intentLabel, $products, $articles, $history, $userName),
            'gemini' => fn () => $this->askGemini($message, $intentLabel, $products, $articles, $history, $userName),
            'openrouter' => fn () => $this->askOpenRouter($message, $intentLabel, $products, $articles, $history, $userName),
        ];

        foreach ($providerOrder as $providerName) {
            if (!isset($runners[$providerName])) {
                continue;
            }

            if ($providerName === 'groq' && !(config('services.groq.api_key') ?: env('GROQ_API_KEY'))) {
                continue;
            }
            if ($providerName === 'gemini' && !(config('services.gemini.api_key') ?: env('GEMINI_API_KEY'))) {
                continue;
            }
            if ($providerName === 'openrouter' && !(config('services.openrouter.api_key') ?: env('OPENROUTER_API_KEY'))) {
                continue;
            }

            $startedAt = microtime(true);
            try {
                $answer = $runners[$providerName]();
                $duration = (int) round((microtime(true) - $startedAt) * 1000);

                if (is_string($answer) && trim($answer) !== '') {
                    $this->logAiProvider($userId, $providerName, 'success', $duration, $message, null);
                    return [trim($answer), $providerName, null];
                }

                $this->logAiProvider($userId, $providerName, 'failed_or_empty', $duration, $message, 'Provider returned empty answer.');
            } catch (\Throwable $e) {
                $duration = (int) round((microtime(true) - $startedAt) * 1000);
                $this->logAiProvider($userId, $providerName, 'exception', $duration, $message, $e->getMessage());
                Log::warning('AI provider exception', [
                    'provider' => $providerName,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return [null, 'local_fallback', 'Catatan: Provider AI sedang tidak tersedia/limit, jadi aku pakai jawaban cadangan lokal dulu.'];
    }

    private function askGroq(string $message, string $intentLabel, $products, $articles, $history, string $userName): ?string
    {
        $apiKey = config('services.groq.api_key') ?: env('GROQ_API_KEY');
        $model = config('services.groq.model') ?: env('GROQ_MODEL', 'llama-3.3-70b-versatile');
        if (!$apiKey) {
            return null;
        }

        $productContext = $products->isNotEmpty()
            ? $products->map(fn($p) =>
                "• {$p->name} (Kategori: {$p->category} | Harga: Rp " . number_format((int)$p->price,0,',','.') .
                " | Rating: {$p->rating}/5 | Terjual: " . number_format((int)($p->sold ?? 0),0,',','.') .
                " | Stok: " . (((int)($p->stock ?? 0) <= 0) ? 'HABIS' : (((int)($p->stock ?? 0) <= 3) ? 'Tinggal sedikit' : 'Tersedia')) . ")"
              )->implode("\n")
            : '';

        $articleContext = $articles->isNotEmpty()
            ? $articles->map(fn($a) =>
                "• [{$a->category_name}] {$a->title} — " . Str::limit(strip_tags((string)$a->content), 180)
              )->implode("\n")
            : '';

        // Build proper chat messages array for better memory
        $messages = [];

        $websiteKnowledge = <<<KNOWLEDGE
=== PENGETAHUAN LENGKAP TENTANG SOBATANAK ===

**Tentang SobatAnak:**
SobatAnak adalah platform e-commerce Mom & Baby Care Indonesia. Menjual produk berkualitas untuk ibu hamil, bayi (0-12 bulan), dan anak-anak (1-10 tahun). Dipercaya oleh 50.000+ keluarga Indonesia.

**Kategori Produk yang Dijual:**
1. Perawatan Bayi: lotion, sabun, shampoo, minyak telon, aloe vera, bedak bayi, produk kulit sensitif
2. Nutrisi & MPASI: sereal bayi, biskuit, makanan pendamping ASI, vitamin, susu formula
3. Pakaian: baju bayi, baju muslim anak, perlengkapan newborn, muslin, popok kain
4. Perlengkapan Bayi: botol susu anti kolik, dot, sterilizer, breast pump, tas bayi
5. Mainan Edukatif: mainan sensorik, puzzle, buku cerita, boneka, mainan stimulasi motorik
6. Keselamatan & Kenyamanan: stroller lipat ringan, car seat, baby monitor, bouncer
7. Popok: popok newborn, popok bayi, pull-up pants, popok kain modern

**Fitur Website SobatAnak:**
- 🛍️ Toko produk dengan filter kategori, sort harga/rating/terlaris
- 🛒 Keranjang belanja (cart) per akun — tidak perlu input ulang data
- 💳 Checkout & pembayaran via Midtrans (transfer bank, e-wallet, kartu kredit, QRIS)
- 🚚 Pengiriman dengan RajaOngkir (JNE, SiCepat, JNT, TIKI, POS Indonesia) — cek ongkir otomatis
- 📝 Ulasan produk dari pembeli terverifikasi dengan rating bintang
- 🤖 AI Chat (ini!) — tanya apapun soal parenting, produk, MPASI, kesehatan anak
- 🎮 Mini Game: Tap Tap Kuman — mainkan untuk kumpulkan poin
- 🎁 Reward Store: tukar poin dengan voucher belanja, diskon, atau hadiah
- 📖 Artikel parenting: tips MPASI, kesehatan anak, perkembangan bayi, parenting
- 👤 Profile & riwayat pesanan
- 📍 Simpan banyak alamat pengiriman

**Cara Berbelanja di SobatAnak:**
1. Browse produk atau pakai AI Search di halaman utama
2. Klik produk → lihat detail → tambah ke keranjang
3. Buka keranjang → pilih alamat → pilih jasa kirim → lihat ongkir
4. Checkout → bayar via Midtrans → konfirmasi otomatis
5. Produk dikirim sesuai jasa kirim yang dipilih

**Cara Main Game & Reward:**
1. Masuk ke halaman Mini Game
2. Main Tap Tap Kuman — kumpulkan poin sebanyak mungkin
3. Poin tersimpan otomatis di akun
4. Buka Reward Store → tukar poin dengan hadiah menarik

**Topik Parenting yang Sering Ditanya (AI bisa bantu semua ini):**
- MPASI: jadwal, menu, tekstur, GTM (Gerakan Tutup Mulut), alergi makanan
- Kesehatan bayi: demam, batuk pilek, diare, kolik, ruam popok, alergi, imunisasi, vaksin
- Tumbuh kembang: milestone usia 0-1-2-3 tahun, stimulasi motorik kasar/halus
- Perawatan kulit bayi: produk aman, bahan berbahaya yang harus dihindari
- Parenting: tantrum, screen time, toilet training, sleep training, bonding
- ASI & menyusui: cara meningkatkan ASI, pompa ASI, MPASI pendamping
- Kehamilan: persiapan perlengkapan bayi, checklist newborn
- Keuangan keluarga: budget anak, biaya pendidikan, perencanaan keluarga

**Hal Penting:**
- Semua harga dalam Rupiah (IDR)
- Pengiriman ke seluruh Indonesia
- Produk original, aman untuk bayi (tidak mengandung bahan berbahaya)
- Customer service tersedia via AI Chat ini
- Untuk masalah pesanan/pembayaran, user bisa cek di halaman Profile > Pesanan

=== END KNOWLEDGE ===
KNOWLEDGE;

        $systemPrompt = <<<SYSTEM
Kamu adalah **Sobat AI**, asisten cerdas SobatAnak — platform Mom & Baby Care Indonesia terpercaya.

{$websiteKnowledge}

**IDENTITASMU:**
- Nama: Sobat AI (asisten resmi SobatAnak)
- Kamu TAHU semua tentang website SobatAnak di atas
- Kamu PAKAR parenting, kesehatan bayi & anak, MPASI, tumbuh kembang
- Kamu berbicara bahasa Indonesia yang hangat, natural, seperti teman yang berpengalaman
- Nama user yang sedang chat: {$userName}

**CARA MENJAWAB:**
1. Langsung jawab pertanyaan — jangan basa-basi panjang di awal
2. Untuk pertanyaan kompleks: berikan jawaban terstruktur dengan poin-poin jelas
3. Untuk sapaan/pertanyaan ringan: jawab singkat, hangat, natural
4. Sebutkan nama user sesekali agar terasa personal (tidak setiap kalimat)
5. Kalau ada info sebelumnya di riwayat chat, INGAT dan gunakan — jangan minta user ulang info yang sudah diberikan
6. Variasikan gaya kalimat — hindari pembuka yang sama berulang
7. Akhiri dengan 1 pertanyaan lanjutan OPSIONAL jika relevan (bukan wajib)

**TENTANG REKOMENDASI PRODUK/ARTIKEL:**
- Rekomendasikan produk HANYA jika user bertanya soal belanja, produk spesifik, atau minta rekomendasi
- Rekomendasikan artikel HANYA jika topiknya match dan artikel tersedia di konteks
- JANGAN karang produk/harga/artikel di luar yang diberikan dalam konteks

**KESEHATAN ANAK:**
- Berikan edukasi umum yang aman dan akurat
- Jelaskan tanda bahaya yang perlu segera ke dokter
- JANGAN diagnosa pasti atau rekomendasikan dosis obat spesifik
- Selalu sarankan konsultasi dokter untuk kondisi serius

**FORMAT JAWABAN:**
- Untuk list/langkah: gunakan bullet (•) atau nomor
- Untuk penekanan: gunakan **bold**
- Maksimal 4-5 paragraf atau 8-10 poin untuk jawaban kompleks
- Jawaban singkat untuk pertanyaan ringan (1-3 kalimat)
SYSTEM;

        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // Inject full chat history as proper messages (better memory)
        foreach ($history as $msg) {
            $role = $msg->role === 'user' ? 'user' : 'assistant';
            $content = Str::limit($msg->message, 800);
            $messages[] = ['role' => $role, 'content' => $content];
        }

        // Build the current user prompt with context
        $userPromptParts = ["**Pertanyaan:** {$message}"];
        $userPromptParts[] = "**Topik terdeteksi:** {$intentLabel}";

        if ($productContext) {
            $userPromptParts[] = "\n**Produk SobatAnak yang relevan (gunakan jika pertanyaan soal belanja/produk):**\n{$productContext}";
        }
        if ($articleContext) {
            $userPromptParts[] = "\n**Artikel SobatAnak yang relevan (gunakan jika match topik):**\n{$articleContext}";
        }

        $userPromptParts[] = "\nJawab secara langsung, spesifik, dan natural. Jangan ulangi info yang sudah dibahas sebelumnya di riwayat.";

        $messages[] = ['role' => 'user', 'content' => implode("\n", $userPromptParts)];

        try {
            $response = Http::timeout(20)
                ->retry(1, 200)
                ->withoutVerifying()
                ->withToken($apiKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.70,
                    'max_tokens' => (int) env('AI_MAX_TOKENS', 1200),
                    'top_p' => 0.9,
                ]);

            if (!$response->successful()) {
                Log::warning('Groq API failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $json = $response->json();
            $content = data_get($json, 'choices.0.message.content')
                ?: data_get($json, 'choices.0.text')
                ?: data_get($json, 'output_text');

            if (is_array($content)) {
                $content = collect($content)->filter()->implode("\n");
            }

            $content = trim((string) $content);
            if ($content === '') {
                Log::warning('Groq API empty content', ['body' => $response->body()]);
                return null;
            }

            return $content;
        } catch (\Throwable $e) {
            Log::warning('Groq API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function askOpenRouter(string $message, string $intentLabel, $products, $articles, $history, string $userName): ?string
    {
        $apiKey = config('services.openrouter.api_key') ?: env('OPENROUTER_API_KEY');
        $model = config('services.openrouter.model') ?: env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-70b-instruct');
        if (!$apiKey) {
            return null;
        }

        $productContext = $products->map(fn($p) => "- {$p->name} | kategori {$p->category} | harga Rp " . number_format((int)$p->price,0,',','.') . " | rating {$p->rating} | stok " . (((int)($p->stock ?? 0) <= 0) ? 'habis' : 'tersedia'))->implode("\n");
        $articleContext = $articles->map(fn($a) => "- {$a->title} | kategori {$a->category_name} | ringkasan " . Str::limit(strip_tags((string)$a->content), 160))->implode("\n");
        $historyText = $history->map(fn($m) => strtoupper($m->role) . ': ' . Str::limit($m->message, 700))->implode("\n");

        $systemPrompt = "Kamu AI SobatAnak, asisten Mom & Baby Care. Jawab dalam bahasa Indonesia yang natural, detail, dan praktis. Jawab pertanyaan user dulu secara langsung. Jangan memaksa produk/artikel kalau tidak relevan. Untuk kesehatan anak, edukasi umum aman, bukan diagnosis/dosis obat. Nama user: {$userName}.";
        $userPrompt = "Pertanyaan user: {$message}\nIntent: {$intentLabel}\nRiwayat:\n{$historyText}\nProduk SobatAnak relevan jika perlu:\n{$productContext}\nArtikel relevan jika perlu:\n{$articleContext}\n\nBuat jawaban kompleks, spesifik, tidak template, dan beri langkah praktis.";

        try {
            $response = Http::timeout(25)
                ->retry(1, 300)
                ->withoutVerifying()
                ->withToken($apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url', 'http://127.0.0.1:8000'),
                    'X-Title' => 'SobatAnak AI Chat',
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.72,
                    'max_tokens' => 1600,
                ]);

            if (!$response->successful()) {
                Log::warning('OpenRouter API failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return data_get($response->json(), 'choices.0.message.content');
        } catch (\Throwable $e) {
            Log::warning('OpenRouter API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function askGemini(string $message, string $intentLabel, $products, $articles, $history, string $userName): ?string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        if (!$apiKey) {
            return null;
        }

        $productContext = $products->map(fn($p) => "- {$p->name} | kategori {$p->category} | harga Rp " . number_format((int)$p->price,0,',','.') . " | rating {$p->rating} | stok " . (((int)($p->stock ?? 0) <= 0) ? 'habis' : 'tersedia'))->implode("\n");
        $articleContext = $articles->map(fn($a) => "- {$a->title} | kategori {$a->category_name} | ringkasan " . Str::limit(strip_tags((string)$a->content), 130))->implode("\n");
        $historyText = $history->map(fn($m) => strtoupper($m->role) . ': ' . Str::limit($m->message, 500))->implode("\n");

        $prompt = <<<PROMPT
Kamu adalah AI SobatAnak, asisten Mom & Baby Care berbahasa Indonesia yang ramah, empatik, dan sangat membantu.
Nama user: {$userName}.
Pertanyaan user saat ini: {$message}
Intent terdeteksi sistem: {$intentLabel}

Riwayat chat singkat:
{$historyText}

Produk SobatAnak yang boleh direkomendasikan jika relevan:
{$productContext}

Artikel SobatAnak yang boleh direkomendasikan jika relevan:
{$articleContext}

ATURAN UTAMA:
1. Jawab pertanyaan user secara langsung. Jangan mengalihkan ke produk/artikel kalau pertanyaan user adalah pertanyaan umum.
2. Kalau user menyapa, balas sapaan dengan hangat, singkat, dan sebut nama user.
3. Kalau user bertanya topik umum seperti keluarga, jumlah anak ideal, budget hidup, pendidikan, rutinitas rumah, atau parenting, jawab dengan analisis lengkap dan praktis.
4. Untuk pertanyaan yang butuh penjelasan kompleks, gunakan struktur rapi: jawaban inti, pertimbangan utama, langkah praktis, contoh sederhana, dan catatan penting.
5. Untuk kesehatan anak, berikan edukasi umum yang aman. Jangan mendiagnosis pasti, jangan memberi dosis obat, dan arahkan ke dokter bila ada tanda bahaya.
6. Produk SobatAnak hanya disebut bila pertanyaan berkaitan dengan belanja, memilih produk, kebutuhan bayi/anak, popok, botol, mainan, perawatan, MPASI, pakaian, stroller, atau perlengkapan.
7. Artikel hanya disebut bila artikel konteks benar-benar relevan.
8. Jangan mengarang produk, harga, stok, promo, atau artikel di luar konteks yang diberikan.
9. Pakai bahasa Indonesia natural, tidak kaku, seperti teman diskusi yang paham parenting.
10. Jangan terlalu sering bertanya balik. Kalau informasi kurang, tetap beri jawaban umum terbaik lalu beri pertanyaan lanjutan opsional di akhir.
11. Hindari pembuka berulang seperti “Aku paham pertanyaan kamu...” terlalu sering. Variasikan gaya bahasa.

CONTOH GAYA:
- Jika user tanya “kira-kira anak berapa yang ideal dan tidak menghabiskan budget”, jawab soal kesiapan finansial, waktu, kesehatan, jarak usia, dan pilihan 1-2 anak sebagai opsi realistis, bukan malah minta konteks.
- Jika user tanya “halo apa kabar”, jawab sapaan saja.
- Jika user tanya “cara pilih popok newborn”, beri panduan memilih dan boleh rekomendasikan produk popok dari konteks.
PROMPT;

        try {
            $response = Http::timeout(25)
                ->retry(1, 400)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.78,
                        'maxOutputTokens' => 1200,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Gemini API failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return data_get($response->json(), 'candidates.0.content.parts.0.text');
        } catch (\Throwable $e) {
            Log::warning('Gemini API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function detectIntent(string $message): array
    {
        $q = Str::lower($message);
        $map = [
            'kesehatan' => ['dokter','tanda bahaya','demam','batuk','pilek','diare','muntah','ruam','alergi','sesak','kejang','dehidrasi','sakit','kesehatan','imunisasi','vaksin'],
            'keuangan' => ['budget','biaya','keuangan','hemat','jumlah anak','anak ideal','1 anak','2 anak','keluarga ideal'],
            'parenting' => ['tantrum','rewel','tidur','disiplin','emosi','screen time','gadget','susah diatur','parenting'],
            'perawatan' => ['lotion','sabun','shampoo','sampo','minyak telon','aloe','kulit','mandi','perawatan'],
            'edukasi' => ['mainan','edukatif','sensorik','puzzle','buku','belajar','aktivitas'],
            'newborn' => ['newborn','bayi baru','popok','botol','anti kolik','susu','0-12','0–12'],
            'pakaian' => ['baju','pakaian','muslin','sepatu'],
            'nutrisi' => ['mpasi','makan','sereal','nutrisi','gtm'],
            'stroller' => ['stroller','jalan','bepergian','lipat'],
        ];
        foreach ($map as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($q, $keyword)) {
                    return [$intent, ucfirst($intent)];
                }
            }
        }
        return ['umum', 'Kebutuhan Mom & Baby'];
    }


    private function isGreetingOnly(string $message): bool
    {
        $q = trim(Str::lower($message));
        $q = preg_replace('/[^a-zA-Z0-9\s]/u', ' ', $q);
        $q = trim(preg_replace('/\s+/', ' ', $q));

        $greetings = ['hai', 'halo', 'hallo', 'hello', 'hi', 'hey', 'pagi', 'siang', 'sore', 'malam', 'apa kabar', 'gimana kabar', 'kabarnya gimana'];

        foreach ($greetings as $greeting) {
            if ($q === $greeting || Str::startsWith($q, $greeting . ' ')) {
                return str_word_count($q) <= 5;
            }
        }

        return false;
    }

    private function shouldRecommendArticles(string $message, string $intent): bool
    {
        if ($this->isGreetingOnly($message)) {
            return false;
        }

        // Always try articles for content-rich intents
        if (in_array($intent, ['perawatan','edukasi','newborn','pakaian','nutrisi','stroller','kesehatan','parenting','keuangan'], true)) {
            return true;
        }

        $q = Str::lower($message);
        $articleKeywords = [
            'artikel','bacaan','panduan','tips','cara','kenapa','mengapa','bagaimana','solusi','info',
            'kesehatan','demam','batuk','pilek','diare','muntah','ruam','alergi','sesak','imunisasi','vaksin',
            'mpasi','makan','gtm','nutrisi','parenting','tidur','tantrum','emosi','disiplin','screen time',
            'perkembangan','tumbuh kembang','perawatan','newborn','bayi','anak','keluarga','budget','biaya',
            'apa itu','apa yang','kapan','berapa lama','berapa usia','stimulasi','motorik','kognitif',
            'menyusui','asi','hamil','kehamilan','melahirkan','newborn care','sleep training','toilet training',
            'pertumbuhan','berat badan','tinggi badan','gizi','stunting','obesitas',
        ];

        foreach ($articleKeywords as $keyword) {
            if (Str::contains($q, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function shouldRecommendProducts(string $message, string $intent): bool
    {
        if ($this->isGreetingOnly($message)) {
            return false;
        }

        $q = Str::lower($message);

        // Always show products for product-related intents
        if (in_array($intent, ['perawatan','edukasi','newborn','pakaian','nutrisi','stroller','kesehatan'], true)) {
            return true;
        }

        $shoppingKeywords = [
            'produk','barang','beli','belanja','rekomendasi','rekomendasikan','pilih','cari','butuh',
            'botol','popok','mainan','lotion','sabun','shampoo','sampo','baju','pakaian','kaos',
            'stroller','mpasi','sereal','perlengkapan','perawatan','anti kolik','newborn','susu',
            'minyak telon','bedak','vitamin','nutrisi','selimut','gendongan','tempat tidur','kasur',
            'sterilizer','breast pump','pompa asi','dot','empeng','thermometer','baby monitor',
            'diaper','pampers','wipes','tisu','cotton bud','baby wash','baby oil',
            'mainan edukatif','puzzle','buku anak','boneka','flash card',
            'apa yang bagus','apa yang cocok','yang aman','yang murah','yang terbaik',
            'harga berapa','berapa harga','murah','terjangkau','budget','hemat',
        ];

        foreach ($shoppingKeywords as $keyword) {
            if (Str::contains($q, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function searchProducts(string $message, string $intent)
    {
        try {
            $q = Str::lower($message);
            $query = Product::query();

            $query->where(function ($sub) use ($q, $intent) {
                foreach (explode(' ', $q) as $word) {
                    $word = trim($word);
                    if (strlen($word) >= 3) {
                        $sub->orWhere('name', 'like', "%{$word}%")
                            ->orWhere('category', 'like', "%{$word}%")
                            ->orWhere('badge', 'like', "%{$word}%");
                    }
                }

                $intentCategory = match ($intent) {
                    'perawatan' => 'Perawatan',
                    'edukasi'   => 'Anak',
                    'newborn'   => 'Bayi',
                    'pakaian'   => 'Pakaian',
                    'nutrisi'   => 'Nutrisi',
                    'kesehatan' => 'Bayi',
                    'stroller'  => 'Stroller',
                    default     => null,
                };
                if ($intentCategory) {
                    $sub->orWhere('category', 'like', "%{$intentCategory}%");
                }
            });

            return $query->orderByDesc('rating')->orderByDesc('sold')->take(6)->get();
        } catch (\Throwable $e) {
            Log::warning('searchProducts error', ['message' => $e->getMessage()]);
            return collect();
        }
    }

    private function searchArticles(string $message, string $intent)
    {
        try {
        $q = Str::lower($message);
        $keywords = $this->extractArticleKeywords($message, $intent);

        if (empty($keywords) && $intent === 'umum') {
            return collect();
        }

        $articles = Post::with('category')
            ->where('status', 'published')
            ->latest('published_at')
            ->take(80)
            ->get();

        $scored = $articles->map(function ($article) use ($keywords, $intent, $q) {
            $haystack = Str::lower(
                trim(($article->title ?? '') . ' ' . ($article->content ?? '') . ' ' . ($article->tags ?? '') . ' ' . ($article->category_name ?? ''))
            );

            $score = 0;
            foreach ($keywords as $keyword => $weight) {
                if ($keyword === '') {
                    continue;
                }
                if (Str::contains($haystack, $keyword)) {
                    $score += $weight;
                }
                if (Str::contains(Str::lower($article->title ?? ''), $keyword)) {
                    $score += $weight * 1.4;
                }
                if (Str::contains(Str::lower($article->tags ?? ''), $keyword)) {
                    $score += $weight * 1.2;
                }
            }

            $category = Str::lower($article->category_name ?? '');
            $categoryBoost = match ($intent) {
                'newborn' => Str::contains($category, ['mom tips','baby care','parenting']) ? 5 : 0,
                'perawatan' => Str::contains($haystack, ['perawatan','kulit','ruam','lotion','mandi','botol']) ? 6 : 0,
                'kesehatan' => Str::contains($haystack, ['kesehatan','dokter','demam','batuk','pilek','diare','muntah','ruam','sesak','dehidrasi','imunisasi','vaksin']) ? 7 : 0,
                'parenting' => Str::contains($haystack, ['parenting','tantrum','tidur','emosi','rutinitas','disiplin','screen time']) ? 6 : 0,
                'keuangan' => Str::contains($haystack, ['budget','biaya','hemat','belanja','keluarga']) ? 6 : 0,
                'edukasi' => Str::contains($haystack, ['edukasi','mainan','sensorik','buku','aktivitas','tumbuh kembang']) ? 6 : 0,
                'nutrisi' => Str::contains($haystack, ['mpasi','nutrisi','makan','sereal']) ? 6 : 0,
                'pakaian' => Str::contains($haystack, ['pakaian','baju','muslin']) ? 5 : 0,
                default => 0,
            };

            $score += $categoryBoost;

            // Hindari artikel yang tidak nyambung: skor harus cukup untuk masuk rekomendasi.
            $article->ai_relevance_score = $score;
            return $article;
        })
        ->filter(fn ($article) => (float) $article->ai_relevance_score >= 7)
        ->sortByDesc(fn ($article) => (float) $article->ai_relevance_score)
        ->take(3)
        ->values();

        return $scored;
        } catch (\Throwable $e) {
            Log::warning('searchArticles error', ['message' => $e->getMessage()]);
            return collect();
        }
    }

    private function extractArticleKeywords(string $message, string $intent): array
    {
        $q = Str::lower($message);
        $q = preg_replace('/[^a-z0-9\s]/u', ' ', $q);
        $words = collect(explode(' ', (string) $q))
            ->map(fn ($word) => trim($word))
            ->filter(fn ($word) => strlen($word) >= 3)
            ->reject(fn ($word) => in_array($word, [
                'yang','dan','atau','untuk','dengan','dari','jadi','kalau','kalo','bisa','coba','tolong','kira','berapa',
                'saya','aku','kamu','anak','bayi','ideal','tentang','seputar','cara','gimana','bagaimana','apa','kenapa'
            ], true));

        $keywords = [];
        foreach ($words as $word) {
            $keywords[$word] = 2;
        }

        $topicMap = [
            'budget' => ['budget'=>5,'biaya'=>5,'hemat'=>5,'belanja'=>4,'kebutuhan'=>3,'keluarga'=>3,'prioritas'=>3],
            'keuangan' => ['budget'=>5,'biaya'=>5,'hemat'=>5,'belanja'=>4,'keluarga'=>3],
            'keluarga' => ['keluarga'=>4,'parenting'=>3,'tumbuh kembang'=>3,'rutinitas'=>3],
            'tidur' => ['tidur'=>6,'rutinitas'=>5,'parenting'=>4,'bayi'=>2],
            'tantrum' => ['tantrum'=>6,'emosi'=>5,'parenting'=>4,'disiplin'=>3],
            'demam' => ['demam'=>6,'kesehatan'=>5,'dokter'=>3,'anak'=>2],
            'batuk' => ['batuk'=>6,'pilek'=>5,'kesehatan'=>5,'dokter'=>3],
            'pilek' => ['pilek'=>6,'batuk'=>5,'kesehatan'=>5],
            'ruam' => ['ruam'=>6,'kulit'=>5,'perawatan'=>4,'bayi'=>2],
            'mpasi' => ['mpasi'=>7,'nutrisi'=>5,'makan'=>5,'bayi'=>2],
            'makan' => ['makan'=>6,'nutrisi'=>5,'mpasi'=>4,'gtm'=>4],
            'popok' => ['popok'=>6,'newborn'=>5,'bayi baru lahir'=>5,'mom tips'=>3],
            'botol' => ['botol'=>6,'susu'=>5,'anti kolik'=>5,'membersihkan'=>4],
            'mainan' => ['mainan'=>6,'edukatif'=>5,'sensorik'=>5,'aktivitas'=>4],
            'edukatif' => ['edukatif'=>6,'mainan'=>5,'sensorik'=>5,'tumbuh kembang'=>4],
            'perawatan' => ['perawatan'=>6,'kulit'=>4,'lotion'=>4,'mandi'=>3,'ruam'=>3],
            'kesehatan' => ['kesehatan'=>7,'dokter'=>6,'demam'=>6,'batuk'=>5,'pilek'=>5,'diare'=>5,'muntah'=>5,'ruam'=>5,'tanda bahaya'=>5,'dehidrasi'=>5,'sesak'=>6],
            'parenting' => ['parenting'=>6,'tantrum'=>6,'tidur'=>5,'emosi'=>5,'rutinitas'=>4,'disiplin'=>4],
        ];

        foreach ($topicMap as $trigger => $related) {
            if (Str::contains($q, $trigger)) {
                foreach ($related as $keyword => $weight) {
                    $keywords[$keyword] = max($keywords[$keyword] ?? 0, $weight);
                }
            }
        }

        $intentMap = [
            'newborn' => ['newborn'=>5,'bayi baru lahir'=>5,'popok'=>4,'botol'=>4,'mom tips'=>3],
            'perawatan' => ['perawatan'=>5,'kulit'=>4,'ruam'=>4,'lotion'=>3],
            'edukasi' => ['edukasi'=>5,'mainan'=>4,'sensorik'=>4,'tumbuh kembang'=>4],
            'nutrisi' => ['mpasi'=>5,'nutrisi'=>5,'makan'=>4],
            'pakaian' => ['pakaian'=>5,'baju'=>4,'muslin'=>4],
        ];
        foreach ($intentMap[$intent] ?? [] as $keyword => $weight) {
            $keywords[$keyword] = max($keywords[$keyword] ?? 0, $weight);
        }

        return $keywords;
    }

    private function buildQuickChoices(string $message, string $answer, string $intent, $products, $articles): array
    {
        $q = Str::lower($message . ' ' . $answer);

        if ($this->isGreetingOnly($message) || Str::contains($q, ['apa kabar', 'halo', 'hai', 'hello'])) {
            return [
                'Tanya kebutuhan bayi baru lahir',
                'Minta rekomendasi produk sesuai usia',
                'Cari artikel parenting populer',
                'Buat checklist belanja bayi',
            ];
        }

        if (Str::contains($q, ['anak ideal', 'jumlah anak', 'berapa anak', 'budget', 'biaya hidup', 'keuangan keluarga', 'keluarga ideal'])) {
            return [
                'Buat simulasi budget anak per bulan',
                'Bandingkan ideal 1 anak dan 2 anak',
                'Jelaskan jarak usia anak yang aman',
                'Buat checklist kesiapan punya anak',
            ];
        }

        if (Str::contains($q, ['demam', 'batuk', 'pilek', 'diare', 'muntah', 'ruam', 'alergi', 'sesak', 'kesehatan', 'imunisasi', 'vaksin'])) {
            return [
                'Kapan anak harus dibawa ke dokter?',
                'Apa tanda bahaya yang perlu diawasi?',
                'Buat langkah perawatan aman di rumah',
                'Cari artikel kesehatan anak terkait',
            ];
        }

        if (Str::contains($q, ['mpasi', 'susah makan', 'gtm', 'nutrisi', 'berat badan', 'bb anak', 'makan'])) {
            return [
                'Buat jadwal MPASI sederhana',
                'Tips anak susah makan tanpa dipaksa',
                'Rekomendasi perlengkapan MPASI',
                'Cari artikel nutrisi anak',
            ];
        }

        if (Str::contains($q, ['tantrum', 'rewel', 'tidur', 'disiplin', 'emosi', 'parenting', 'screen time', 'gadget'])) {
            return [
                'Buat langkah menghadapi tantrum',
                'Tips rutinitas tidur anak',
                'Cara membatasi screen time',
                'Cari artikel parenting terkait',
            ];
        }

        if ($products->count() || $this->shouldRecommendProducts($message, $intent)) {
            return [
                'Bandingkan produk yang paling cocok',
                'Cari pilihan paling hemat',
                'Urutkan dari rating terbaik',
                'Tips memilih produk yang aman',
            ];
        }

        if ($articles->count() || $this->shouldRecommendArticles($message, $intent)) {
            return [
                'Ringkas artikel yang paling relevan',
                'Beri langkah praktis dari artikel',
                'Cari artikel lain yang mirip',
                'Jelaskan dengan bahasa sederhana',
            ];
        }

        return [
            'Jelaskan lebih detail',
            'Beri contoh praktis',
            'Buat checklist langkahnya',
            'Hubungkan dengan kebutuhan anak',
        ];
    }

    private function buildLocalFallbackAnswer(string $message, string $intentLabel, $products, $articles, string $userName): string
    {
        $lowerMessage = Str::lower($message);

        if ($this->isGreetingOnly($message) || Str::contains($lowerMessage, ['apa kabar', 'gimana kabar', 'kabarnya gimana'])) {
            return "Halo {$userName}, kabarku baik dan siap bantu kamu 😊\n\nMau bahas apa hari ini? Kamu bisa tanya soal parenting, kesehatan anak secara umum, MPASI, tumbuh kembang, budget keluarga, rutinitas anak, sampai rekomendasi produk SobatAnak. Aku akan jawab pertanyaan kamu dulu, lalu baru menampilkan produk atau artikel kalau memang nyambung.";
        }

        if (Str::contains($lowerMessage, ['bandingkan ideal 1 anak dan 2 anak', 'bandingkan 1 anak dan 2 anak', '1 anak dan 2 anak', 'satu anak dan dua anak'])) {
            return "Bisa, {$userName}. Kalau dibandingkan dari sisi kualitas hidup dan budget, pilihan 1 anak dan 2 anak sama-sama bisa ideal, tapi konsekuensinya berbeda.\n\n**1 anak** biasanya lebih ringan secara finansial. Biaya makan, kesehatan, pendidikan, pakaian, transportasi, dan tabungan masa depan lebih mudah dikontrol. Orang tua juga bisa memberi perhatian lebih fokus. Tantangannya, anak mungkin perlu lebih banyak kesempatan sosialisasi di luar rumah supaya tetap belajar berbagi dan berinteraksi.\n\n**2 anak** bisa memberi pengalaman tumbuh bersama saudara, belajar berbagi, dan punya teman di rumah. Tapi biaya hampir pasti naik: kebutuhan harian, sekolah, kesehatan, liburan, sampai ruang tinggal. Orang tua juga perlu membagi waktu dan energi lebih seimbang.\n\nKalau prioritas utama kamu adalah **tidak menghabiskan budget**, saran paling realistis adalah mulai dari 1 anak dulu, lalu evaluasi setelah kondisi keuangan, kesehatan orang tua, waktu, dan support system stabil. Anak kedua lebih aman dipertimbangkan kalau dana darurat, cicilan, tabungan pendidikan, dan kebutuhan harian anak pertama sudah terkendali.\n\nPatokan sederhananya: jumlah anak ideal adalah jumlah yang masih membuat orang tua mampu hadir secara emosional, tidak tertekan secara finansial, dan anak tetap mendapat kesehatan, pendidikan, perhatian, serta lingkungan tumbuh yang baik.";
        }

        if (Str::contains($lowerMessage, ['simulasi budget anak', 'budget anak per bulan', 'biaya anak per bulan', 'simulasi biaya anak'])) {
            return "Bisa, {$userName}. Ini simulasi kasar budget anak per bulan yang bisa kamu jadikan patokan awal. Angkanya bisa beda tergantung kota, gaya hidup, dan usia anak.\n\n**Kebutuhan dasar:** makan/MPASI atau susu, popok bila masih bayi, perlengkapan mandi, pakaian, dan kebutuhan kecil harian.\n**Kesehatan:** vitamin bila direkomendasikan dokter, kontrol kesehatan, imunisasi, dan dana jaga-jaga kalau sakit.\n**Pendidikan dan stimulasi:** buku, mainan edukatif, aktivitas belajar, daycare atau sekolah jika sudah masuk usia.\n**Dana darurat anak:** sebaiknya dipisah dari dana darurat keluarga, minimal mulai dari nominal kecil tapi rutin.\n\nCara amannya: hitung biaya rutin anak sekarang, tambah 20–30% untuk kebutuhan tidak terduga, lalu sisihkan tabungan pendidikan. Kalau setelah dihitung masih membuat pengeluaran keluarga terlalu mepet, berarti jumlah anak atau timing menambah anak perlu dipertimbangkan ulang.\n\nPrinsipnya bukan mencari angka paling murah, tapi mencari angka yang tetap membuat keluarga aman, anak terawat, dan orang tua tidak terus-menerus stres karena biaya.";
        }

        if (Str::contains($lowerMessage, ['jarak usia anak', 'jarak aman', 'jarak kehamilan', 'jarak lahir'])) {
            return "Secara umum, {$userName}, jarak usia anak yang sering dianggap lebih nyaman untuk banyak keluarga adalah sekitar **2–4 tahun**. Alasannya, anak pertama biasanya sudah mulai lebih mandiri, orang tua punya waktu pemulihan fisik dan mental, dan pengeluaran tidak menumpuk terlalu ekstrem dalam waktu dekat.\n\nJarak terlalu dekat bisa membuat orang tua lebih lelah, biaya popok/susu/perawatan bisa dobel, dan anak pertama mungkin masih sangat membutuhkan perhatian penuh. Tapi jarak terlalu jauh juga punya tantangan, misalnya ritme pengasuhan seperti mulai dari awal lagi.\n\nYang perlu dilihat: kondisi kesehatan ibu, kesiapan mental orang tua, kondisi finansial, dukungan keluarga, dan kebutuhan anak pertama. Untuk keputusan medis terkait kehamilan, paling aman tetap konsultasi dengan dokter kandungan karena kondisi tiap ibu berbeda.";
        }

        if (Str::contains($lowerMessage, ['checklist kesiapan punya anak', 'kesiapan punya anak', 'siap punya anak', 'checklist anak'])) {
            return "Ini checklist sederhana sebelum memutuskan punya anak atau menambah anak, {$userName}:\n\n1. **Finansial:** penghasilan cukup stabil, dana darurat ada, cicilan tidak terlalu menekan, dan sudah mulai menyiapkan biaya kesehatan serta pendidikan.\n2. **Waktu:** orang tua punya ruang untuk mendampingi anak, bukan hanya memenuhi kebutuhan materi.\n3. **Kesehatan:** kondisi fisik dan mental orang tua cukup siap, terutama ibu bila berkaitan dengan kehamilan.\n4. **Support system:** ada pasangan/keluarga/daycare/bantuan yang bisa mendukung saat situasi berat.\n5. **Pola asuh:** orang tua cukup sepakat soal nilai utama, batasan, disiplin, screen time, dan pembagian peran.\n6. **Lingkungan:** rumah cukup aman, rutinitas cukup stabil, dan anak punya ruang tumbuh yang sehat.\n\nKalau banyak poin masih belum siap, bukan berarti tidak boleh punya anak, tapi lebih baik dibereskan bertahap dulu supaya keputusan terasa lebih aman dan tidak terburu-buru.";
        }

        if (Str::contains($lowerMessage, ['anak berapa', 'berapa anak', 'jumlah anak', 'anak ideal', 'ideal punya anak', 'budget', 'biaya hidup', 'menghabiskan budget', 'keuangan keluarga', 'keluarga ideal'])) {
            return "Menurutku, {$userName}, jumlah anak yang ideal bukan angka yang sama untuk semua keluarga. Yang paling penting adalah apakah orang tua masih bisa menjaga **kualitas hidup, kesehatan mental, waktu pengasuhan, dan stabilitas finansial**.\n\nKalau fokus kamu adalah agar tidak menghabiskan budget kehidupan, pilihan paling realistis untuk banyak keluarga biasanya **1–2 anak**. Satu anak lebih mudah dikontrol dari sisi biaya dan perhatian. Dua anak masih memungkinkan jika pemasukan stabil, dana darurat aman, dan orang tua punya support system yang cukup.\n\nHal yang perlu dihitung: biaya makan/susu/MPASI, popok, kesehatan, pendidikan, transportasi, pakaian, tempat tinggal, hiburan, dan tabungan masa depan. Selain uang, hitung juga energi orang tua: apakah masih punya waktu mendampingi, bermain, mengatur emosi, dan menjaga hubungan keluarga tetap sehat.\n\nKesimpulan praktisnya: mulai dari kondisi keluarga kamu sekarang. Kalau dana darurat belum aman dan pengeluaran bulanan masih mepet, 1 anak dulu lebih bijak. Kalau keuangan stabil, kesehatan orang tua baik, dan waktu pengasuhan cukup, 2 anak bisa jadi pilihan realistis. Idealnya bukan yang terlihat sempurna, tapi yang bisa dijalani tanpa membuat orang tua dan anak sama-sama kelelahan.";
        }

        if (Str::contains($lowerMessage, ['kapan anak harus dibawa ke dokter', 'kapan harus dibawa ke dokter', 'dibawa ke dokter', 'perlu ke dokter'])) {
            return "Felix, anak sebaiknya dibawa ke dokter kalau gejalanya tidak hanya ringan, mulai mengganggu kondisi umum, atau ada tanda bahaya. Patokan praktisnya begini:\n\n1. **Kondisi anak tampak berat**: sangat lemas, sulit dibangunkan, tidak mau minum, menangis terus dengan tangisan yang tidak biasa, atau terlihat kesakitan.\n2. **Napas bermasalah**: napas cepat/berat, tarikan dinding dada, bibir kebiruan, mengi berat, atau anak tampak kesulitan bicara/menyusu karena sesak.\n3. **Demam berisiko**: bayi usia sangat kecil demam, demam tinggi, demam lebih dari 3 hari, atau demam disertai kejang, ruam luas, leher kaku, muntah terus, atau anak tampak sangat lemas.\n4. **Cairan tubuh berkurang**: pipis sangat sedikit, mulut kering, mata cekung, tidak ada air mata saat menangis, diare/muntah berulang, atau tanda dehidrasi.\n5. **Gejala tidak membaik**: batuk/pilek makin berat, diare tidak membaik, ruam menyebar, luka bernanah, atau nyeri yang terus meningkat.\n\nKalau ragu, lebih aman konsultasi ke dokter anak, apalagi untuk bayi kecil. Aku bisa bantu bantu buat checklist gejala yang perlu kamu catat sebelum ke dokter.";
        }

        if (Str::contains($lowerMessage, ['tanda bahaya', 'bahaya yang perlu diawasi', 'red flag', 'waspadai'])) {
            return "Tanda bahaya pada anak yang perlu diawasi biasanya berkaitan dengan napas, kesadaran, cairan tubuh, demam, dan perubahan perilaku. Yang perlu cepat diperiksakan antara lain:\n\n- Sesak napas, napas sangat cepat, tarikan dada, bibir/kuku kebiruan.\n- Anak sangat lemas, sulit dibangunkan, bingung, kejang, atau tidak responsif seperti biasa.\n- Tidak mau minum/menyusu, muntah terus, diare berat, pipis sangat sedikit, tanda dehidrasi.\n- Demam tinggi atau demam yang disertai ruam luas, leher kaku, sakit kepala berat, atau bayi kecil demam.\n- Nyeri hebat, perut kembung keras, BAB berdarah, muntah hijau/berdarah, atau ruam yang cepat menyebar.\n\nUntuk gejala ringan, kamu bisa pantau dan rawat di rumah. Tapi kalau salah satu tanda di atas muncul, jangan tunggu terlalu lama. Lebih aman hubungi dokter/IGD.";
        }

        if (Str::contains($lowerMessage, ['perawatan aman di rumah', 'langkah perawatan aman', 'rawat di rumah', 'perawatan di rumah'])) {
            return "Langkah perawatan aman di rumah tergantung keluhannya, tapi prinsip umumnya seperti ini, Felix:\n\n1. **Pantau kondisi umum**: cek anak masih aktif atau lemas, mau minum/makan, frekuensi pipis, suhu tubuh, dan napasnya.\n2. **Cukup cairan dan istirahat**: untuk banyak keluhan ringan, cairan dan istirahat sangat penting. Jangan paksa makan banyak, tapi usahakan minum tetap masuk.\n3. **Jaga kenyamanan**: pakaian nyaman, ruangan tidak terlalu panas/dingin, bersihkan hidung bila pilek, jaga area kulit tetap kering kalau ada ruam.\n4. **Hindari obat sembarangan**: jangan memberi antibiotik, obat batuk keras, atau dosis obat tanpa arahan tenaga kesehatan.\n5. **Catat perkembangan**: kapan mulai sakit, suhu tertinggi, frekuensi muntah/diare, obat yang sudah diberikan, dan gejala tambahan. Ini membantu dokter kalau perlu periksa.\n6. **Tetapkan batas waktu pantau**: kalau memburuk, muncul tanda bahaya, atau tidak membaik dalam waktu wajar, segera konsultasi.\n\nKalau kamu sebutkan gejalanya apa, usia anak, dan sudah berapa lama, aku bisa bantu buat langkah pantau yang lebih spesifik.";
        }

        if (Str::contains($lowerMessage, ['demam','batuk','pilek','diare','muntah','ruam','alergi','sesak','sakit','kesehatan','imunisasi','vaksin','dokter'])) {
            return "Aku bantu jelaskan secara umum ya, {$userName}. Untuk kesehatan anak, fokus pertama adalah kondisi umum anak: apakah masih aktif, mau minum, pipis normal, napas nyaman, dan gejalanya membaik atau justru memburuk.\n\nLangkah aman di rumah: cukup cairan, istirahat, pantau suhu/gejala, jaga kebersihan, dan hindari obat sembarangan tanpa aturan tenaga kesehatan. Catat juga kapan gejala mulai muncul, suhu tertinggi, frekuensi muntah/diare, dan perubahan perilaku anak.\n\nSegera ke dokter/IGD bila anak sesak napas, bibir kebiruan, sangat lemas, kejang, demam tinggi tidak membaik, muntah terus, diare berat, tanda dehidrasi, ruam menyebar cepat, atau bayi masih sangat kecil. Aku bisa bantu edukasi umum, tapi diagnosis tetap perlu dokter ya.";
        }

        if (Str::contains($lowerMessage, ['mpasi','makan','susah makan','gtm','nutrisi','berat badan','bb anak'])) {
            return "Untuk MPASI dan anak susah makan, kuncinya adalah melihat penyebabnya dulu. Anak bisa menolak makan karena bosan tekstur, sedang tumbuh gigi, terlalu banyak camilan/susu, jadwal makan berantakan, sedang sakit, atau suasana makan terlalu penuh tekanan.\n\nYang bisa dicoba: buat jadwal makan teratur, porsi kecil dulu, variasikan tekstur, kurangi distraksi layar, beri contoh makan bersama, dan jangan memaksa sampai anak trauma. Fokus pada konsistensi, bukan harus habis setiap kali makan.\n\nKalau berat badan tidak naik, anak sering muntah, tampak lemas, atau sulit makan berkepanjangan, sebaiknya konsultasi ke dokter anak atau ahli gizi.";
        }

        if (Str::contains($lowerMessage, ['tantrum','rewel','tidur','disiplin','emosi','parenting','screen time','gadget','susah diatur','marah'])) {
            return "Aku bantu ya, {$userName}. Dalam parenting, perilaku anak biasanya adalah cara mereka menyampaikan kebutuhan: lapar, lelah, bosan, ingin diperhatikan, takut, atau belum mampu mengatur emosi.\n\nLangkah praktis: validasi emosi anak, beri batasan yang jelas, gunakan kalimat pendek, tawarkan pilihan sederhana, dan konsisten dengan rutinitas. Saat tantrum, biasanya lebih efektif menunggu anak cukup tenang dulu, baru diajak bicara.\n\nKalau perilaku sangat intens, sering menyakiti diri/orang lain, atau mengganggu aktivitas harian, konsultasi dengan psikolog anak atau dokter anak bisa sangat membantu.";
        }

        if (Str::contains($lowerMessage, ['perawatan','mandi','lotion','sabun','kulit','minyak telon','shampoo','sampo','ruam popok'])) {
            $tail = $products->count() ? "\n\nKalau kamu sedang mencari produk, aku juga tampilkan rekomendasi perawatan SobatAnak yang relevan di bawah jawaban ini." : '';
            return "Untuk perawatan bayi, pilih produk yang lembut, sesuai usia, dan tidak membuat kulit kering. Kulit bayi lebih sensitif, jadi sebaiknya hindari produk dengan aroma terlalu kuat atau bahan yang terasa keras di kulit.\n\nRutinitas sederhana: mandi secukupnya, keringkan area lipatan, ganti popok secara rutin, gunakan pelembap bila kulit kering, dan pantau tanda iritasi. Kalau ruam meluas, bernanah, anak demam, atau bayi tampak sangat tidak nyaman, sebaiknya cek ke dokter.{$tail}";
        }

        if ($products->count()) {
            return "Aku bantu ya, {$userName}. Untuk kebutuhan {$intentLabel}, pilih yang sesuai usia anak, aman digunakan, bahannya nyaman, dan benar-benar mendukung rutinitas keluarga. Jangan hanya lihat harga; cek juga fungsi, kemudahan dibersihkan, daya tahan, dan apakah cocok untuk kondisi anak.\n\nAku tampilkan beberapa produk SobatAnak yang paling relevan di bawah jawaban ini supaya kamu bisa bandingkan pilihan dengan lebih mudah.";
        }

        if ($articles->count()) {
            return "Aku bantu jawab ya, {$userName}. Topik ini cocok dibahas dari sisi parenting dan kebutuhan anak. Secara umum, mulai dari memahami usia anak, kebutuhan hariannya, kebiasaan keluarga, lalu pilih solusi yang aman dan realistis untuk dilakukan konsisten.\n\nAku juga tampilkan artikel pendukung yang relevan di bawah ini supaya kamu bisa lanjut baca panduan lengkapnya.";
        }

        return "Aku bantu ya, {$userName}. Untuk pertanyaan “{$message}”, aku akan jawab dari sudut pandang keluarga dan anak secara umum. Keputusan yang baik biasanya perlu mempertimbangkan keamanan, kesehatan, emosi anak, kemampuan orang tua, budget, dan rutinitas keluarga.\n\nKalau kamu mau, beri sedikit detail tambahan seperti usia anak, kondisi keluarga, atau tujuan yang ingin dicapai. Nanti aku bisa bantu pecah menjadi langkah praktis, checklist, atau perbandingan pilihan yang lebih jelas.";
    }


    private function normalizeQuestion(string $message): string
    {
        $q = Str::lower(trim($message));
        $q = preg_replace('/[^a-z0-9\s]/u', ' ', $q);
        $q = preg_replace('/\s+/', ' ', $q);
        return trim((string) $q);
    }

    private function checkUserAiLimit(int $userId): array
    {
        $dailyLimit = (int) env('AI_DAILY_LIMIT', 30);
        $today = now()->toDateString();

        try {
            $row = DB::table('ai_user_limits')
                ->where('user_id', $userId)
                ->where('limit_date', $today)
                ->first();

            $used = (int) ($row->used_count ?? 0);
            return [
                'allowed' => $used < $dailyLimit,
                'used' => $used,
                'limit' => $dailyLimit,
                'remaining' => max(0, $dailyLimit - $used),
            ];
        } catch (\Throwable $e) {
            // Kalau tabel belum ada, chat tetap jalan agar website tidak error.
            return ['allowed' => true, 'used' => 0, 'limit' => $dailyLimit, 'remaining' => $dailyLimit];
        }
    }

    private function incrementUserAiLimit(int $userId): void
    {
        $today = now()->toDateString();
        try {
            $exists = DB::table('ai_user_limits')
                ->where('user_id', $userId)
                ->where('limit_date', $today)
                ->exists();

            if ($exists) {
                DB::table('ai_user_limits')
                    ->where('user_id', $userId)
                    ->where('limit_date', $today)
                    ->update([
                        'used_count' => DB::raw('used_count + 1'),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('ai_user_limits')->insert([
                    'user_id' => $userId,
                    'limit_date' => $today,
                    'used_count' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('AI limit increment failed', ['message' => $e->getMessage()]);
        }
    }

    private function getCachedAnswer(string $message, string $intent, $products, $articles): ?array
    {
        if ((int) env('AI_CACHE_DAYS', 14) <= 0) {
            return null;
        }

        $question = $this->normalizeQuestion($message);
        if (mb_strlen($question) < 8) {
            return null;
        }

        $hash = hash('sha256', $question . '|' . $intent);
        try {
            $row = DB::table('ai_answer_cache')
                ->where('question_hash', $hash)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();

            if (!$row) {
                return null;
            }

            if (($row->provider ?? '') === 'local_fallback' || Str::contains((string) $row->answer, 'Provider AI sedang tidak tersedia')) {
                return null;
            }

            DB::table('ai_answer_cache')->where('id', $row->id)->update([
                'hit_count' => DB::raw('hit_count + 1'),
                'last_used_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'answer' => $row->answer,
                'provider' => $row->provider ?: 'cache',
                'recommendations' => json_decode($row->recommendations_json ?? '[]', true) ?: [],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function storeCachedAnswer(string $message, string $intent, string $answer, ?string $provider, $products, $articles): void
    {
        if ((int) env('AI_CACHE_DAYS', 14) <= 0 || $provider === 'local_fallback') {
            return;
        }

        $question = $this->normalizeQuestion($message);
        if (mb_strlen($question) < 8 || mb_strlen($answer) < 50) {
            return;
        }

        // Jangan cache sapaan pendek agar tetap natural.
        if ($this->isGreetingOnly($message)) {
            return;
        }

        $hash = hash('sha256', $question . '|' . $intent);
        $recommendations = [
            'products' => $products->pluck('id')->values()->all(),
            'articles' => $articles->pluck('id')->values()->all(),
            'intent' => $intent,
        ];

        try {
            $payload = [
                'question' => $question,
                'intent' => $intent,
                'answer' => $answer,
                'provider' => $provider ?: 'unknown',
                'recommendations_json' => json_encode($recommendations),
                'expires_at' => now()->addDays((int) env('AI_CACHE_DAYS', 14)),
                'updated_at' => now(),
            ];

            $exists = DB::table('ai_answer_cache')->where('question_hash', $hash)->exists();
            if ($exists) {
                DB::table('ai_answer_cache')->where('question_hash', $hash)->update($payload);
            } else {
                $payload['question_hash'] = $hash;
                $payload['hit_count'] = 0;
                $payload['created_at'] = now();
                DB::table('ai_answer_cache')->insert($payload);
            }
        } catch (\Throwable $e) {
            Log::warning('AI cache store failed', ['message' => $e->getMessage()]);
        }
    }

    private function logAiProvider(?int $userId, string $provider, string $status, int $durationMs, string $prompt, ?string $error): void
    {
        try {
            DB::table('ai_provider_logs')->insert([
                'user_id' => $userId,
                'provider' => $provider,
                'model' => match ($provider) {
                    'gemini' => config('services.gemini.model') ?: env('GEMINI_MODEL'),
                    'groq' => config('services.groq.model') ?: env('GROQ_MODEL'),
                    'openrouter' => config('services.openrouter.model') ?: env('OPENROUTER_MODEL'),
                    default => null,
                },
                'status' => $status,
                'duration_ms' => $durationMs,
                'prompt_preview' => Str::limit($prompt, 300),
                'error_message' => $error ? Str::limit($error, 500) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai logging bikin chat error.
        }
    }

}
