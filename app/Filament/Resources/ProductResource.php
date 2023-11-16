<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductMorphResource;
use App\Filament\Resources\ProductResource\Pages;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use App\Filament\Resources\ProductResource\RelationManagers;
use SevendaysDigital\FilamentNestedResources\NestedResource;
use App\Filament\Resources\ProductResource\RelationManagers\ComponentsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariationsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\SubproductsRelationManager;

class ProductResource extends NestedResource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getParent(): string
    {
        return ProductMorphResource::class;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                    //->live()
                   // ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required(),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('weight')->default(0)->required(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('€'),
                CuratorPicker::make('featured_image_id')
                    ->relationship('featuredImage', 'id')
                    ->imageResizeTargetWidth(10)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight'),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                CuratorColumn::make('featured_image')
                    ->size(40),
              
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->reorderable('order_column')
            ->defaultSort('order_column');
    }

    public static function getRelations(): array
    {
        return [
            SubproductsRelationManager::class,
            //VariationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
