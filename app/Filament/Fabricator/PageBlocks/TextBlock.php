<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\ColorPicker;

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
                    ])
                    ->extraInputAttributes(['style' => 'min-height: 250px;'])
                    ->columnSpanFull()
                    ->extraAlpineAttributes([
                        'x-data' => '{ color: \'#000000\' }',
                    ])
                    ->suffixAction(
                        Action::make('color')
                            ->icon('heroicon-o-paint-brush')
                            ->label(__('Colore Testo'))
                            ->modalHeading(__('Scegli un colore per il testo'))
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalContent(view('forms.components.color-picker-content'))
                            ->modalWidth('sm')
                    ),
            ]);
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.text-block');
    }
} 