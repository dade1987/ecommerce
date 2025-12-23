<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Jobs\GenerateSeoArticleFromKeywordJob;
use App\Services\Seo\CsvKeywordExtractor;
use App\Services\Seo\KeywordRelevanceService;
use App\Services\Seo\MenuItemUrlResolver;
use Filament\Actions;
use Filament\Actions\Action as HeaderAction;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
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
                                TextInput::make('target_href')
                                    ->label('URL target (relativo o assoluto)')
                                    ->helperText('Esempio: /landing/prodotto oppure https://dominio.tld/landing/prodotto')
                                    ->required()
                                    ->live(onBlur: true),

                                Placeholder::make('target_url_preview')
                                    ->label('URL target (preview)')
                                    ->content(function (callable $get, MenuItemUrlResolver $resolver): string {
                                        $href = $get('target_href');
                                        $href = is_string($href) ? trim($href) : '';
                                        if ($href === '') {
                                            return '—';
                                        }

                                        try {
                                            return $resolver->resolveHref($href);
                                        } catch (\Throwable $e) {
                                            return 'URL non valido: '.$e->getMessage();
                                        }
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
                                            $targetHref = $get('target_href');
                                            $targetHref = is_string($targetHref) ? trim($targetHref) : '';
                                            $csv = $get('csv_file');

                                            if ($targetHref === '') {
                                                Notification::make()->title('Inserisci un URL target prima di analizzare.')->warning()->send();

                                                return;
                                            }

                                            if (! $csv) {
                                                Notification::make()->title('Carica un CSV prima di analizzare.')->warning()->send();

                                                return;
                                            }

                                            // Filament FileUpload può tornare: TemporaryUploadedFile, string, oppure array (single-file wrapped)
                                            $csvFile = $csv;
                                            if (is_array($csvFile)) {
                                                $csvFile = reset($csvFile) ?: null;
                                            }

                                            $csvPath = null;
                                            if ($csvFile instanceof TemporaryUploadedFile) {
                                                $csvPath = $csvFile->getRealPath();
                                            } elseif (is_string($csvFile)) {
                                                // Se è un path assoluto temporaneo (storeFiles(false) in alcuni setup)
                                                if (is_file($csvFile)) {
                                                    $csvPath = $csvFile;
                                                } elseif (Storage::disk('local')->exists($csvFile)) {
                                                    // Se è stato salvato su disco local (pattern già usato in CustomerResource)
                                                    $csvPath = storage_path('app/'.$csvFile);
                                                }
                                            }

                                            if (! is_string($csvPath) || $csvPath === '' || ! is_file($csvPath)) {
                                                Notification::make()
                                                    ->title('CSV non valido')
                                                    ->body('Non riesco a leggere il file caricato. Riprova a ricaricare il CSV.')
                                                    ->danger()
                                                    ->send();

                                                return;
                                            }

                                            $keywords = $extractor->extractFromPath($csvPath);
                                            $set('csv_keywords', $keywords);

                                            try {
                                                $suggestions = $relevance->suggest($targetHref, $keywords);
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
                                        ->disabled(function (callable $get): bool {
                                            $href = $get('target_href');
                                            $href = is_string($href) ? trim($href) : '';

                                            return $href === '' || ! $get('csv_file');
                                        }),
                                ]),
                            ]),

                        Step::make('Seleziona + genera')
                            ->description('Seleziona le keyword e genera 1 articolo per keyword')
                            ->schema([
                                \Filament\Forms\Components\Actions::make([
                                    FormAction::make('select_all_keywords')
                                        ->label('Seleziona tutte')
                                        ->icon('heroicon-o-check')
                                        ->action(function (Set $set, callable $get) {
                                            $options = $get('suggested_keywords');
                                            $options = is_array($options) ? $options : [];
                                            $set('selected_keywords', array_keys($options));
                                        }),
                                    FormAction::make('deselect_all_keywords')
                                        ->label('Deseleziona tutte')
                                        ->icon('heroicon-o-x-mark')
                                        ->action(fn (Set $set) => $set('selected_keywords', [])),
                                ]),
                                CheckboxList::make('selected_keywords')
                                    ->label('Keyword da generare')
                                    ->options(fn (callable $get): array => is_array($get('suggested_keywords')) ? $get('suggested_keywords') : [])
                                    ->columns(2)
                                    ->required(),
                            ]),
                    ])->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $targetHref = $data['target_href'] ?? '';
                    $targetHref = is_string($targetHref) ? trim($targetHref) : '';
                    $keywords = $data['selected_keywords'] ?? [];

                    if ($targetHref === '' || ! is_array($keywords) || empty($keywords)) {
                        Notification::make()->title('Dati mancanti: inserisci URL target e seleziona keyword.')->danger()->send();

                        return;
                    }

                    $keywords = array_values(array_unique(array_filter(array_map('strval', $keywords), fn ($k) => trim($k) !== '')));
                    $queued = count($keywords);

                    // Unlimited: dispatch UN job che crea placeholder + dispatcha i job per keyword
                    Bus::dispatch(function () use ($keywords, $targetHref) {
                        foreach ($keywords as $kw) {
                            $kw = trim((string) $kw);
                            if ($kw === '') {
                                continue;
                            }
                            GenerateSeoArticleFromKeywordJob::dispatch(
                                targetHref: $targetHref,
                                keyword: $kw
                            );
                        }
                    });

                    $notif = Notification::make()
                        ->title("Messi in coda {$queued} articoli")
                        ->body('Generazione illimitata in coda: i placeholder compariranno in lista e verranno aggiornati quando i job finiscono.')
                        ->success();

                    $notif->send();
                }),
        ];
    }
}
