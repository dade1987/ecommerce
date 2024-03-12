<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Testimonial extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('testimonial')
            ->schema([
                Repeater::make('testimonials')
                    ->schema([
                        TextInput::make('logo'),
                        TextInput::make('title'),
                        TextInput::make('subtitle'),
                        TextInput::make('text'),
                    ])


            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['logo'] = url('images/' . $data['logo']);
        return $data;
    }
}
