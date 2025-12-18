<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\View\View;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class CavalliniService extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('cavalliniservice')
            ->label('CavalliniService (EnjoyTalk 3D)')
            ->schema([
                TextInput::make('team_slug')
                    ->label('Team Slug')
                    ->required()
                    ->default('cavalliniservice')
                    ->helperText('Lo slug del team da usare per le richieste API'),
                TextInput::make('calendly_url')
                    ->label('Calendly URL')
                    ->url()
                    ->helperText('URL del calendario Calendly per prenotare appuntamenti'),
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
