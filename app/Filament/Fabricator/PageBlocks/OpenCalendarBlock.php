<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class OpenCalendarBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('open-calendar-block')
            ->label('Pulsante Apri Calendario')
            ->schema([
                TextInput::make('text')
                    ->label('Testo del pulsante')
                    ->default('Prenota una Call')
                    ->required(),
                Select::make('style')
                    ->label('Stile del pulsante')
                    ->options([
                        'primary' => 'Primario (Colorato)',
                        'secondary' => 'Secondario (Grigio)',
                    ])
                    ->default('primary')
                    ->required(),
                Select::make('align')
                    ->label('Allineamento')
                    ->options([
                        'left' => 'Sinistra',
                        'center' => 'Centro',
                        'right' => 'Destra',
                    ])
                    ->default('center')
                    ->required(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.filament-fabricator.page-blocks.open-calendar-block');
    }
} 