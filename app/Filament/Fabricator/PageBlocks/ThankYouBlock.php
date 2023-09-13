<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ThankYouBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('thank-you')
            ->schema([
                TextInput::make('title'),
                TextInput::make('subtitle'),
                TextInput::make('button_text'),
                TextInput::make('button_link'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
