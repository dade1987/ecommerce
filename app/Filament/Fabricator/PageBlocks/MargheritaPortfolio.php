<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class MargheritaPortfolio extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('margherita-portfolio');
    }

    public static function getBlockView(): string
    {
        return 'components.filament-fabricator.page-blocks.margherita-full-index';
    }
} 