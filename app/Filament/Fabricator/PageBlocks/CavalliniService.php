<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Illuminate\Contracts\View\View;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class CavalliniService extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('cavalliniservice')
            ->label('CavalliniService (EnjoyTalk 3D)')
            ->schema([
                // Nessun campo per ora: blocco “plug & play”
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.cavalliniservice');
    }
}

