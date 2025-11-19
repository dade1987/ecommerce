<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Illuminate\Contracts\View\View;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class VoiceTranslator extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('voice-translator')
            ->label('Voice Translator AI')
            ->schema([
                // Nessun campo configurabile per ora: il blocco è solo la UI del traduttore.
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.voice-translator');
    }
}
