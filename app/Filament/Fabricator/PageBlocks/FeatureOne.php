<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class FeatureOne extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('feature-one')
            ->schema([
                TextInput::make('anchor'),
                TextInput::make('text_one'),
                TextInput::make('text_two'),
                TextInput::make('text_three'),
                TextInput::make('text_four'),
                TextInput::make('text_five'),
                TextInput::make('text_six'),
                TextInput::make('text_seven'),
                TextInput::make('text_eight'),
                TextInput::make('text_nine'),
                TextInput::make('text_ten'),

                TextInput::make('anchor_two'),
                TextInput::make('text_eleven'),
                TextInput::make('text_twelve'),
                TextInput::make('text_thirteen'),
                TextInput::make('text_fourteen'),
                TextInput::make('text_fifteen'),
                TextInput::make('text_sixteen'),
                TextInput::make('text_seventeen'),
                TextInput::make('text_eighteen'),
                TextInput::make('link_one'),
                TextInput::make('link_two'),

            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
