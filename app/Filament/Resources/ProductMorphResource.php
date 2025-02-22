<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ProductMorphResource\Pages;
use App\Filament\Resources\ProductMorphResource\RelationManagers;
use App\Models\Category;
use App\Models\ProductMorph;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SevendaysDigital\FilamentNestedResources\Columns\ChildResourceLink;
use SevendaysDigital\FilamentNestedResources\NestedResource;

class ProductMorphResource extends Resource
{
    protected static ?string $model = ProductMorph::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /*Forms\Components\TextInput::make('model_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('model_id')
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('option')
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->maxLength(255),*/
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //ChildResourceLink::make(ProductResource::class),
                /*Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('model_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('option')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),*/

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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductMorphs::route('/'),
            'create' => Pages\CreateProductMorph::route('/create'),
            'edit' => Pages\EditProductMorph::route('/{record}/edit'),
        ];
    }
}
