<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use App\Models\LogisticProduct;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Logistica';
    protected static ?string $modelLabel = 'Movimento Inventario';
    protected static ?string $pluralModelLabel = 'Movimenti Inventario';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('logistic_product_id')
                    ->label('Prodotto Logistico')
                    ->required()
                    ->relationship('logisticProduct', 'nome')
                    ->searchable()
                    ->preload()
                    ->columnSpan(2),

                Forms\Components\Select::make('movement_type')
                    ->label('Tipo Movimento')
                    ->required()
                    ->options([
                        'carico' => 'Carico',
                        'scarico' => 'Scarico',
                        'trasferimento' => 'Trasferimento',
                    ])
                    ->live()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('quantita')
                    ->label('Quantità')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->columnSpan(1),

                Forms\Components\Select::make('from_warehouse_id')
                    ->label('Magazzino Origine')
                    ->relationship('fromWarehouse', 'nome')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get) => in_array($get('movement_type'), ['scarico', 'trasferimento']))
                    ->required(fn (Forms\Get $get) => in_array($get('movement_type'), ['scarico', 'trasferimento']))
                    ->columnSpan(1),

                Forms\Components\Select::make('to_warehouse_id')
                    ->label('Magazzino Destinazione')
                    ->relationship('toWarehouse', 'nome')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get) => in_array($get('movement_type'), ['carico', 'trasferimento']))
                    ->required(fn (Forms\Get $get) => in_array($get('movement_type'), ['carico', 'trasferimento']))
                    ->columnSpan(1),

                Forms\Components\Textarea::make('note')
                    ->label('Note')
                    ->columnSpanFull()
                    ->rows(3),

                Forms\Components\Section::make('Integrazione Produzione')
                    ->description('Campi per collegamento con il sistema di produzione')
                    ->schema([
                        Forms\Components\TextInput::make('production_order_id')
                            ->label('ID Ordine Produzione')
                            ->numeric()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('origine_automatica')
                            ->label('Origine Automatica')
                            ->helperText('Movimento generato automaticamente dal sistema di produzione')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('logisticProduct.nome')
                    ->label('Prodotto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('movement_type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'carico',
                        'danger' => 'scarico',
                        'warning' => 'trasferimento',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('quantita')
                    ->label('Quantità')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fromWarehouse.nome')
                    ->label('Da Magazzino')
                    ->placeholder('N/A')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('toWarehouse.nome')
                    ->label('A Magazzino')
                    ->placeholder('N/A')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('origine_automatica')
                    ->label('Auto')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('production_order_id')
                    ->label('Ordine Prod.')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->label('Tipo Movimento')
                    ->options([
                        'carico' => 'Carico',
                        'scarico' => 'Scarico',
                        'trasferimento' => 'Trasferimento',
                    ]),

                Tables\Filters\TernaryFilter::make('origine_automatica')
                    ->label('Origine Automatica')
                    ->placeholder('Tutti')
                    ->trueLabel('Automatici')
                    ->falseLabel('Manuali'),

                Tables\Filters\SelectFilter::make('logistic_product_id')
                    ->label('Prodotto')
                    ->relationship('logisticProduct', 'nome')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListInventoryMovements::route('/'),
            'create' => Pages\CreateInventoryMovement::route('/create'),
            'edit' => Pages\EditInventoryMovement::route('/{record}/edit'),
        ];
    }
}
