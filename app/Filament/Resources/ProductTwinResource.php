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
    
    protected static ?string $navigationGroup = 'Tracciabilità';

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
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('UUID copiato'),
                Tables\Columns\TextColumn::make('internalProduct.name')
                    ->label('Prodotto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lifecycle_status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_production' => 'gray',
                        'in_stock' => 'success',
                        'in_transit' => 'warning',
                        'in_use' => 'info',
                        'recycled' => 'primary',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('co2_emissions_total')
                    ->label('CO2 Totale (kg)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Creazione')
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
