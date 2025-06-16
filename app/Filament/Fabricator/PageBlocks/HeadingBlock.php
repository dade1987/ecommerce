<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeadingBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('heading')
            ->schema([
                TextInput::make('title')
                    ->label('Titolo principale')
                    ->placeholder('Es: Formificio STF s.r.l.'),
                TextInput::make('subtitle')
                    ->label('Sottotitolo')
                    ->placeholder('Es: Modulo di inserimento ordini'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}