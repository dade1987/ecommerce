<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeroBackgroundImage extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('hero-background-image')
            ->schema([
                TextInput::make('image_url'),
                TextInput::make('text_one'),
                TextInput::make('text_two'),
                TextInput::make('text_button'),
                TextInput::make('link_button'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}