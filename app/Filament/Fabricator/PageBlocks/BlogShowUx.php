<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Article;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Contracts\View\View;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class BlogShowUx extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('blog-show-ux')
            ->label('Blog Show (UX)')
            ->schema([
                // Nessun campo: CTA e layout sono fissi per coerenza UX.
            ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function mutateData(array $data): array
    {
        $param = (string) (request()->route('item0') ?? '');

        $article = Article::where('slug', $param)->first()
            ?? (is_numeric($param) ? Article::find((int) $param) : null);

        if (! $article) {
            $article = Article::findOrFail($param);
        }

        $data['row'] = $article;

        \Illuminate\Support\Facades\View::share('pageTitle', $article->title);
        \Illuminate\Support\Facades\View::share('pageDescription', $article->summary);
        \Illuminate\Support\Facades\View::share('ogImage', $article->featuredImage?->path);

        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.blog-show-ux');
    }
}
