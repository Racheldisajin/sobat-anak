<?php
namespace App\Http\Controllers;

use App\Models\{Product, Post, Testimonial, Reward, User};

class HomeController extends Controller
{
    public function index()
    {
        $authUser = session('user_id') ? User::find(session('user_id')) : null;
        $likedTestimonialIds = [];

        $testimonials = Testimonial::query()
            ->with('user')
            ->withCount('likes')
            ->orderByDesc('likes_count')
            ->latest()
            ->take(3)
            ->get();

        if ($authUser) {
            $likedTestimonialIds = \App\Models\TestimonialLike::where('user_id', $authUser->id)
                ->pluck('testimonial_id')
                ->all();
        }

        return view('pages.home',[
            'products' => Product::query()
                ->orderByDesc('sold')
                ->orderByDesc('rating')
                ->orderByDesc('id')
                ->take(6)
                ->get(),
            'articles' => Post::with('category')->where('status','published')->latest('published_at')->take(3)->get(),
            'testimonials' => $testimonials,
            'likedTestimonialIds' => $likedTestimonialIds,
            'authUser' => $authUser,
            'rewards' => Reward::all()
        ]);
    }
}
