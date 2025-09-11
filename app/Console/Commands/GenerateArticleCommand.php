<?php

namespace App\Console\Commands;

use App\Jobs\GenerateArticleJob;
use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use function Safe\file;
use function Safe\shuffle;

class GenerateArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-article {--test : Generate only one article for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new article from a keyword and an external AI Service.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keywordsPath = base_path('keywords.txt');

        if (!File::exists($keywordsPath)) {
            $this->error('keywords.txt file not found!');
            return 1;
        }

        $keywords = file($keywordsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($keywords)) {
            $this->info('No more keywords to process.');
            return 0;
        }

        if ($this->option('test')) {
            $keywords = [array_shift($keywords)];
            $this->info('Test mode: generating only one article.');
        } else {
            shuffle($keywords);
            //$keywords = array_slice($keywords, 0, 3);
        }

        $this->info('Dispatching jobs for ' . count($keywords) . ' keywords.');

        // Costruisci la lista location: tutti i comuni di Veneto e Lombardia
        $cachePath = storage_path('app/comuni.json');
        $locations = [];
        $comuni = [];
        try {
            $mustRefresh = true;
            if (File::exists($cachePath)) {
                $ageSeconds = time() - File::lastModified($cachePath);
                // aggiorna mensilmente
                $mustRefresh = $ageSeconds > 60 * 60 * 24 * 30;
            }
            if ($mustRefresh) {
                $this->info('Scarico elenco comuni aggiornato...');
                $response = Http::timeout(20)->get('https://raw.githubusercontent.com/matteocontrini/comuni-json/master/comuni.json');
                if ($response->successful()) {
                    File::put($cachePath, $response->body());
                } else {
                    $this->warn('Impossibile scaricare elenco comuni, uso la cache locale se disponibile.');
                }
            }
            if (File::exists($cachePath)) {
                $data = json_decode(File::get($cachePath), true);
                if (is_array($data)) {
                    $comuni = collect($data)
                        ->filter(function ($comune) {
                            return isset($comune['regione']) && in_array($comune['regione'], ['Lombardia', 'Veneto'], true);
                        })
                        ->map(function ($comune) {
                            return [
                                'nome' => $comune['nome'] ?? null,
                                // Alcuni dataset includono 'popolazione'; se assente, sarà null
                                'popolazione' => $comune['popolazione'] ?? null,
                            ];
                        })
                        ->filter(fn ($c) => !empty($c['nome']))
                        ->unique('nome')
                        ->values()
                        ->all();
                    // Ordina per popolazione desc se disponibile, altrimenti alfabetico
                    usort($comuni, function ($a, $b) {
                        $pa = $a['popolazione'] ?? null;
                        $pb = $b['popolazione'] ?? null;
                        if (is_numeric($pa) && is_numeric($pb)) {
                            return (int)$pb <=> (int)$pa;
                        }
                        return strcmp($a['nome'], $b['nome']);
                    });
                    $locations = array_map(fn ($c) => $c['nome'], $comuni);
                }
            }
            if (empty($locations)) {
                $this->error('Nessun elenco comuni disponibile (download e cache falliti).');
                return 1;
            }
        } catch (\Throwable $e) {
            $this->error('Errore nel caricamento dei comuni: ' . $e->getMessage());
            return 1;
        }

        // Escludi comuni già pubblicati e seleziona solo 3 location/giorno
        $publishedPath = storage_path('app/published_locations.json');
        $alreadyPublished = [];
        if (File::exists($publishedPath)) {
            $decoded = json_decode(File::get($publishedPath), true);
            if (is_array($decoded)) {
                $alreadyPublished = array_values(array_filter(array_map('strval', $decoded)));
            }
        }
        $remaining = array_values(array_diff($locations, $alreadyPublished));
        $selectedLocations = array_slice($remaining, 0, 3);

        if (empty($selectedLocations)) {
            $this->info('Nessuna nuova location da pubblicare oggi.');
            return 0;
        }

        $internalLinksList = '';
        $navbar = Menu::with('items')->where('name', 'Navbar')->first();
        if ($navbar && $navbar->relationLoaded('items')) {
            /** @var \Illuminate\Database\Eloquent\Collection $items */
            $items = $navbar->getRelation('items');
            $internalLinksList = $items->map(function ($item) {
                return "- {$item->name}: {$item->href}";
            })->implode("\n");
        }

        $jobCount = 0;
        foreach ($keywords as $keyword) {
            foreach ($selectedLocations as $location) {
                GenerateArticleJob::dispatch($keyword, $location, $internalLinksList);
                $this->info("Job dispatched for keyword: \"{$keyword} a {$location}\"");
                $jobCount++;
                if ($jobCount >= 3) {
                    break 2; // Pubblica al massimo 3 location al giorno
                }
            }
        }
        // Aggiorna la lista dei comuni pubblicati
        $newPublished = array_values(array_unique(array_merge($alreadyPublished, $selectedLocations)));
        File::put($publishedPath, json_encode($newPublished, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('All jobs have been dispatched.');
        return 0;
    }
}
