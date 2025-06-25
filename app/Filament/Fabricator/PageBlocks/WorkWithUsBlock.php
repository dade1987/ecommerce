<?php

namespace App\Filament\Fabricator\PageBlocks;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class WorkWithUsBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('work-with-us-block')
            ->label('Lavora con Noi')
            ->schema([
                TextInput::make('title')
                    ->label('Titolo')
                    ->required(),
                RichEditor::make('subtitle')
                    ->label('Sottotitolo / Testo')
                    ->required(),
                CuratorPicker::make('image')
                    ->label('Immagine')
                    ->required(),
                RichEditor::make('privacy_policy_text')
                    ->label('Testo Privacy Policy')
                    ->helperText('Testo da visualizzare accanto alla checkbox di consenso.')
                    ->required(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.filament-fabricator.page-blocks.work-with-us-block');
    }
} 