<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;

class EnjoyHen extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('enjoy-hey')
            ->label('EnjoyHen AI (HeyGen)')
            ->schema([
                // Nessun campo per ora
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.enjoy-hey');
    }
}
