<?php

namespace App\Http\Controllers;

use App\Models\Post;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Post::with('category')
            ->where('status', 'published')
            ->orderByDesc('counter')
            ->latest('published_at')
            ->latest('id')
            ->get();

        return view('pages.articles', [
            'articles' => $articles,
        ]);
    }

    public function show(string $slug)
    {
        $article = Post::with('category')
            ->where('status', 'published')
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->firstOrFail();

        $article->increment('counter');

        $related = Post::with('category')
            ->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('pages.article-detail', compact('article', 'related'));
    }
}
