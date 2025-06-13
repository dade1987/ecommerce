<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Builder\Block;

class LogoHeader extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('logo-header')
            ->label('Header con Logo Centrato')
            ->schema([
                TextInput::make('logo_url')
                    ->label('Nome file logo')
                    ->required(),
                
                Checkbox::make('logo_border')
                    ->label('Mostra bordo blu')
                    ->default(false),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['logoUrl'] = url('images/'.$data['logo_url']);
        $data['logoBorder'] = $data['logo_border'];

        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.logo-header');
    }
} 