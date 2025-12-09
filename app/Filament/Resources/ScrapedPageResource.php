<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScrapedPageResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\WebScraper\Models\ScrapedPage;

class ScrapedPageResource extends Resource
{
    protected static ?string $model = ScrapedPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'AI & Chatbot';

    protected static ?string $navigationLabel = 'Pagine scrapate';

    protected static ?string $modelLabel = 'Pagina scrapata';

    protected static ?string $pluralModelLabel = 'Pagine scrapate';

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
                    ->url(fn (ScrapedPage $record): string => $record->url, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Scade il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creata il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('domain')
                    ->label('Dominio')
                    ->options(function (): array {
                        /** @var \Illuminate\Support\Collection<string,string> $domains */
                        $domains = ScrapedPage::query()
                            ->select('url')
                            ->get()
                            ->map(function (ScrapedPage $page): ?string {
                                return $page->domain;
                            })
                            ->filter()
                            ->unique()
                            ->values();

                        return $domains
                            ->mapWithKeys(fn (string $domain): array => [$domain => $domain])
                            ->all();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $domain = $data['value'] ?? null;

                        if (! $domain) {
                            return $query;
                        }

                        // Filtra per dominio guardando l'host dell'URL.
                        // Gestisce sia http che https (ed eventuali schemi alternativi).
                        return $query->where(function (Builder $q) use ($domain): void {
                            $q->where('url', 'like', 'http://'.$domain.'%')
                                ->orWhere('url', 'like', 'https://'.$domain.'%')
                                ->orWhere('url', 'like', '%://'.$domain.'%');
                        });
                    }),
            ])
            ->defaultSort('domain')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScrapedPages::route('/'),
        ];
    }
}

