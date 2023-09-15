<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
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

        $user = Auth::user();

        $index = 0;
        $before_key = '';
        $parent = null;
        foreach ($params as $key => $param) {

            if (Str::startsWith($key, 'item')) {
                $row_class = 'App\\Models\\' . Str::singular(Str::title($params[$before_key]));
                $parent = $row_class::findOrFail($param);
            } else {



                if ($parent != null) {
                    $class_name = class_basename(Str::lower($parent->$param()->getModel()::class));

                    if (!$user->can('view_any_' . Str::singular($class_name))) {
                        abort(403);
                    }

                    $container = $parent->$param;
                } else {

                    if (!$user->can('view_any_' . Str::singular($param))) {
                        abort(403);
                    }
                    /*$row_class = 'App\\Models\\' . Str::singular(Str::title($param));
                    $container = $row_class::get();*/

                    //forse è meglio usare il controller perchè è il controller che dovrebbe fare da interfaccia tra modello e applicativo

                    $controller_class = 'App\\Http\\Controllers\\' . Str::singular(Str::title($param)) . 'Controller';
                    $container = app($controller_class)->index();
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
