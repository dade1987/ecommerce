<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Filament\Forms\Components\Checkbox;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Database\Eloquent\Collection;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class ModelListBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('model-list')
            ->schema([
                Checkbox::make('second_button')
            ]);
    }


    public static function mutateData(array $data): array
    {
        /*if (!Auth::check()) {
            abort(403);
            //return redirect()->route('login');
        }*/

        $params = Route::current()->parameters();

        $user = Auth::user();

        $container = null;
        $index = 0;
        $before_key = '';
        $parent = null;
        foreach ($params as $key => $param) {
            if ($param != null) {
                if (Str::startsWith($key, 'item')) {
                    $row_class = 'App\\Models\\' . Str::singular(Str::title($params[$before_key]));
                    $parent = $row_class::findOrFail($param);
                } else {



                    if ($parent != null) {
                        $class_name = class_basename(Str::lower($parent->$param()->getModel()::class));



                        /*if (!$user->can('view_any_' . Str::singular($class_name))) {
                            abort(403);
                        }*/

                        $container = $parent->$param;
                    } else {


                        //forse è meglio usare il controller perchè è il controller che dovrebbe fare da interfaccia tra modello e applicativo

                        $model = Str::singular(Str::title($param));
                        $model_with_ns = 'App\\Models\\' . $model;

                        $controller_class = 'App\\Http\\Controllers\\' . $model . 'Controller';
                        $container = app($controller_class)->index();

                        if (!$container instanceof Collection) {
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
            'second_button' => $data['second_button'] ?? false
        ];
    }
}
