<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeaderOne extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('header-one')
            ->schema([
                TextInput::make('text_one'),
                TextInput::make('link_one'),
                TextInput::make('text_two'),
                TextInput::make('link_two'),
                TextInput::make('text_three'),
                TextInput::make('link_three'),
                TextInput::make('text_four'),
                TextInput::make('link_four'),
                
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}