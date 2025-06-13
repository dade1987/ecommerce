<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;

class RedirectBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('redirect-block')
            ->label('Blocco Redirect')
            ->schema([
                TextInput::make('redirect_url')
                    ->label('URL di Redirect')
                    ->required()
                    ->url()
                    ->columnSpanFull(),
            ]);
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.redirect-block');
    }
} 