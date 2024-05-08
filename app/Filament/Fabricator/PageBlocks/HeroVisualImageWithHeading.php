<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeroVisualImageWithHeading extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('hero-visual-image-with-heading')
            ->schema([
                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
