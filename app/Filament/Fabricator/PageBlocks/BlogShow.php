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
        $data['row'] = Article::findOrFail(request()->route('item0'));

        return $data;
    }
}
