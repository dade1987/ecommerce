<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class PortfolioStatsBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('portfolio-stats-block')
            ->schema([
                Repeater::make('stats')
                    ->label('Statistiche')
                    ->schema([
                        TextInput::make('icon')->label('Classe Icona (es. bi bi-globe)')->required(),
                        TextInput::make('icon_color_class')->label('Classe Colore Icona (es. text-primary)')->required(),
                        TextInput::make('number')->label('Numero')->numeric()->required(),
                        TextInput::make('number_color_class')->label('Classe Colore Numero (es. text-primary)')->required(),
                        TextInput::make('label')->label('Etichetta')->required(),
                    ])->grid(2)
            ]);
    }

    public static function getLayout(): string
    {
        return 'portfolio-stats-block';
    }
} 