<?php

namespace App\Http\Controllers;

use App\Models\GameSetting;
use App\Models\Reward;
use App\Models\UserPoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class MiniGameController extends Controller
{
    private function defaultGameCatalog(): array
    {
        return [
            'puzzle-edukatif' => [
                'title' => 'Puzzle Edukatif',
                'icon' => '🧩',
                'color' => 'teal',
                'desc' => 'Susun puzzle lucu sambil melatih fokus, logika, dan kesabaran si kecil.',
                'points_per_play' => 10,
                'max_points' => 60,
                'sort_order' => 1,
                'game_path' => 'games/puzzle/dist/index.html',
            ],
            'memory-card' => [
                'title' => 'Memory Card',
                'icon' => '🃏',
                'color' => 'purple',
                'desc' => 'Cocokkan kartu berpasangan dan asah daya ingat dengan cara menyenangkan.',
                'points_per_play' => 10,
                'max_points' => 60,
                'sort_order' => 2,
                'game_path' => 'games/memory-card/dist/index.html',
            ],
            'tap-tap-kuman' => [
                'title' => 'TapTap Kuman',
                'icon' => '🦠',
                'color' => 'orange',
                'desc' => 'Tap kuman secepat mungkin, kejar skor, dan kumpulkan poin SobatAnak.',
                'points_per_play' => 1,
                'max_points' => 60,
                'sort_order' => 3,
                'game_path' => 'games/tap-tap-kuman/index.html',
            ],
            'keranjang-sehat' => [
                'title' => 'Keranjang Sehat',
                'icon' => '🧺',
                'color' => 'green',
                'desc' => 'Tangkap makanan sehat, hindari junk food, dan belajar memilih makanan baik.',
                'points_per_play' => 10,
                'max_points' => 60,
                'sort_order' => 4,
                'game_path' => 'games/keranjang-sehat/index.html',
            ],
            'sikat-gigi' => [
                'title' => 'Sikat Gigi',
                'icon' => '🦷',
                'color' => 'blue',
                'desc' => 'Bersihkan gigi dari kuman lucu agar anak makin semangat menjaga kesehatan gigi.',
                'points_per_play' => 10,
                'max_points' => 60,
                'sort_order' => 5,
                'game_path' => 'games/sikat-gigi/index.html',
            ],
            'mewarnai' => [
                'title' => 'Game Mewarnai',
                'icon' => '🎨',
                'color' => 'pink',
                'desc' => 'Warnai gambar dengan bebas untuk melatih kreativitas dan imajinasi anak.',
                'points_per_play' => 10,
                'max_points' => 50,
                'sort_order' => 6,
                'game_path' => 'games/mewarnai/index.html',
            ],
        ];
    }

    private function gameCatalog()
    {
        $defaults = collect($this->defaultGameCatalog());

        if (!DB::getSchemaBuilder()->hasTable('game_settings')) {
            return $defaults->map(function ($game, $slug) {
                return $this->normalizeGame($slug, $game);
            })->values();
        }

        $settings = GameSetting::query()->orderBy('sort_order')->get()->keyBy('slug');

        return $defaults->map(function ($game, $slug) use ($settings) {
            $row = $settings->get($slug);
            if ($row) {
                $game = array_merge($game, [
                    'title' => $row->title ?: $game['title'],
                    'icon' => $row->icon ?: $game['icon'],
                    'color' => $row->color ?: $game['color'],
                    'desc' => $row->description ?: $game['desc'],
                    'is_active' => (bool) $row->is_active,
                    'points_per_play' => (int) ($row->points_per_play ?? $game['points_per_play']),
                    'max_points' => (int) ($row->max_points ?? $game['max_points']),
                    'sort_order' => (int) ($row->sort_order ?? $game['sort_order']),
                    'game_path' => $row->game_path ?: $game['game_path'],
                    'cover_image' => $row->cover_image,
                    'settings' => $row->settings ?: [],
                ]);
            }
            return $this->normalizeGame($slug, $game);
        })->sortBy('sort_order')->values();
    }

    private function normalizeGame(string $slug, array $game): array
    {
        $routeMap = [
            'puzzle-edukatif' => 'mini-games.puzzle-edukatif',
            'memory-card' => 'mini-games.memory-card',
            'tap-tap-kuman' => 'mini-games.tap-tap-kuman',
            'keranjang-sehat' => 'mini-games.keranjang-sehat',
            'sikat-gigi' => 'mini-games.sikat-gigi',
            'mewarnai' => 'mini-games.mewarnai',
        ];

        $game['slug'] = $slug;
        $game['url'] = Route::has($routeMap[$slug] ?? '') ? route($routeMap[$slug]) : route('mini-games');
        $game['is_active'] = $game['is_active'] ?? true;
        $game['available'] = $this->gameAssetExists((string) ($game['game_path'] ?? ''));
        return $game;
    }

    private function gameAssetExists(string $path): bool
    {
        $path = trim($path, '/');
        if ($path === '') return false;
        return file_exists(public_path($path));
    }

    private function getGameSetting(string $slug): ?GameSetting
    {
        if (!DB::getSchemaBuilder()->hasTable('game_settings')) {
            return null;
        }
        return GameSetting::where('slug', $slug)->first();
    }

    public function index()
    {
        $leaderboard = UserPoint::with('user')
            ->orderByDesc('points')
            ->take(10)
            ->get();

        $games = $this->gameCatalog()->values();
        $activeGameCount = $games->filter(fn($game) => !empty($game['is_active']))->count();

        return view('pages.mini-games', [
            'games' => $games,
            'activeGameCount' => $activeGameCount,
            'rewards' => Reward::all(),
            'leaderboard' => $leaderboard,
        ]);
    }

    public function tapTapKuman()
    {
        return view('pages.game-tap-tap-kuman', ['gameSetting' => $this->getGameSetting('tap-tap-kuman')]);
    }

    public function keranjangSehat()
    {
        return view('pages.game-keranjang-sehat', ['gameSetting' => $this->getGameSetting('keranjang-sehat')]);
    }

    public function puzzleEdukatif()
    {
        return view('pages.game-puzzle-edukatif', ['gameSetting' => $this->getGameSetting('puzzle-edukatif')]);
    }

    public function memoryCard()
    {
        return view('pages.game-memory-card', ['gameSetting' => $this->getGameSetting('memory-card')]);
    }

    public function sikatGigi()
    {
        return view('pages.game-sikat-gigi', ['gameSetting' => $this->getGameSetting('sikat-gigi')]);
    }

    public function mewarnai()
    {
        return view('pages.game-mewarnai', ['gameSetting' => $this->getGameSetting('mewarnai')]);
    }
}
