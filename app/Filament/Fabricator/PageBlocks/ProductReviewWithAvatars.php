<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ProductReviewWithAvatars extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('product-review-with-avatars')
            ->schema([
                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
