<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Category;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class CategoriesBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('categories')
            ->schema([
                //CuratorPicker::make('header_image')
            ]);
    }

    public static function mutateData(array $data): array
    {
        return [
            //'header_image' => $data['header_image'],
            'categories' => Category::get()
        ];
    }
}
