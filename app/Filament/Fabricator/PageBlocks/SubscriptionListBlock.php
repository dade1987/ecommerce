<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class SubscriptionListBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('subscription-list')
            ->schema([
                TextInput::make('title'),
                TextInput::make('subtitle'),
                TextInput::make('first_period'),
                TextInput::make('second_period'),
                TextInput::make('button_text'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}