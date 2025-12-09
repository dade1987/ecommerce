<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebscraperPageResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\WebScraper\Models\WebscraperPage;

class WebscraperPageResource extends Resource
{
    protected static ?string $model = WebscraperPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'AI & Chatbot';

    protected static ?string $navigationLabel = 'Pagine Atlas (RAG)';

    protected static ?string $modelLabel = 'Pagina indicizzata';

    protected static ?string $pluralModelLabel = 'Pagine indicizzate';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->label('Dominio')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(80)
                    ->searchable()
                    ->wrap()
                    ->url(fn (WebscraperPage $record): string => $record->url, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                Tables\Columns\TextColumn::make('chunk_count')
                    ->label('Chunks')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_scraped_at')
                    ->label('Ultimo scraping')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('domain')
                    ->label('Dominio')
                    ->options(function (): array {
                        return WebscraperPage::query()
                            ->select('domain')
                            ->whereNotNull('domain')
                            ->distinct()
                            ->orderBy('domain')
                            ->pluck('domain', 'domain')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $domain = $data['value'] ?? null;

                        if (! $domain) {
                            return $query;
                        }

                        return $query->where('domain', $domain);
                    }),
            ])
            ->defaultSort('domain')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebscraperPages::route('/'),
        ];
    }
}

