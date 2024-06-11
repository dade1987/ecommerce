<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class DefaultFeatureList extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('default-feature-list')
            ->schema([
                TextInput::make('title'),
                TextInput::make('text'),

                TextInput::make('titleOne'),
                TextInput::make('textOne'),
                TextInput::make('titleTwo'),
                TextInput::make('textTwo'),
                TextInput::make('titleThree'),
                TextInput::make('textThree'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
