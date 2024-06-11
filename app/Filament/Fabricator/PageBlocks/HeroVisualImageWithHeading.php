<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeroVisualImageWithHeading extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('hero-visual-image-with-heading')
            ->schema([
                TextInput::make('text'),
                TextInput::make('button'),
                TextInput::make('link'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['link'] = url($data['link']);

        return $data;
    }
}
