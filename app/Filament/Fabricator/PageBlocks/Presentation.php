<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Presentation extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('presentation')
            ->schema([
                TextInput::make('background'),
                TextInput::make('background_two'),
                TextInput::make('text'),
                TextInput::make('text_two'),
                
                
                
               
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['background'] = url('images/' . $data['background']);
        $data['background_two'] = url('images/' . $data['background_two']);
        return $data;
    }
}
