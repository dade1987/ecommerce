<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class CartBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('cart')
            ->schema([
                TextInput::make('back_to_shop_link'),
                TextInput::make('next_link')
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
