<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Traits\HasParentResource;
use App\Filament\Resources\ProductResource;

class EditProduct extends EditRecord
{

    use HasParentResource;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getParentResource()::getUrl('products.index', [
            'parent' => $this->parent,
        ]);
    }
 
    protected function configureDeleteAction(Actions\DeleteAction $action): void
    {
        $resource = static::getResource();
 
        $action->authorize($resource::canDelete($this->getRecord()))
            ->successRedirectUrl(static::getParentResource()::getUrl('products.index', [
                'parent' => $this->parent,
            ]));
    }
}
