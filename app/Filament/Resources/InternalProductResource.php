<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\InternalProduct;
use Filament\Resources\Resource;
use App\Filament\Resources\InternalProductResource\Pages\EditInternalProduct;
use App\Filament\Resources\InternalProductResource\Pages\ListInternalProducts;
use App\Filament\Resources\InternalProductResource\Pages\CreateInternalProduct;

class InternalProductResource extends Resource
{
    protected static ?string $model = InternalProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 0;

    public static function getNavigationGroup(): string
    {
        return __('filament-production.Produzione');
    }

    public static function getModelLabel(): string
    {
        return __('internal_product.Prodotto Interno');
    }

    public static function getPluralModelLabel(): string
    {
        return __('internal_product.Prodotti Interni');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label(__('internal_product.Nome'))->required(),
                Forms\Components\TextInput::make('code')->label(__('internal_product.Codice'))->required()->unique(),
                Forms\Components\Textarea::make('description')->label(__('internal_product.Descrizione')),
                Forms\Components\TextInput::make('unit_of_measure')->label(__('internal_product.Unita di Misura'))->required(),
                Forms\Components\TextInput::make('weight')->label(__('internal_product.Peso (kg)'))->numeric(),
                Forms\Components\Textarea::make('materials')->label(__('internal_product.Materiali'))->rows(2),
                Forms\Components\TextInput::make('emission_factor')->label(__('internal_product.Fattore Emissione'))->numeric(),
                Forms\Components\TextInput::make('co2_avoided')->label(__('internal_product.CO2 Evitata'))->numeric(),
                Forms\Components\TextInput::make('expected_lifespan_days')->label(__('internal_product.Durata Attesa (giorni)'))->numeric(),
                Forms\Components\Toggle::make('is_recyclable')->label(__('internal_product.Riciclabile')),
                Forms\Components\Textarea::make('metadata')->label(__('internal_product.Metadata'))->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('internal_product.Nome'))->searchable(),
                Tables\Columns\TextColumn::make('code')->label(__('internal_product.Codice'))->searchable(),
                Tables\Columns\TextColumn::make('unit_of_measure')->label(__('internal_product.Unita di Misura')),
                Tables\Columns\TextColumn::make('weight')->label(__('internal_product.Peso (kg)')),
                Tables\Columns\TextColumn::make('emission_factor')->label(__('internal_product.Fattore Emissione')),
                Tables\Columns\TextColumn::make('co2_avoided')->label(__('internal_product.CO2 Evitata')),
                Tables\Columns\TextColumn::make('expected_lifespan_days')->label(__('internal_product.Durata Attesa (giorni)')),
                Tables\Columns\IconColumn::make('is_recyclable')->label(__('internal_product.Riciclabile'))->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label(__('internal_product.Creato il'))->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Qui puoi aggiungere relation manager se servono
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInternalProducts::route('/'),
            'create' => CreateInternalProduct::route('/create'),
            'edit' => EditInternalProduct::route('/{record}/edit'),
        ];
    }
} 