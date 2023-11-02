<?php

namespace App\Filament\Fabricator\PageBlocks;

use Illuminate\Support\Facades\Route;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class BreadcrumbsBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('breadcrumbs')
            ->schema([
                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        $params = Route::current()->parameters();

        $breadcrumbs = collect(Breadcrumbs::generate(Route::currentRouteName(), $params))->pluck('title', 'url')->toArray();

        return  ['breadcrumbs' => $breadcrumbs];
    }
}
