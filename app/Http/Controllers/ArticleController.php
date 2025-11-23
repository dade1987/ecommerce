<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Translation;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct()
    {
        // Tutte le operazioni richiedono autenticazione
        $this->middleware('auth');
    }

    public function index()
    {
        $articles = Article::with('tags', 'translations')->get();

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'summary' => 'nullable|string|max:500',
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Rimuovi 'image' dai dati validati perché non è un campo del model
        $articleData = collect($validated)->except('image')->toArray();
        $article = Article::create($articleData);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
            $article->update(['image_path' => $path]);
        }

        return redirect()->route('articles.index');
    }

    public function show($id)
    {
        $article = Article::with('tags', 'translations')->find($id);

        return view('articles.show', compact('article'));
    }

    public function edit($id)
    {
        $article = Article::find($id);
        $categories = Category::all();

        return view('articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'required|string|max:255|unique:articles,slug,' . $id,
            'summary' => 'nullable|string|max:500',
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Rimuovi 'image' dai dati validati perché non è un campo del model
        $articleData = collect($validated)->except('image')->toArray();
        $article->update($articleData);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
            $article->update(['image_path' => $path]);
        }

        return redirect()->route('articles.index');
    }

    public function destroy($id)
    {
        Article::destroy($id);

        return redirect()->route('articles.index');
    }
}
