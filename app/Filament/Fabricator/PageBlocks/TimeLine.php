<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Repeater;
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

                Repeater::make('cards')
                    ->schema([
                        TextInput::make('title_left')->required(),
                        TextInput::make('text_left')->required(),

                        TextInput::make('title_right')->required(),
                        TextInput::make('text_right')->required(),
                    ])
                /* TextInput::make('text'),
                TextInput::make('text_two'),
                TextInput::make('text_three'),
                TextInput::make('text_four'),
                TextInput::make('text_five'),
                TextInput::make('text_six'),
                TextInput::make('text_seven'),
                TextInput::make('text_eight'),
                TextInput::make('text_nine'),
                TextInput::make('text_ten'),
                TextInput::make('text_eleven'),
                TextInput::make('text_twelve'),
                TextInput::make('text_thirteen'),
                TextInput::make('text_fourteen'),
                TextInput::make('text_fifteen'),
                TextInput::make('text_sixteen'),
                TextInput::make('text_seventeen'),
                TextInput::make('text_eighteen'),
                TextInput::make('text_nineteen'),
                TextInput::make('text_twenty'), */


            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['background'] = url('images/' . $data['background']);
        return $data;
    }
}
