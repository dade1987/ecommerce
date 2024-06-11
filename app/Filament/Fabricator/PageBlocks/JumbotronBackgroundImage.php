<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class JumbotronBackgroundImage extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('jumbotron-background-image')
            ->schema([
                TextInput::make('background_image_url'),
                TextInput::make('title'),
                TextInput::make('text'),
                TextInput::make('buttonText'),
                TextInput::make('buttonLink'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['background_image_url'] = url('images/'.$data['background_image_url']);

        return $data;
    }
}
