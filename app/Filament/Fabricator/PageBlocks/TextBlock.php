<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;

class TextBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('text-block')
            ->label('Blocco di Testo')
            ->schema([
                Grid::make(2)->schema([
                    ColorPicker::make('textColor')
                        ->label('Colore del Testo')
                        ->reactive()
                        ->afterStateUpdated(static function ($state, $livewire, $get, $set) {
                            // Questo serve per far aggiornare il componente, ma la logica è in Alpine
                        })
                        ->extraAttributes([
                            'x-on:change' => '
                                const richEditor = document.getElementById(\'content\').closest(\'.fi-fo-rich-editor\').__x;
                                richEditor.execute(
                                    (view) => {
                                        view.dispatch(view.state.tr.setSelection(view.state.selection))
                                    },
                                    (view) => {
                                        view.chain().focus().setColor($event.target.value).run()
                                    }
                                );
                            ',
                        ]),
                ]),
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
                    ])
                    ->extraInputAttributes(['style' => 'min-height: 250px;', 'id' => 'content'])
                    ->columnSpanFull(),
            ]);
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.text-block');
    }
} 