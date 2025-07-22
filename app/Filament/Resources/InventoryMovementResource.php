<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InternalProduct;
use App\Models\InventoryMovement;
use App\Models\ProductTwin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
public static function getNavigationGroup(): string
    {
        return __('filament-logistics.Logistica');
    }

public static function getModelLabel(): string
    {
        return __('filament-logistics.Movimento di Inventario');
    }

public static function getPluralModelLabel(): string
    {
        return __('filament-logistics.Movimenti di Inventario');
    }
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('movement_type')
                    ->label(__('filament-logistics.Tipo Movimento'))
                    ->required()
                    ->options([
                        'carico' => __('filament-logistics.Carico'),
                        'scarico' => __('filament-logistics.Scarico'),
                        'trasferimento' => __('filament-logistics.Trasferimento'),
                        'reso' => __('filament-logistics.Reso'),
                    ])
                    ->live()
                    ->columnSpanFull(),

                // Fields for UNLOAD and TRANSFER
                Forms\Components\Select::make('from_warehouse_id')
                    ->label(__('filament-logistics.Magazzino Origine'))
                    ->relationship('fromWarehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->visible(fn (Get $get) => in_array($get('movement_type'), ['scarico', 'trasferimento']))
                    ->required(fn (Get $get) => in_array($get('movement_type'), ['scarico', 'trasferimento'])),

                Forms\Components\Select::make('internal_product_id_for_twins')
                    ->label(__('filament-logistics.Prodotto'))
                    ->options(InternalProduct::query()->pluck('name', 'id'))
                    ->live()
                    ->visible(fn (Get $get) => in_array($get('movement_type'), ['scarico', 'trasferimento']))
                    ->dehydrated(false),

                Forms\Components\CheckboxList::make('product_twins')
                    ->label(__('filament-logistics.Prodotti Specifici (Digital Twin)'))
                    ->options(function (Get $get): Collection {
                        $warehouseId = $get('from_warehouse_id');
                        $internalProductId = $get('internal_product_id_for_twins');
                        if (!$warehouseId || !$internalProductId) {
                            return collect();
                        }
                        return ProductTwin::query()
                            ->where('current_warehouse_id', $warehouseId)
                            ->where('internal_product_id', $internalProductId)
                            ->pluck('uuid', 'id');
                    })
                    ->visible(fn (Get $get) => in_array($get('movement_type'), ['scarico', 'trasferimento']))
                    ->required(fn (Get $get) => in_array($get('movement_type'), ['scarico', 'trasferimento']))
                    ->columns(2),

                // Fields for LOAD
                Forms\Components\Select::make('internal_product_id')
                    ->label(__('filament-logistics.Prodotto'))
                    ->relationship('internalProduct', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get) => $get('movement_type') === 'carico')
                    ->required(fn (Get $get) => $get('movement_type') === 'carico'),
                
                Forms\Components\TextInput::make('quantity')
                    ->label(__('filament-logistics.Quantità'))
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn (Get $get) => $get('movement_type') === 'carico')
                    ->required(fn (Get $get) => $get('movement_type') === 'carico'),

                // Fields for LOAD and TRANSFER
                Forms\Components\Select::make('to_warehouse_id')
                    ->label(__('filament-logistics.Magazzino Destinazione'))
                    ->relationship('toWarehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get) => in_array($get('movement_type'), ['carico', 'trasferimento']))
                    ->required(fn (Get $get) => in_array($get('movement_type'), ['carico', 'trasferimento'])),

                Forms\Components\TextInput::make('distance_km')
                    ->label(__('filament-logistics.Distanza (km)'))
                    ->numeric(),

                Forms\Components\Select::make('transport_mode')
                    ->label(__('filament-logistics.Mezzo di Trasporto'))
                    ->options([
                        'camion' => __('filament-logistics.Camion'),
                        'treno' => __('filament-logistics.Treno'),
                        'aereo' => __('filament-logistics.Aereo'),
                    ]),

                Forms\Components\Textarea::make('note')
                    ->label(__('filament-logistics.Note'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('movement_type')
                    ->label(__('filament-logistics.Tipo'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'carico' => 'success',
                        'scarico' => 'danger',
                        'trasferimento' => 'warning',
                        'reso' => 'info',
                    }),
                Tables\Columns\TextColumn::make('internalProduct.name')->label(__('filament-logistics.Prodotto'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fromWarehouse.name')->label(__('filament-logistics.Da'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('toWarehouse.name')->label(__('filament-logistics.A'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('quantity')->label(__('filament-logistics.Quantità (Carico)'))->numeric()->sortable(),
                Tables\Columns\TextColumn::make('product_twins_count')->counts('productTwins')->label(__('filament-logistics.Unità Mosse'))->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('filament-logistics.Data'))->dateTime()->sortable(),
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
            'index' => Pages\ListInventoryMovements::route('/'),
            'create' => Pages\CreateInventoryMovement::route('/create'),
            'edit' => Pages\EditInventoryMovement::route('/{record}/edit'),
        ];
    }
}
