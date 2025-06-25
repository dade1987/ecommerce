<?php

namespace App\Filament\Fabricator\PageBlocks;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                RichEditor::make('text_one')->label('Testo 1')->required(),
                Toggle::make('show_button_one')->label('Mostra pulsante 1')->default(true),

                // Seconda riga
                CuratorPicker::make('image_two')->label('Immagine 2')->required(),
                TextInput::make('title_two')->label('Titolo 2')->required(),
                RichEditor::make('text_two')->label('Testo 2')->required(),
                Toggle::make('show_button_two')->label('Mostra pulsante 2')->default(true),

                // Terza riga
                CuratorPicker::make('image_three')->label('Immagine 3')->required(),
                TextInput::make('title_three')->label('Titolo 3')->required(),
                RichEditor::make('text_three')->label('Testo 3')->required(),
                Toggle::make('show_button_three')->label('Mostra pulsante 3')->default(true),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
} 