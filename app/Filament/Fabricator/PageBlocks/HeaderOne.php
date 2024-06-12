<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use phpDocumentor\Reflection\Types\Boolean;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeaderOne extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('header-one')
            ->schema([
                Checkbox::make('logo_border')->default(false),
                TextInput::make('logo_url'),
                TextInput::make('text_one'),
                TextInput::make('link_one'),
                TextInput::make('text_two'),
                TextInput::make('link_two'),
                TextInput::make('text_three'),
                TextInput::make('link_three'),
                TextInput::make('text_four'),
                TextInput::make('link_four'),
                TextInput::make('text_five'),
                TextInput::make('link_five'),
                TextInput::make('text_six'),
                TextInput::make('link_six'),
                TextInput::make('text_seven'),
                TextInput::make('link_seven'),
                TextInput::make('text_eight'),
                TextInput::make('link_eight'),
                TextInput::make('text_nine'),
                TextInput::make('link_nine'),

                Checkbox::make('cart_enabled')->default(false),

            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['logo_url'] = url('images/' . $data['logo_url']);
        return $data;
    }
}
