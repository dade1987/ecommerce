<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class RestaurantSearchBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('restaurant-search-block')
            ->schema([
                // Nessun campo custom per ora
            ]);
    }

    public static function mutateData(array $data): array
    {
        // Passo solo la collection completa dei ristoranti all'inizio
        return [
            'restaurants' => \App\Models\Restaurant::all(),
        ];
    }
} 