<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Diglactic\Breadcrumbs\Breadcrumbs;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Http\Controllers\PageController;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /*$value = Route::current()->uri();

        $pageModel = FilamentFabricator::getPageModel();

        $pageUrls = FilamentFabricator::getPageUrls();

        $value = Str::start($value, '/');

        $pageId = array_search($value, $pageUrls);

        $page = $pageModel::query()
            ->where('id', $pageId)
            ->firstOrFail();

        $view = app(PageController::class)($page);

        return $view;*/

        //$categories = Category::all();
        //$breadcrumbs = collect(Breadcrumbs::generate(Route::currentRouteName()))->pluck('title', 'url')->toArray();

        // return view('categories.index', compact('breadcrumbs', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
