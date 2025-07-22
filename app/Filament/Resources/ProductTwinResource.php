<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTwinResource\Pages;
use App\Filament\Resources\ProductTwinResource\RelationManagers;
use App\Models\ProductTwin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductTwinResource extends Resource
{
    protected static ?string $model = ProductTwin::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    
public static function getNavigationGroup(): string
{
    return __('filament-traceability.Tracciabilità');
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form definition here
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('internalProduct.name')
                    ->label(__('filament-traceability.Prodotto'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('lifecycle_status')
                    ->label(__('filament-traceability.Stato'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_production' => 'gray',
                        'in_stock' => 'success',
                        'in_transit' => 'warning',
                        'in_use' => 'info',
                        'recycled' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("filament-traceability." . $state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('co2_emissions_total')
                    ->label(__('filament-traceability.CO2 Totale (kg)'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-traceability.Data Creazione'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryMovementsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductTwins::route('/'),
            'view' => Pages\ViewProductTwin::route('/{record}'),
        ];
    }    
}
