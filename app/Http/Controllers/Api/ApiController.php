<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($container0, ?string $item0 = null, ?string $container1 = null, ?string $item1 = null, ?string $container2 = null, ?string $item2 = null)
    {

        $params = [
            'container0' => $container0,
            'item0' => $item0,
            'container1' => $container1,
            'item1' => $item1,
            'container2' => $container2,
            'item2' => $item2,
        ];
      

        $container = null;
        $index = 0;
        $before_key = '';
        $parent = null;
        foreach ($params as $key => $param) {
            //dd([$key,$param]);
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
                    $model = Str::singular(Str::title($param));
                    $model_with_ns = 'App\\Models\\' . $model;

                    $controller_class = 'App\\Http\\Controllers\\' . $model . 'Controller';
                    $container = app($controller_class)->index();

                    if (!$container instanceof Collection) {
                        $container = collect(new $model_with_ns);
                    }
                }
           
            }

            dd('qui');

            $before_key = $key;
            $index++;
        }

        dd($container);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        //
    }
}
