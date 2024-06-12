<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TimeLine extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('time-line')
            ->schema([
                TextInput::make('background'),
                TextInput::make('text'),
                TextInput::make('text_two'),
                TextInput::make('text_three'),
                TextInput::make('text_four'),
                TextInput::make('text_five'),
                TextInput::make('text_six'),
                TextInput::make('text_seven'),
                TextInput::make('text_eight'),
                
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['background'] = url('images/' . $data['background']);
        return $data;
    }
}
