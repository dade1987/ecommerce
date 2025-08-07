<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeroPortfolioBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('hero-portfolio-block')
            ->schema([
                TextInput::make('heading')
                    ->label('Titolo')
                    ->required(),
                Textarea::make('subheading')
                    ->label('Sottotitolo')
                    ->required(),
                Repeater::make('badges')
                    ->label('Badges')
                    ->schema([
                        TextInput::make('text')->label('Testo Badge')->required(),
                    ])
                    ->grid(3),
            ]);
    }

    public static function getLayout(): string
    {
        return 'hero-portfolio-block';
    }
} 