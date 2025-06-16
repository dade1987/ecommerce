<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class OrderReader extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('order-reader')
            ->schema([
                TextInput::make('slug')
                    ->label('Slug dell\'estrattore')
                    ->required(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
