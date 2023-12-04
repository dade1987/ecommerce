<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ContactFormWithMap extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('contact-form-with-map')
            ->schema([
                TextInput::make('company_email'),
                TextInput::make('company_phone'),
                TextInput::make('maps_query'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}