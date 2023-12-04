<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class IntroLogo extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('intro-logo')
            ->schema([
                TextInput::make('logo_url'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['logo_url'] = url('images/'.$data['logo_url']);

        return $data;
    }
}