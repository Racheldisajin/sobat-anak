<?php
namespace App\Http\Controllers;
use App\Models\Post;
class ArticleController extends Controller
{
    public function index()
    {
        return view('pages.articles',[
            'articles'=>Post::with('category')->where('status','published')->latest('published_at')->get()
        ]);
    }
}
