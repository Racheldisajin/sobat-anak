<?php
namespace App\Http\Controllers;

use App\Models\{AiSearchLog, AiTrendKeyword, Product, Post};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiSearchController extends Controller
{
    public function search(Request $request)
    {
        $data = $request->validate([
            'q' => 'required|string|min:2|max:80',
        ]);

        $query = trim($data['q']);
        $normalized = Str::of($query)->lower()->squish()->toString();
        $tokens = collect(preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn ($word) => mb_strlen($word) >= 2)
            ->take(6)
            ->values();

        $products = Product::query()
            ->where(function ($builder) use ($normalized, $tokens) {
                $builder->whereRaw('LOWER(name) LIKE ?', [$normalized.'%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$normalized.'%'])
                    ->orWhereRaw('LOWER(category) LIKE ?', ['%'.$normalized.'%'])
                    ->orWhereRaw('LOWER(COALESCE(badge, "")) LIKE ?', ['%'.$normalized.'%']);

                foreach ($tokens as $token) {
                    $builder->orWhereRaw('LOWER(name) LIKE ?', ['%'.$token.'%'])
                        ->orWhereRaw('LOWER(category) LIKE ?', ['%'.$token.'%'])
                        ->orWhereRaw('LOWER(COALESCE(badge, "")) LIKE ?', ['%'.$token.'%']);
                }
            })
            ->orderByRaw('CASE WHEN LOWER(name) LIKE ? THEN 0 ELSE 1 END', [$normalized.'%'])
            ->orderByDesc('sold')
            ->orderByDesc('rating')
            ->limit(6)
            ->get();

        $posts = Post::with('category')
            ->where('status', 'published')
            ->where(function ($builder) use ($normalized, $tokens) {
                $builder->whereRaw('LOWER(title) LIKE ?', ['%'.$normalized.'%'])
                    ->orWhereRaw('LOWER(content) LIKE ?', ['%'.$normalized.'%'])
                    ->orWhereRaw('LOWER(COALESCE(tags, "")) LIKE ?', ['%'.$normalized.'%']);

                foreach ($tokens as $token) {
                    $builder->orWhereRaw('LOWER(title) LIKE ?', ['%'.$token.'%'])
                        ->orWhereRaw('LOWER(content) LIKE ?', ['%'.$token.'%'])
                        ->orWhereRaw('LOWER(COALESCE(tags, "")) LIKE ?', ['%'.$token.'%']);
                }
            })
            ->latest('published_at')
            ->limit(4)
            ->get();

        $intent = $this->detectIntent($normalized);
        $recommendation = $this->makeRecommendation($normalized, $intent, $products->count(), $posts->count());

        $payload = [
            'ok' => true,
            'query' => $query,
            'intent' => $intent,
            'recommendation' => $recommendation,
            'products' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category,
                'price' => $p->price,
                'image' => $p->image,
                'rating' => $p->rating,
                'sold' => $p->sold,
                'badge' => $p->badge,
                'stock_status' => ((int)($p->stock ?? 0)) <= 0 ? 'Stok Habis' : (((int)($p->stock ?? 0)) <= 5 ? 'Stok tinggal sedikit' : 'Stok tersedia'),
                'url' => route('products').'#product-'.$p->id,
            ])->values(),
            'articles' => $posts->map(fn ($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'category' => $post->category_name,
                'excerpt' => $post->excerpt,
                'image' => $post->image,
                'date' => optional($post->published_at ?? $post->created_at)->format('d M Y'),
                'url' => route('articles'),
            ])->values(),
        ];

        AiSearchLog::create([
            'user_id' => session('user_id'),
            'query' => $query,
            'intent' => $intent,
            'results_count' => $products->count() + $posts->count(),
            'results_meta' => [
                'products' => $products->pluck('id')->values(),
                'posts' => $posts->pluck('id')->values(),
            ],
            'source' => 'landing_ai_search',
        ]);

        return response()->json($payload);
    }

    public function trends()
    {
        $keywords = AiTrendKeyword::query()
            ->where('is_active', 1)
            ->orderByDesc('score')
            ->orderBy('name')
            ->limit(8)
            ->get(['name', 'category', 'score']);

        if ($keywords->isEmpty()) {
            $keywords = collect([
                ['name' => 'botol susu anti kolik', 'category' => 'Bayi', 'score' => 96],
                ['name' => 'popok newborn', 'category' => 'Bayi', 'score' => 91],
                ['name' => 'mainan edukatif', 'category' => 'Edukasi', 'score' => 88],
                ['name' => 'perlengkapan mpasi', 'category' => 'Nutrisi', 'score' => 84],
            ])->map(fn ($item) => (object) $item);
        }

        return response()->json([
            'ok' => true,
            'trends' => $keywords->map(fn ($k) => [
                'name' => $k->name,
                'category' => $k->category,
                'score' => (int) $k->score,
            ])->values(),
        ]);
    }

    private function detectIntent(string $query): string
    {
        return match (true) {
            Str::contains($query, ['botol', 'susu', 'popok', 'newborn', 'bayi']) => 'Kebutuhan bayi',
            Str::contains($query, ['mainan', 'edukatif', 'sensorik', 'puzzle', 'buku']) => 'Edukasi anak',
            Str::contains($query, ['mpasi', 'makan', 'nutrisi', 'sereal']) => 'Nutrisi & MPASI',
            Str::contains($query, ['kulit', 'lotion', 'mandi', 'perawatan']) => 'Perawatan bayi',
            Str::contains($query, ['baju', 'pakaian', 'sepatu']) => 'Pakaian anak',
            default => 'Rekomendasi SobatAnak',
        };
    }

    private function makeRecommendation(string $query, string $intent, int $productCount, int $postCount): string
    {
        if ($productCount === 0 && $postCount === 0) {
            return 'Belum ada hasil yang pas. Coba pakai kata yang lebih umum seperti botol susu, popok, mainan edukatif, atau MPASI.';
        }

        return "AI SobatAnak menangkap pencarian ini sebagai {$intent}. Kami tampilkan produk yang paling relevan dan artikel pendukung supaya kamu bisa belanja sekaligus membaca panduannya.";
    }
}
