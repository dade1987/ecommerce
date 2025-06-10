<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class RestaurantItem extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('restaurant-item')
            ->schema([
                // puoi aggiungere qui eventuali campi custom per il block
            ]);
    }

    public static function mutateData(array $data): array
    {
        $params = Route::current()->parameters();
        $user = Auth::user();
        $container = null;
        $index = 0;
        $before_key = '';
        $parent = null;
        foreach ($params as $key => $param) {
            if ($param != null) {
                if (Str::startsWith($key, 'item')) {
                    $row_class = 'App\\Models\\'.Str::singular(Str::title($params[$before_key]));
                    $parent = $row_class::findOrFail($param);
                } else {
                    if ($parent !== null && method_exists($parent, $param)) {
                        $class_name = class_basename(Str::lower($parent->$param()->getModel()::class));
                        $container = $parent->$param;
                    } else {
                        $model = Str::singular(Str::title($param));
                        $model_with_ns = 'App\\Models\\'.$model;
                        $controller_class = 'App\\Http\\Controllers\\'.$model.'Controller';
                        $container = app($controller_class)->index();
                        if (! $container instanceof Collection) {
                            $container = collect(new $model_with_ns);
                        }
                    }
                }
                $before_key = $key;
                $index++;
            }
        }
        return [
            'rows' => $container,
        ];
    }
} 