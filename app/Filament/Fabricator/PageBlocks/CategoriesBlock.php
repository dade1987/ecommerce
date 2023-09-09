<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Category;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class CategoriesBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('categories')
            ->schema([
                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        return [
            'categories' => Category::get()->pluck('name', 'id')->toArray()
        ];
    }
}
