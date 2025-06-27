<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Article;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class BlogShow extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('blog-show')
            ->schema([
                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        $article = Article::where('slug', request()->route('item0'))->first() ?? Article::findOrFail(request()->route('item0'));
        $data['row'] = $article;

        \Illuminate\Support\Facades\View::share('pageTitle', $article->title . ' - Cavallini Service' );
        \Illuminate\Support\Facades\View::share('pageDescription', $article->summary);
        \Illuminate\Support\Facades\View::share('ogImage', $article->featuredImage->path);

        return $data;
    }
}
