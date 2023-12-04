<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Http\Controllers\PageController as FabricatorPageController;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($container0, ?string $item0 = null, ?string $container1 = null, ?string $item1 = null, ?string $container2 = null, ?string $item2 = null)
    {
        
        $value = $container0;

        if (isset($container1)) {
            $value = $container1;
        }

        if (isset($container2)) {
            $value = $container2;
        }

        $pageModel = FilamentFabricator::getPageModel();

        $pageUrls = FilamentFabricator::getPageUrls();

        $value = Str::start($value, '/');


        $pageId = array_search($value, $pageUrls);

        //dd(['pageUrls'=>$pageUrls,'value'=>$value, 'pageId'=>$pageId,'pageModel'=>$pageModel]);

        //qui potrei usare le policy tipo 
        $page = $pageModel::query()
            ->where('id', $pageId)
            ->firstOrFail();
       
        $view = app(FabricatorPageController::class)($page);

        return $view;
    }
}
