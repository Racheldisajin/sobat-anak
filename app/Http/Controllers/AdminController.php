<?php
namespace App\Http\Controllers;

use App\Models\{GameSetting,Post,PostCategory,Product,ProductReview,Reward,Testimonial,User,UserPoint,CartItem,RewardClaim};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function adminUser()
    {
        $user = User::find(session('user_id'));
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan login sebagai admin dulu.']);
        }
        if (($user->role ?? 'user') !== 'admin') {
            abort(403, 'Halaman ini khusus admin.');
        }
        return $user;
    }


    private function uploadAdminImageFile($file): ?string
    {
        if (!$file) return null;

        $filename = now()->format('YmdHis') . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $destination = public_path('uploads/admin');

        if (!is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);
        return '/uploads/admin/' . $filename;
    }

    private function uploadAdminImage(Request $request, string $field, ?string $oldPath = null): ?string
    {
        if (!$request->hasFile($field)) {
            return $oldPath;
        }

        return $this->uploadAdminImageFile($request->file($field));
    }

    private function syncProductGallery(Product $product, Request $request, array $extraImages = [], bool $replace = false): void
    {
        if (!Schema::hasTable('product_images')) {
            return;
        }

        if ($replace) {
            DB::table('product_images')->where('product_id', $product->id)->delete();
        }

        $paths = [];
        if (!empty($product->image)) {
            $paths[] = $product->image;
        }

        foreach ($extraImages as $img) {
            if ($img) $paths[] = $img;
        }

        if ($request->hasFile('gallery_images')) {
            foreach ((array) $request->file('gallery_images') as $file) {
                if ($file) {
                    $paths[] = $this->uploadAdminImageFile($file);
                }
            }
        }

        $paths = collect($paths)->filter()->unique()->values();
        if ($paths->isEmpty()) {
            return;
        }

        $existing = DB::table('product_images')
            ->where('product_id', $product->id)
            ->pluck('image')
            ->all();

        $sort = (int) DB::table('product_images')
            ->where('product_id', $product->id)
            ->max('sort_order');

        foreach ($paths as $path) {
            if (in_array($path, $existing, true)) {
                continue;
            }
            $sort++;
            DB::table('product_images')->insert([
                'product_id' => $product->id,
                'image' => $path,
                'sort_order' => $sort,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


    /**
     * Cek kolom dengan SHOW COLUMNS agar aman untuk MySQL/MariaDB lama.
     * Laravel 13 bisa memanggil kolom generation_expression saat hasColumn(),
     * sedangkan beberapa versi database lokal belum punya kolom itu di information_schema.
     */
    private function dbColumnExistsSafe(string $table, string $column): bool
    {
        $safeTable = str_replace('`', '``', $table);

        try {
            return DB::selectOne("SHOW COLUMNS FROM `{$safeTable}` LIKE ?", [$column]) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function dashboard()
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        // Penjualan dashboard harus data real dari transaksi/order, bukan angka dummy products.sold.
        // Karena payment/order belum dibuat, semua grafik penjualan default 0 dulu.
        $salesTotalQty = 0;
        $estimatedRevenue = 0;
        $topSellingProducts = collect();

        $salesChartLabels = [];
        $salesChartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $salesChartLabels[] = $date->format('d M');
            $salesChartValues[$date->format('Y-m-d')] = 0;
        }

        if (DB::getSchemaBuilder()->hasTable('order_items')) {
            $orderItems = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id');

            if (DB::getSchemaBuilder()->hasTable('orders')) {
                $orderItems->join('orders', 'order_items.order_id', '=', 'orders.id');

                if ($this->dbColumnExistsSafe('orders', 'payment_status')) {
                    $orderItems->whereIn('orders.payment_status', ['paid', 'settlement', 'success']);
                } elseif ($this->dbColumnExistsSafe('orders', 'status')) {
                    $orderItems->whereIn('orders.status', ['paid', 'settlement', 'success', 'completed']);
                }
            }

            $salesTotalQty = (int) (clone $orderItems)->sum('order_items.quantity');
            $estimatedRevenue = (int) (clone $orderItems)
                ->selectRaw('COALESCE(SUM(order_items.quantity * COALESCE(order_items.price, products.price)),0) as total')
                ->value('total');

            $topSellingProducts = (clone $orderItems)
                ->select(
                    'products.id',
                    'products.name',
                    'products.category',
                    'products.image',
                    'products.stock'
                )
                ->selectRaw('SUM(order_items.quantity) as real_sold')
                ->selectRaw('SUM(order_items.quantity * COALESCE(order_items.price, products.price)) as real_revenue')
                ->groupBy('products.id', 'products.name', 'products.category', 'products.image', 'products.stock')
                ->orderByDesc('real_sold')
                ->take(5)
                ->get();

            $createdColumn = $this->dbColumnExistsSafe('orders', 'created_at')
                ? 'orders.created_at'
                : 'order_items.created_at';

            $dailySales = (clone $orderItems)
                ->whereDate($createdColumn, '>=', now()->subDays(6)->format('Y-m-d'))
                ->selectRaw('DATE('.$createdColumn.') as sales_date')
                ->selectRaw('COALESCE(SUM(order_items.quantity * COALESCE(order_items.price, products.price)),0) as revenue')
                ->groupBy('sales_date')
                ->pluck('revenue', 'sales_date');

            foreach ($salesChartValues as $date => $value) {
                $salesChartValues[$date] = (int) ($dailySales[$date] ?? 0);
            }
        }

        $salesChart = [
            'labels' => $salesChartLabels,
            'values' => array_values($salesChartValues),
            'max' => max(array_values($salesChartValues)) ?: 1,
        ];

        $averageProductRating = ProductReview::count()
            ? round((float) ProductReview::avg('rating'), 1)
            : 0;

        $stats = [
            'articles' => Post::count(),
            'published_articles' => Post::where('status', 'published')->count(),
            'draft_articles' => Post::where('status', 'draft')->count(),
            'products' => Product::count(),
            'users' => User::count(),
            'rewards' => Reward::count(),
            'claims' => RewardClaim::count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out_stock' => Product::where('stock', '<=', 0)->count(),
            'reviews' => ProductReview::count(),
            'sales_qty' => $salesTotalQty,
            'estimated_revenue' => $estimatedRevenue,
            'average_rating' => $averageProductRating,
        ];

        $latestArticles = Post::with('category')->latest('published_at')->latest('created_at')->take(5)->get();
        $latestUsers = User::latest()->take(5)->get();
        $lowStockProducts = Product::where('stock', '<=', 5)->orderBy('stock')->take(5)->get();

        return view('pages.admin.dashboard', compact(
            'admin',
            'stats',
            'latestArticles',
            'latestUsers',
            'topSellingProducts',
            'lowStockProducts',
            'salesChart'
        ));
    }

    public function articles(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'all');
        $categoryId = $request->query('category_id', 'all');

        if (!in_array($status, ['all', 'published', 'draft'], true)) {
            $status = 'all';
        }

        $categories = PostCategory::orderBy('name')->get();

        $articleQuery = Post::with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('tags', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereHas('category', fn($cat) => $cat->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== 'all', fn($query) => $query->where('status', $status))
            ->when($categoryId !== 'all' && $categoryId !== null && $categoryId !== '', fn($query) => $query->where('category_id', $categoryId));

        $articles = (clone $articleQuery)->latest('created_at')->get();

        $stats = [
            'total' => Post::count(),
            'published' => Post::where('status', 'published')->count(),
            'draft' => Post::where('status', 'draft')->count(),
            'views' => (int) Post::sum('counter'),
        ];

        return view('pages.admin.articles', compact(
            'admin',
            'articles',
            'search',
            'status',
            'categoryId',
            'categories',
            'stats'
        ));
    }

    public function createArticle()
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $categories = PostCategory::orderBy('name')->get();
        return view('pages.admin.article-form', ['admin'=>$admin, 'article'=>new Post(), 'mode'=>'create', 'categories'=>$categories]);
    }

    public function storeArticle(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'slug'=>'nullable|string|max:255',
            'category_id'=>'required|exists:post_categories,id',
            'tags'=>'nullable|string|max:255',
            'status'=>'required|in:draft,published',
            'content'=>'required|string',
            'image_file'=>'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        $slug = $data['slug'] ?: Str::slug($data['title']);
        $baseSlug = $slug;
        $counter = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }
        $data['slug'] = $slug;
        $data['image'] = $this->uploadAdminImage($request, 'image_file');
        $data['created_by'] = $admin->id;
        $data['updated_by'] = null;
        $data['counter'] = 0;
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        $data['source'] = 'web';
        $data['meta_data'] = json_encode(['editor'=>'SobatAnak Admin']);
        unset($data['image_file']);
        Post::create($data);
        return redirect()->route('admin.articles')->with('success','Postingan berita berhasil ditambahkan.');
    }

    public function editArticle(Post $post)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $categories = PostCategory::orderBy('name')->get();
        return view('pages.admin.article-form', ['admin'=>$admin, 'article'=>$post, 'mode'=>'edit', 'categories'=>$categories]);
    }

    public function updateArticle(Request $request, Post $post)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'slug'=>'nullable|string|max:255',
            'category_id'=>'required|exists:post_categories,id',
            'tags'=>'nullable|string|max:255',
            'status'=>'required|in:draft,published',
            'content'=>'required|string',
            'image_file'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        $slug = $data['slug'] ?: Str::slug($data['title']);
        $baseSlug = $slug;
        $counter = 1;
        while (Post::where('slug', $slug)->where('id','!=',$post->id)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }
        $data['slug'] = $slug;
        $data['image'] = $this->uploadAdminImage($request, 'image_file', $post->image);
        $data['updated_by'] = $admin->id;
        if ($data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }
        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }
        unset($data['image_file']);
        $post->update($data);
        return redirect()->route('admin.articles')->with('success','Postingan berita berhasil diperbarui.');
    }

    public function destroyArticle(Post $post)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $post->delete();
        return back()->with('success','Postingan berita berhasil dihapus.');
    }

    public function products(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        $search = trim((string) $request->query('search', ''));
        $stockFilter = $request->query('stock');
        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('badge', 'like', "%{$search}%");
                });
            })
            ->when($stockFilter === 'habis', fn($query) => $query->where('stock', '<=', 0))
            ->when($stockFilter === 'tersedia', fn($query) => $query->where('stock', '>', 0))
            ->latest()
            ->get();

        return view('pages.admin.products', compact('admin','products','search','stockFilter'));
    }

    public function createProduct()
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        return view('pages.admin.product-form', compact('admin'));
    }

    public function storeProduct(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate([
            'name'=>'required|string|max:160', 'category'=>'required|string|max:80', 'price'=>'required|integer|min:0',
            'badge'=>'nullable|string|max:80', 'rating'=>'nullable|numeric|min:0|max:5', 'sold'=>'nullable|integer|min:0',
            'stock'=>'required|integer|min:0',
            'image_file'=>'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery_images'=>'nullable|array|max:6',
            'gallery_images.*'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        $data['image'] = $this->uploadAdminImage($request, 'image_file');
        unset($data['image_file'], $data['gallery_images']);
        $data['rating'] = $data['rating'] ?? 4.8;
        $data['sold'] = $data['sold'] ?? 0;
        $product = Product::create($data);
        $this->syncProductGallery($product, $request, [], true);
        return redirect()->route('admin.products')->with('success','Produk berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate([
            'name'=>'required|string|max:160', 'category'=>'required|string|max:80', 'price'=>'required|integer|min:0',
            'badge'=>'nullable|string|max:80', 'rating'=>'nullable|numeric|min:0|max:5', 'sold'=>'nullable|integer|min:0',
            'stock'=>'required|integer|min:0',
            'image_file'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery_images'=>'nullable|array|max:6',
            'gallery_images.*'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'replace_gallery'=>'nullable|boolean',
        ]);
        $oldImage = $product->image;
        $data['image'] = $this->uploadAdminImage($request, 'image_file', $product->image);
        unset($data['image_file'], $data['gallery_images'], $data['replace_gallery']);
        $product->update($data);
        $product->refresh();
        $replace = $request->boolean('replace_gallery');
        $this->syncProductGallery($product, $request, [], $replace);
        if (!$replace && $oldImage && Schema::hasTable('product_images')) {
            // biarkan foto lama tetap ada sebagai thumbnail kalau admin mengganti gambar utama.
            $this->syncProductGallery($product, $request, [$oldImage], false);
        }
        return back()->with('success','Produk berhasil diperbarui.');
    }

    public function destroyProduct(Product $product)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $product->delete();
        return back()->with('success','Produk berhasil dihapus.');
    }

    public function rewards()
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $rewards = Reward::latest()->get();
        $claims = RewardClaim::latest()->take(20)->get();
        return view('pages.admin.rewards', compact('admin','rewards','claims'));
    }

    public function storeReward(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate(['name'=>'required|string|max:150','points'=>'required|integer|min:1','description'=>'required|string|max:350']);
        Reward::create($data);
        return back()->with('success','Reward berhasil ditambahkan.');
    }

    public function destroyReward(Reward $reward)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $reward->delete();
        return back()->with('success','Reward berhasil dihapus.');
    }



    public function games()
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        if (!DB::getSchemaBuilder()->hasTable('game_settings')) {
            return view('pages.admin.games', [
                'admin' => $admin,
                'games' => collect(),
                'tableMissing' => true,
            ]);
        }

        $games = GameSetting::orderBy('sort_order')->get();
        return view('pages.admin.games', compact('admin', 'games'));
    }

    public function updateGame(Request $request, GameSetting $gameSetting)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'icon' => 'required|string|max:16',
            'color' => 'required|string|max:30',
            'description' => 'required|string|max:500',
            'game_path' => 'nullable|string|max:255',
            'points_per_play' => 'required|integer|min:0|max:999',
            'max_points' => 'required|integer|min:0|max:9999',
            'sort_order' => 'required|integer|min:1|max:99',
            'is_active' => 'nullable|boolean',
            'cover_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'puzzle_image_1' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'puzzle_image_2' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'puzzle_image_3' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['cover_image'] = $this->uploadAdminImage($request, 'cover_image_file', $gameSetting->cover_image);
        unset($data['cover_image_file']);

        $settings = $gameSetting->settings ?: [];
        if ($gameSetting->slug === 'puzzle-edukatif') {
            for ($i = 1; $i <= 3; $i++) {
                $field = 'puzzle_image_'.$i;
                if ($request->hasFile($field)) {
                    $settings[$field] = $this->uploadAdminImage($request, $field, $settings[$field] ?? null);
                }
                unset($data[$field]);
            }
        } else {
            unset($data['puzzle_image_1'], $data['puzzle_image_2'], $data['puzzle_image_3']);
        }

        $data['settings'] = $settings;
        $gameSetting->update($data);

        return back()->with('success', 'Setting game '.$gameSetting->title.' berhasil diperbarui.');
    }

    public function testimonials()
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $testimonials = Testimonial::latest()->get();
        return view('pages.admin.testimonials', compact('admin','testimonials'));
    }

    public function storeTestimonial(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate(['name'=>'required|string|max:100','message'=>'required|string|max:400']);
        Testimonial::create($data);
        return back()->with('success','Testimonial berhasil ditambahkan.');
    }

    public function destroyTestimonial(Testimonial $testimonial)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $testimonial->delete();
        return back()->with('success','Testimonial berhasil dihapus.');
    }
}
