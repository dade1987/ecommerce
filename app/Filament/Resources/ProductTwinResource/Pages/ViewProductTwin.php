<?php

namespace App\Filament\Resources\ProductTwinResource\Pages;

use App\Filament\Resources\ProductTwinResource;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProductTwin extends ViewRecord
{
    protected static string $resource = ProductTwinResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informazioni Principali')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('uuid')->label('UUID'),
                        TextEntry::make('internalProduct.name')->label('Nome Prodotto'),
                        TextEntry::make('internalProduct.code')->label('Codice Prodotto'),
                        TextEntry::make('lifecycle_status')
                            ->label('Stato Ciclo di Vita')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'in_production' => 'gray',
                                'in_stock' => 'success',
                                'in_transit' => 'warning',
                                'in_use' => 'info',
                                'recycled' => 'primary',
                                default => 'gray',
                            }),
                    ]),
                Section::make('Impronta di Carbonio (kg CO₂)')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('co2_emissions_production')->label('Produzione')->numeric(2)->suffix(' kg'),
                        TextEntry::make('co2_emissions_logistics')->label('Logistica')->numeric(2)->suffix(' kg'),
                        TextEntry::make('co2_emissions_total')->label('Totale')->numeric(2)->suffix(' kg')->weight('bold'),
                    ]),
                Section::make('Metadati')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('internalProduct.weight')->label('Peso (kg)')->numeric(2),
                        TextEntry::make('internalProduct.unit_of_measure')->label('Unità di Misura'),
                        TextEntry::make('metadata')
                            ->label('Dati Aggiuntivi')
                            ->formatStateUsing(fn ($state) => collect($state)->map(fn ($value, $key) => "<strong>{$key}:</strong> {$value}")->implode('<br>'))
                            ->html(),
                        TextEntry::make('internalProduct.materials')
                            ->label('Materiali')
                            ->formatStateUsing(fn ($state) => collect($state)->implode(', ')),
                    ]),
            ]);
    }
}
