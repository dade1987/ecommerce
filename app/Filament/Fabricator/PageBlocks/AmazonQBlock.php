<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\RichEditor;

class AmazonQBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('amazon-q-block')
            ->label('Amazon Q Block')
            ->schema([
                TextInput::make('title')
                    ->label('Titolo')
                    ->required(),
                RichEditor::make('subtitle')
                    ->label('Sottotitolo')
                    ->required(),
                CuratorPicker::make('image')
                    ->label('Immagine')
                    ->required(),
                TextInput::make('button_one_text')
                    ->label('Testo Pulsante 1'),
                TextInput::make('button_one_link')
                    ->label('Link Pulsante 1'),
            ]);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.filament-fabricator.page-blocks.amazon-q-block');
    }
} 