<?php
namespace App\Http\Controllers;
use App\Models\{Product,Post,Testimonial,Reward};
class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home',[
            'products'=>Product::take(6)->get(),
            'articles'=>Post::with('category')->where('status','published')->latest('published_at')->take(3)->get(),
            'testimonials'=>Testimonial::all(),
            'rewards'=>Reward::all()
        ]);
    }
}
