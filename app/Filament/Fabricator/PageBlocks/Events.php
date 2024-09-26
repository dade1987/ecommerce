<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Event;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Events extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('events')
            ->schema([
                TextInput::make('logo_url'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['logo_url'] = url('images/'.$data['logo_url']);

        $data['rows'] = Event::get();

        return $data;
    }
}
