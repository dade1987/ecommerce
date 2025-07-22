<?php

namespace App\Filament\Pages;

use App\Models\InternalProduct;
use App\Models\InventoryMovement;
use App\Models\ProductTwin;
use App\Models\Warehouse;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\DB;

/**
 * Pagina personalizzata per la visualizzazione delle giacenze di magazzino
 * 
 * Questa pagina mostra una tabella aggregata delle quantità correnti per ogni 
 * prodotto interno e magazzino, basata sui ProductTwin presenti.
 */
class InventoryOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Giacenze Magazzino';
    protected static ?string $title = 'Giacenze Magazzino';
    protected static ?string $navigationGroup = 'Logistica';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.inventory-overview';

    /**
     * Tabella aggregata delle giacenze per prodotto e magazzino
     * 
     * Questa è una vista aggregata calcolata in tempo reale che mostra:
     * - Il nome del prodotto interno
     * - Il nome del magazzino
     * - La quantità corrente (basata sui ProductTwin)
     * - Le emissioni CO2 totali
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductTwin::query()
                    ->select([
                        DB::raw('CONCAT(internal_products.id, "-", logistic_warehouses.id) as id'),
                        'internal_products.id as product_id',
                        'internal_products.name as product_name',
                        'internal_products.code as product_code',
                        'internal_products.unit_of_measure',
                        'logistic_warehouses.id as warehouse_id',
                        'logistic_warehouses.name as warehouse_name',
                        'logistic_warehouses.type as warehouse_type',
                        DB::raw('COUNT(product_twins.id) as current_stock'),
                        DB::raw('AVG(product_twins.co2_emissions_total) as avg_co2_per_unit'),
                        DB::raw('SUM(product_twins.co2_emissions_total) as total_co2_emissions'),
                        DB::raw('GROUP_CONCAT(DISTINCT product_twins.lifecycle_status) as lifecycle_statuses'),
                        // Movimenti in entrata (carico)
                        DB::raw('(
                            SELECT COALESCE(SUM(quantity),0) FROM logistic_inventory_movements
                            WHERE movement_type = "carico"
                              AND to_warehouse_id = logistic_warehouses.id
                              AND internal_product_id = internal_products.id
                        ) as incoming_movements'),
                        // Movimenti in uscita (scarico)
                        DB::raw('(
                            SELECT COALESCE(SUM(quantity),0) FROM logistic_inventory_movements
                            WHERE movement_type = "scarico"
                              AND from_warehouse_id = logistic_warehouses.id
                              AND internal_product_id = internal_products.id
                        ) as outgoing_movements')
                    ])
                    ->join('internal_products', 'product_twins.internal_product_id', '=', 'internal_products.id')
                    ->join('logistic_warehouses', 'product_twins.current_warehouse_id', '=', 'logistic_warehouses.id')
                    ->groupBy([
                        'internal_products.id', 'internal_products.name', 'internal_products.code', 
                        'internal_products.unit_of_measure', 'logistic_warehouses.id', 'logistic_warehouses.name', 'logistic_warehouses.type'
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Prodotto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_code')
                    ->label('Codice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('warehouse_name')
                    ->label('Magazzino')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Quantità (Digital Twin)')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state): string => match (true) {
                        $state == 0 => 'warning',
                        $state > 0 && $state <= 5 => 'danger',
                        $state > 5 && $state <= 20 => 'warning',
                        $state > 20 => 'success',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('U.M.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('avg_co2_per_unit')
                    ->label('CO2 Media/Unità')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->sortable()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('total_co2_emissions')
                    ->label('CO2 Totale')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->sortable()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('warehouse_type')
                    ->label('Tipo Magazzino')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fornitore' => 'primary',
                        'magazzino' => 'success',
                        'negozio' => 'warning',
                        default => 'gray'
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('lifecycle_statuses')
                    ->label('Stati Lifecycle')
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'in_production' => 'info',
                        'in_stock' => 'success',
                        'in_transit' => 'warning',
                        'in_use' => 'primary',
                        default => 'gray'
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_type')
                    ->label('Tipo Magazzino')
                    ->options([
                        'fornitore' => 'Fornitore',
                        'magazzino' => 'Magazzino',
                        'negozio' => 'Negozio',
                    ]),

                Tables\Filters\SelectFilter::make('lifecycle_status')
                    ->label('Stato Lifecycle')
                    ->options([
                        'in_production' => 'In Produzione',
                        'in_stock' => 'In Magazzino',
                        'in_transit' => 'In Transito',
                        'in_use' => 'In Uso',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            return $query->where('product_twins.lifecycle_status', $data['value']);
                        }
                        return $query;
                    }),

                Tables\Filters\TernaryFilter::make('has_stock')
                    ->label('Con Giacenza')
                    ->placeholder('Tutti')
                    ->trueLabel('Solo con giacenza')
                    ->falseLabel('Solo senza giacenza')
                    ->queries(
                        true: fn ($query) => $query->having('current_stock', '>', 0),
                        false: fn ($query) => $query->having('current_stock', '<=', 0),
                    ),
            ])
            ->defaultSort('current_stock', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Statistiche rapide per la pagina
     */
    protected function getHeaderStats(): array
    {
        $totalProducts = InternalProduct::count();
        $totalWarehouses = Warehouse::count();
        $totalTwins = ProductTwin::count();
        $totalCo2 = ProductTwin::sum('co2_emissions_total') ?? 0;

        $stockByWarehouse = ProductTwin::select('current_warehouse_id')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('current_warehouse_id')
            ->get();

        return [
            'total_products' => $totalProducts,
            'total_warehouses' => $totalWarehouses,
            'total_twins' => $totalTwins,
            'total_co2' => round($totalCo2, 2),
            'warehouses_with_stock' => $stockByWarehouse->count(),
        ];
    }
} 