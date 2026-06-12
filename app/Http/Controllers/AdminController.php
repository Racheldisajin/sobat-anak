<?php
namespace App\Http\Controllers;

use App\Models\{Post,PostCategory,Product,Reward,Testimonial,User,UserPoint,CartItem,RewardClaim};
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


    private function uploadAdminImage(Request $request, string $field, ?string $oldPath = null): ?string
    {
        if (!$request->hasFile($field)) {
            return $oldPath;
        }

        $file = $request->file($field);
        $filename = now()->format('YmdHis') . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $destination = public_path('uploads/admin');

        if (!is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);
        return '/uploads/admin/' . $filename;
    }

    public function dashboard()
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        $stats = [
            'articles' => Post::count(),
            'products' => Product::count(),
            'users' => User::count(),
            'cart_items' => CartItem::sum('quantity'),
            'rewards' => Reward::count(),
            'claims' => RewardClaim::count(),
        ];
        $latestArticles = Post::with('category')->latest('published_at')->take(5)->get();
        $latestUsers = User::latest()->take(6)->get();
        $cartItems = CartItem::with(['product','user'])->latest()->take(8)->get();
        $topPoints = UserPoint::with('user')->orderByDesc('points')->take(6)->get();
        return view('pages.admin.dashboard', compact('admin','stats','latestArticles','latestUsers','cartItems','topPoints'));
    }

    public function articles(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;

        $search = trim((string) $request->query('search', ''));
        $articles = Post::with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhere('tags', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereHas('category', fn($cat) => $cat->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('created_at')
            ->get();

        return view('pages.admin.articles', compact('admin','articles','search'));
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

    public function storeProduct(Request $request)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate([
            'name'=>'required|string|max:160', 'category'=>'required|string|max:80', 'price'=>'required|integer|min:0',
            'badge'=>'nullable|string|max:80', 'rating'=>'nullable|numeric|min:0|max:5', 'sold'=>'nullable|integer|min:0',
            'stock'=>'required|integer|min:0',
            'image_file'=>'required|image|mimes:jpg,jpeg,png,webp|max:4096'
        ]);
        $data['image'] = $this->uploadAdminImage($request, 'image_file');
        unset($data['image_file']);
        $data['rating'] = $data['rating'] ?? 4.8;
        $data['sold'] = $data['sold'] ?? 0;
        Product::create($data);
        return back()->with('success','Produk berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $admin = $this->adminUser();
        if (!$admin instanceof User) return $admin;
        $data = $request->validate([
            'name'=>'required|string|max:160', 'category'=>'required|string|max:80', 'price'=>'required|integer|min:0',
            'badge'=>'nullable|string|max:80', 'rating'=>'nullable|numeric|min:0|max:5', 'sold'=>'nullable|integer|min:0',
            'stock'=>'required|integer|min:0',
            'image_file'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096'
        ]);
        $data['image'] = $this->uploadAdminImage($request, 'image_file', $product->image);
        unset($data['image_file']);
        $product->update($data);
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
