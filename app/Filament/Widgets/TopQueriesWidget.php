<?php

namespace App\Filament\Widgets;

use App\Models\WebsiteSearch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopQueriesWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WebsiteSearch::query()
                    ->select('query')
                    ->selectRaw('MD5(query) as id')
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('MAX(created_at) as last_used')
                    ->groupBy('query')
                    ->orderByDesc('count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('query')
                    ->label('Query')
                    ->searchable()
                    ->limit(60)
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('count')
                    ->label('Volte')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('last_used')
                    ->label('Ultima Volta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('count', 'desc')
            ->heading('Query Più Frequenti')
            ->description('Top 10 query di ricerca più utilizzate');
    }
}
