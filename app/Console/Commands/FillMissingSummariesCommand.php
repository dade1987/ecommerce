<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use OpenAI;

class FillMissingSummariesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-missing-summaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills empty summaries for articles by generating SEO meta descriptions from the content using AI.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Finding articles with empty summaries...');

        $articles = Article::whereNull('summary')->orWhere('summary', '')->get();

        if ($articles->isEmpty()) {
            $this->info('No articles with empty summaries found.');
            return 0;
        }

        $this->info("Found {$articles->count()} articles to update. Starting process...");

        $apiKey = config('openapi.key');
        if (empty($apiKey)) {
            $this->error('OpenAI API key is not configured. Please set it in config/openapi.php or .env file.');
            return 1;
        }
        $client = OpenAI::client($apiKey);

        foreach ($articles as $article) {
            $this->info("Processing article: \"{$article->title}\" (ID: {$article->id})");

            try {
                $response = $client->chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Sei un esperto di SEO. Il tuo compito è creare una meta description ottimizzata per i motori di ricerca, basata sul contenuto di un articolo che ti viene fornito. La meta description deve essere un riassunto conciso e accattivante (massimo 160 caratteri) per invogliare al click. Fornisci come risposta solo ed esclusivamente il testo della meta description, senza frasi introduttive come 'Ecco la meta description:' o simili."
                        ],
                        [
                            'role' => 'user',
                            'content' => "Genera una meta description per il seguente articolo:\n\n" . strip_tags($article->content),
                        ]
                    ],
                ]);

                $metaDescription = trim($response->choices[0]->message->content);

                if (!empty($metaDescription)) {
                    $article->summary = $metaDescription;
                    $article->save();
                    $this->info("-> Summary updated successfully for article ID: {$article->id}.");
                } else {
                    $this->warn("-> AI returned an empty summary for article ID: {$article->id}. Skipping.");
                    Log::warning("AI returned an empty summary for article '{$article->title}' (ID: {$article->id}).");
                }
            } catch (\Exception $e) {
                $this->error("-> Failed to generate summary for article ID: {$article->id} - " . $e->getMessage());
                Log::error("OpenAI API call failed for article '{$article->title}' (ID: {$article->id}): " . $e->getMessage());
                continue;
            }
        }

        $this->info('All articles have been processed.');
        return 0;
    }
} 