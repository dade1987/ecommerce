<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct()
    {
        // Tutte le operazioni richiedono autenticazione
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = Category::all();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'is_hidden' => 'nullable|boolean',
            'order_column' => 'nullable|integer|min:0',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index');
    }

    public function show($id)
    {
        $category = Category::find($id);

        return view('categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::find($id);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'is_hidden' => 'nullable|boolean',
            'order_column' => 'nullable|integer|min:0',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index');
    }

    public function destroy($id)
    {
        Category::destroy($id);

        return redirect()->route('categories.index');
    }
}
