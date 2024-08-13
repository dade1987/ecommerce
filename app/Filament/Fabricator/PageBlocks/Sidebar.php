<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Menu;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Sidebar extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('sidebar')
            ->schema([
                TextInput::make('name'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['items'] = Menu::firstWhere('name', $data['name'])->items;
        
        return $data;
    }
}