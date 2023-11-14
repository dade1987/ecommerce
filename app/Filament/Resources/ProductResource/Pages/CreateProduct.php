<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use SevendaysDigital\FilamentNestedResources\ResourcePages\NestedPage;

class CreateProduct extends CreateRecord
{
    //use NestedPage;
    protected static string $resource = ProductResource::class;
}
