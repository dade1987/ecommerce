<?php

namespace App\Filament\Fabricator\PageBlocks;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class FeatureTwo extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('feature-two')
            ->label('Feature Two - 3 Immagini alternate')
            ->schema([
                TextInput::make('title')->label('Titolo Principale')->required(),
                Textarea::make('subtitle')->label('Sottotitolo Principale'),

                // Prima riga
                CuratorPicker::make('image_one')->label('Immagine 1')->required(),
                TextInput::make('title_one')->label('Titolo 1')->required(),
                Textarea::make('text_one')->label('Testo 1')->required(),

                // Seconda riga
                CuratorPicker::make('image_two')->label('Immagine 2')->required(),
                TextInput::make('title_two')->label('Titolo 2')->required(),
                Textarea::make('text_two')->label('Testo 2')->required(),

                // Terza riga
                CuratorPicker::make('image_three')->label('Immagine 3')->required(),
                TextInput::make('title_three')->label('Titolo 3')->required(),
                Textarea::make('text_three')->label('Testo 3')->required(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
} 