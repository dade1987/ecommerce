<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionLineResource\Pages;
use App\Filament\Resources\ProductionLineResource\RelationManagers;
use App\Filament\Resources\ProductionLineResource\RelationManagers\WorkstationsRelationManager;
use App\Models\ProductionLine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionLineResource extends Resource
{
    protected static ?string $model = ProductionLine::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

public static function getNavigationGroup(): string
    {
        return __('filament-production.Produzione');
    }

public static function getModelLabel(): string
    {
        return __('filament-production.Linea di Produzione');
    }

public static function getPluralModelLabel(): string
    {
        return __('filament-production.Linee di Produzione');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament-production.Nome Linea'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('filament-production.Descrizione'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label(__('filament-production.Stato'))
                    ->options([
                        'active' => __('filament-production.Attiva'),
                        'inactive' => __('filament-production.Inattiva'),
                        'maintenance' => __('filament-production.In Manutenzione'),
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-production.Nome Linea'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament-production.Stato'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'maintenance' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-production.Data Creazione'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            WorkstationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionLines::route('/'),
            'create' => Pages\CreateProductionLine::route('/create'),
            'edit' => Pages\EditProductionLine::route('/{record}/edit'),
        ];
    }
}
