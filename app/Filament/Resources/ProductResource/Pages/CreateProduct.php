<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use App\Filament\Resources\CategoryResource;
use App\Filament\Traits\HasParentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProductResource;

class CreateProduct extends CreateRecord
{
    use HasParentResource;
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getParentResource()::getUrl('products.index', [
            'parent' => $this->parent,
        ]);
    }
 
    // This can be moved to Trait, but we are keeping it here
    //   to avoid confusion in case you mutate the data yourself
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the parent relationship key to the parent resource's ID.
        $data[$this->getParentRelationshipKey()] = $this->parent->id;
 
        return $data;
    }
}
