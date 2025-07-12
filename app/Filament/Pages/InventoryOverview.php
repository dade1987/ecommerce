<?php

namespace App\Filament\Pages;

use App\Models\InventoryMovement;
use App\Models\LogisticProduct;
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
 * prodotto logistico e magazzino, calcolata in tempo reale sommando le entrate
 * e sottraendo le uscite.
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
     * - Il nome del prodotto logistico
     * - Il nome del magazzino
     * - La quantità corrente (entrate - uscite)
     * - L'unità di misura
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                LogisticProduct::query()
                    ->select([
                        'logistic_products.id as product_id',
                        'logistic_products.nome as product_name',
                        'logistic_products.unita_misura as unit_of_measure',
                        'logistic_warehouses.id as warehouse_id',
                        'logistic_warehouses.nome as warehouse_name',
                        'logistic_warehouses.tipo as warehouse_type',
                        DB::raw('COALESCE(SUM(entries.quantita), 0) as total_entries'),
                        DB::raw('COALESCE(SUM(exits.quantita), 0) as total_exits'),
                        DB::raw('COALESCE(SUM(entries.quantita), 0) - COALESCE(SUM(exits.quantita), 0) as current_stock')
                    ])
                    ->crossJoin('logistic_warehouses')
                    ->leftJoin('logistic_inventory_movements as entries', function ($join) {
                        $join->on('logistic_products.id', '=', 'entries.logistic_product_id')
                             ->on('logistic_warehouses.id', '=', 'entries.to_warehouse_id');
                    })
                    ->leftJoin('logistic_inventory_movements as exits', function ($join) {
                        $join->on('logistic_products.id', '=', 'exits.logistic_product_id')
                             ->on('logistic_warehouses.id', '=', 'exits.from_warehouse_id');
                    })
                    ->groupBy([
                        'logistic_products.id', 'logistic_products.nome', 'logistic_products.unita_misura',
                        'logistic_warehouses.id', 'logistic_warehouses.nome', 'logistic_warehouses.tipo'
                    ])
                    ->havingRaw('COALESCE(SUM(entries.quantita), 0) > 0 OR COALESCE(SUM(exits.quantita), 0) > 0')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Prodotto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('warehouse_name')
                    ->label('Magazzino')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Giacenza Corrente')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state): string => match (true) {
                        $state < 0 => 'danger',
                        $state == 0 => 'warning',
                        $state > 0 => 'success',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unità di Misura')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_entries')
                    ->label('Tot. Entrate')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_exits')
                    ->label('Tot. Uscite')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_type')
                    ->label('Tipo Magazzino')
                    ->options([
                        'fornitore' => 'Fornitore',
                        'magazzino' => 'Magazzino',
                        'negozio' => 'Negozio',
                    ]),

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
        $stats = DB::table('logistic_products as lp')
            ->crossJoin('logistic_warehouses as lw')
            ->leftJoin('logistic_inventory_movements as entries', function ($join) {
                $join->on('lp.id', '=', 'entries.logistic_product_id')
                     ->on('lw.id', '=', 'entries.to_warehouse_id');
            })
            ->leftJoin('logistic_inventory_movements as exits', function ($join) {
                $join->on('lp.id', '=', 'exits.logistic_product_id')
                     ->on('lw.id', '=', 'exits.from_warehouse_id');
            })
            ->select([
                DB::raw('COUNT(*) as total_combinations'),
                DB::raw('SUM(CASE WHEN COALESCE(SUM(entries.quantita), 0) - COALESCE(SUM(exits.quantita), 0) > 0 THEN 1 ELSE 0 END) as with_stock'),
                DB::raw('SUM(CASE WHEN COALESCE(SUM(entries.quantita), 0) - COALESCE(SUM(exits.quantita), 0) <= 0 THEN 1 ELSE 0 END) as without_stock')
            ])
            ->groupBy(['lp.id', 'lw.id'])
            ->havingRaw('COALESCE(SUM(entries.quantita), 0) > 0 OR COALESCE(SUM(exits.quantita), 0) > 0')
            ->get();

        return [
            'total_products' => LogisticProduct::count(),
            'total_warehouses' => Warehouse::count(),
            'active_combinations' => $stats->count(),
            'with_stock' => $stats->where('current_stock', '>', 0)->count(),
        ];
    }
} 