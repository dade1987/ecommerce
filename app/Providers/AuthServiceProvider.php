<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Product;
use App\Models\Category;
use App\Models\Article;
use App\Models\Quoter;
use App\Policies\PagePolicy;
use App\Policies\MediaPolicy;
use App\Policies\ProductPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ArticlePolicy;
use App\Policies\QuoterPolicy;
use Awcodes\Curator\Models\Media;
use Z3d0X\FilamentFabricator\Models\Page;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Extractor;
use App\Policies\ExtractorPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Extractor::class => ExtractorPolicy::class,
        Article::class => ArticlePolicy::class,
        Category::class => CategoryPolicy::class,
        Media::class => MediaPolicy::class,
        Page::class => PagePolicy::class,
        Product::class => ProductPolicy::class,
        Quoter::class => QuoterPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
