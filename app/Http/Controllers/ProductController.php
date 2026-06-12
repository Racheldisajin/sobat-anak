<?php
namespace App\Http\Controllers;

use App\Models\{Product, ProductReview};
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return view('pages.products', [
            'products' => Product::query()
                ->orderByDesc('sold')
                ->orderByDesc('rating')
                ->orderByDesc('id')
                ->get(),
            'categories' => Product::select('category')
                ->distinct()
                ->pluck('category'),
        ]);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $reviews = ProductReview::with('user')
            ->where('product_id', $id)
            ->latest()
            ->get();
        $avgRating = $reviews->count() ? round($reviews->avg('rating'), 1) : $product->rating;
        $related = Product::where('category', $product->category)
            ->where('id', '!=', $id)
            ->orderByDesc('sold')
            ->take(4)
            ->get();
        $authUser = \App\Models\User::find(session('user_id'));
        $userAddress = $authUser ? \App\Models\UserAddress::where('user_id', $authUser->id)->first() : null;
        $userReview = $authUser ? ProductReview::where('product_id', $id)->where('user_id', $authUser->id)->first() : null;

        return view('pages.product-detail', compact('product', 'reviews', 'avgRating', 'related', 'authUser', 'userAddress', 'userReview'));
    }
}
