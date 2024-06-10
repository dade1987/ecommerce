<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Translation;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with('category', 'translations')->get();

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $article = Article::create($request->all());
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $article->update(['image_path' => $path]);
        }

        return redirect()->route('articles.index');
    }

    public function show($id)
    {
        $article = Article::with('category', 'translations')->find($id);

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
        $article = Article::find($id);
        $article->update($request->all());
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
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
