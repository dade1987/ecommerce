<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Article;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class BlogIndex extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('blog-index')
            ->schema([
                TextInput::make('title'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['rows'] = Article::get();

        return $data;
    }
}
