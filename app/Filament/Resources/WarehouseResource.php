<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
public static function getNavigationGroup(): string
    {
        return __('filament-logistics.Logistica');
    }

public static function getModelLabel(): string
    {
        return __('filament-logistics.Magazzino');
    }

public static function getPluralModelLabel(): string
    {
        return __('filament-logistics.Magazzini');
    }
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament-logistics.Nome'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\Select::make('type')
                    ->label(__('filament-logistics.Tipo'))
                    ->required()
                    ->options([
                        'fornitore' => __('filament-logistics.Fornitore'),
                        'magazzino' => __('filament-logistics.Magazzino (option)'),
                        'negozio' => __('filament-logistics.Negozio'),
                    ])
                    ->columnSpan(1),
                Forms\Components\Toggle::make('is_final_destination')
                    ->label(__('filament-logistics.È Destinazione Finale?'))
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-logistics.Nome'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label(__('filament-logistics.Tipo'))
                    ->colors([
                        'primary' => 'fornitore',
                        'success' => 'magazzino',
                        'warning' => 'negozio',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('incomingMovements_count')
                    ->label(__('filament-logistics.Movimenti in Entrata'))
                    ->sortable()
                    ->default(0),

                Tables\Columns\TextColumn::make('outgoingMovements_count')
                    ->label(__('filament-logistics.Movimenti in Uscita'))
                    ->sortable()
                    ->default(0),

                
                Tables\Columns\ToggleColumn::make('is_final_destination')
                    ->label(__('filament-logistics.Destinazione Finale')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-logistics.Creato il'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('filament-logistics.Tipo'))
                    ->options([
                        'fornitore' => __('filament-logistics.Fornitore'),
                        'magazzino' => __('filament-logistics.Magazzino (option)'),
                        'negozio' => __('filament-logistics.Negozio'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
