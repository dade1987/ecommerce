<?php

namespace App\Filament\Resources\ProductTwinResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryMovements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('movement_type')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('movement_type')
            ->columns([
                Tables\Columns\TextColumn::make('movement_type')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('fromWarehouse.name')
                    ->label('Da')
                    ->default('-')
                    ->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('toWarehouse.name')
                    ->label('A')
                    ->default('-')
                    ->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('distance_km')
                    ->label('Distanza (km)'),
                Tables\Columns\TextColumn::make('transport_mode')
                    ->label('Mezzo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
