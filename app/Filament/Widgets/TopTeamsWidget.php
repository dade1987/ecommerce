<?php

namespace App\Filament\Widgets;

use App\Models\Team;
use App\Models\WebsiteSearch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopTeamsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WebsiteSearch::query()
                    ->select('team_id')
                    ->selectRaw('MD5(CONCAT(team_id, "-", MAX(created_at))) as id')
                    ->selectRaw('COUNT(*) as search_count')
                    ->selectRaw('MAX(created_at) as last_search')
                    ->whereNotNull('team_id')
                    ->groupBy('team_id')
                    ->orderByDesc('search_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('search_count')
                    ->label('Ricerche')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('last_search')
                    ->label('Ultima Ricerca')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('search_count', 'desc')
            ->heading('Team Più Attivi')
            ->description('Top 10 team per numero di ricerche effettuate');
    }
}
