<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;

class TextBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('text-block')
            ->label('Blocco di Testo')
            ->schema([
                RichEditor::make('content')
                    ->label('Contenuto')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'orderedList',
                        'unorderedList',
                        'h2',
                        'h3',
                        'alignLeft',
                        'alignCenter',
                        'alignRight',
                        'source',
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.text-block');
    }
} 