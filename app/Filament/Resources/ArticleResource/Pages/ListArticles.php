<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Seo\CsvKeywordExtractor;
use App\Services\Seo\KeywordRelevanceService;
use App\Services\Seo\SeoArticleGenerator;
use Filament\Actions;
use Filament\Actions\Action as HeaderAction;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use function Safe\preg_match;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            HeaderAction::make('createSeoArticlesFromCsv')
                ->label('Crea articoli SEO da CSV')
                ->icon('heroicon-m-sparkles')
                ->modalWidth('7xl')
                ->form([
                    Wizard::make([
                        Step::make('Pagina + CSV')
                            ->description('Scegli la pagina target e carica il CSV con le keyword')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->options(fn (): array => Menu::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->required()
                                    ->live(),

                                Select::make('menu_item_id')
                                    ->label('Voce menu (pagina target)')
                                    ->options(function (callable $get): array {
                                        $menuId = $get('menu_id');
                                        if (! $menuId) {
                                            return [];
                                        }

                                        return MenuItem::query()
                                            ->where('menu_id', $menuId)
                                            ->orderBy('sort')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live(),

                                Placeholder::make('target_url_preview')
                                    ->label('URL target (preview)')
                                    ->content(function (callable $get): string {
                                        $menuItemId = (int) ($get('menu_item_id') ?? 0);
                                        if ($menuItemId <= 0) {
                                            return '—';
                                        }
                                        $item = MenuItem::query()->find($menuItemId);
                                        if (! $item) {
                                            return '—';
                                        }

                                        $baseConfig = config('app.url');
                                        $base = is_string($baseConfig) ? $baseConfig : '';

                                        $href = is_string($item->href) ? $item->href : '';
                                        if (preg_match('#^https?://#i', $href) === 1) {
                                            return $href;
                                        }

                                        return rtrim($base, '/').'/'.ltrim($href, '/');
                                    }),

                                FileUpload::make('csv_file')
                                    ->label('CSV keyword')
                                    ->storeFiles(false)
                                    ->acceptedFileTypes(['text/csv', 'text/plain', '.csv'])
                                    ->required()
                                    ->maxFiles(1),
                            ]),

                        Step::make('Suggerisci keyword')
                            ->description('Estrai le keyword dal CSV e filtra quelle correlate alla pagina (LLM + sorgente URL)')
                            ->schema([
                                Hidden::make('csv_keywords'),
                                Hidden::make('suggested_keywords'),

                                Placeholder::make('csv_keywords_count')
                                    ->label('Keyword estratte dal CSV')
                                    ->content(function (callable $get): string {
                                        $k = $get('csv_keywords');
                                        $count = is_array($k) ? count($k) : 0;

                                        return $count > 0 ? (string) $count : 'Non ancora estratte';
                                    }),

                                Placeholder::make('suggested_keywords_count')
                                    ->label('Keyword correlate (suggestite)')
                                    ->content(function (callable $get): string {
                                        $opt = $get('suggested_keywords');
                                        $count = is_array($opt) ? count($opt) : 0;

                                        return $count > 0 ? (string) $count : 'Non ancora calcolate';
                                    }),

                                Placeholder::make('note')
                                    ->label('Nota')
                                    ->content('Clicca “Analizza e suggerisci” per calcolare le keyword correlate.'),

                                \Filament\Forms\Components\Actions::make([
                                    FormAction::make('analyze')
                                        ->label('Analizza e suggerisci keyword')
                                        ->icon('heroicon-o-magnifying-glass')
                                        ->action(function (Set $set, callable $get, CsvKeywordExtractor $extractor, KeywordRelevanceService $relevance) {
                                            $menuItemId = (int) ($get('menu_item_id') ?? 0);
                                            $csv = $get('csv_file');

                                            if ($menuItemId <= 0) {
                                                Notification::make()->title('Seleziona una voce menu prima di analizzare.')->warning()->send();

                                                return;
                                            }

                                            if (! $csv) {
                                                Notification::make()->title('Carica un CSV prima di analizzare.')->warning()->send();

                                                return;
                                            }

                                            /** @var TemporaryUploadedFile $csv */
                                            $keywords = $extractor->extractFromPath($csv->getRealPath());
                                            $set('csv_keywords', $keywords);

                                            $menuItem = MenuItem::query()->find($menuItemId);
                                            if (! $menuItem) {
                                                Notification::make()->title('Voce menu non trovata.')->danger()->send();

                                                return;
                                            }

                                            try {
                                                $suggestions = $relevance->suggest($menuItem, $keywords);
                                            } catch (\Throwable $e) {
                                                Notification::make()
                                                    ->title('Errore analisi')
                                                    ->body($e->getMessage())
                                                    ->danger()
                                                    ->send();

                                                return;
                                            }

                                            $options = [];
                                            $selected = [];
                                            foreach ($suggestions as $row) {
                                                $kw = (string) ($row['keyword'] ?? '');
                                                if ($kw === '') {
                                                    continue;
                                                }
                                                $score = (int) ($row['score'] ?? 0);
                                                $reason = (string) ($row['reason'] ?? '');
                                                $label = $kw;
                                                if ($score > 0) {
                                                    $label .= " — {$score}/100";
                                                }
                                                if (trim($reason) !== '') {
                                                    $label .= " — {$reason}";
                                                }
                                                $options[$kw] = $label;
                                                $selected[] = $kw;
                                            }

                                            $set('suggested_keywords', $options);
                                            $set('selected_keywords', $selected);

                                            Notification::make()
                                                ->title('Suggerimenti aggiornati')
                                                ->body('Ora puoi selezionare le keyword da usare per la generazione.')
                                                ->success()
                                                ->send();
                                        })
                                        ->disabled(fn (callable $get) => ! $get('menu_item_id') || ! $get('csv_file')),
                                ]),
                            ]),

                        Step::make('Seleziona + genera')
                            ->description('Seleziona le keyword e genera 1 articolo per keyword')
                            ->schema([
                                CheckboxList::make('selected_keywords')
                                    ->label('Keyword da generare')
                                    ->options(fn (callable $get): array => is_array($get('suggested_keywords')) ? $get('suggested_keywords') : [])
                                    ->columns(2)
                                    ->required(),

                                Placeholder::make('limit_note')
                                    ->label('Limite')
                                    ->content('Per sicurezza, la generazione è limitata a 10 articoli per singola esecuzione.'),
                            ]),
                    ])->columnSpanFull(),
                ])
                ->action(function (array $data, SeoArticleGenerator $generator) {
                    $menuItemId = (int) ($data['menu_item_id'] ?? 0);
                    $keywords = $data['selected_keywords'] ?? [];

                    if ($menuItemId <= 0 || ! is_array($keywords) || empty($keywords)) {
                        Notification::make()->title('Dati mancanti: seleziona pagina e keyword.')->danger()->send();

                        return;
                    }

                    $menuItem = MenuItem::query()->find($menuItemId);
                    if (! $menuItem) {
                        Notification::make()->title('Voce menu non trovata.')->danger()->send();

                        return;
                    }

                    $keywords = array_values(array_unique(array_filter(array_map('strval', $keywords), fn ($k) => trim($k) !== '')));
                    $keywords = array_slice($keywords, 0, 10);

                    $created = 0;
                    $errors = [];
                    $firstEditUrl = null;

                    foreach ($keywords as $kw) {
                        try {
                            $article = $generator->generate($menuItem, $kw);
                            $created++;

                            if ($firstEditUrl === null) {
                                $firstEditUrl = ArticleResource::getUrl('edit', ['record' => $article]);
                            }
                        } catch (\Throwable $e) {
                            $errors[] = "{$kw}: {$e->getMessage()}";
                        }
                    }

                    if ($created > 0) {
                        $n = $created;
                        $notif = Notification::make()
                            ->title("Creati {$n} articoli")
                            ->success();

                        if ($firstEditUrl) {
                            $notif->body('Apri il primo articolo per verificare contenuto e SEO.')
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('open_first')
                                        ->label('Apri primo articolo')
                                        ->url($firstEditUrl)
                                        ->button(),
                                ]);
                        }

                        $notif->send();
                    }

                    if (! empty($errors)) {
                        Notification::make()
                            ->title('Alcuni articoli non sono stati generati')
                            ->body(implode("\n", array_slice($errors, 0, 5)))
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}
