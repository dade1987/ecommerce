<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class ModelListBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('model-list')
            ->schema([
                //CuratorPicker::make('header_image')
            ]);
    }

    public static function mutateData(array $data): array
    {
        $params = Route::current()->parameters();

        $index = 0;
        $before_key = '';
        $parent = null;
        foreach ($params as $key => $param) {

            if (Str::startsWith($key, 'item')) {
                $row_class = 'App\\Models\\' . Str::singular(Str::title($params[$before_key]));
                $parent = $row_class::findOrFail($param);
            } else {
                if ($parent != null) {
                    $container = $parent->$param;
                } else {
                    $row_class = 'App\\Models\\' . Str::singular(Str::title($param));
                    $container = $row_class::get();
                }
            }

            $before_key = $key;
            $index++;
        }

        return [

            'rows' => $container
        ];
    }
}
