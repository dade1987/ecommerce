<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Menu;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Navbar extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('navbar')
            ->schema([
                Checkbox::make('logo_border')->default(false),
                TextInput::make('logo_url'),
                TextInput::make('name'),
                Checkbox::make('cart_enabled')->default(false),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['logo_url'] = url('images/'.$data['logo_url']);

        $data['items'] = Menu::firstWhere('name', $data['name'])->items;

        return $data;
    }
}
