<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use App\Models\Team;
use App\Services\EmbeddingCacheService;
use App\Services\WebsiteScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * RealtimeChatWebsiteController
 *
 * Controller ottimizzato per analizzare risposte da multiple URL di un Team
 * con una sola chiamata GPT, mantenendo la logica separata da RealtimeChatController
 * per non sporcare il file principale.
 */
class RealtimeChatWebsiteController extends Controller
{
    public OpenAIClient $client;
    private ?Team $cachedTeam = null;
    private ?string $cachedTeamSlug = null;
    private ?EmbeddingCacheService $embeddingService = null;
    private ?WebsiteScraperService $scraperService = null;

    public function __construct()
    {
        $apiKey = config('openapi.key');
        $this->client = OpenAI::client($apiKey);
        $this->embeddingService = new EmbeddingCacheService($this->client);
        $this->scraperService = new WebsiteScraperService($this->client);
    }

    /**
     * Ottiene Team dal cache locale durante la richiesta
     */
    private function getTeamCached(string $teamSlug): ?Team
    {
        if ($this->cachedTeamSlug === $teamSlug && $this->cachedTeam !== null) {
            return $this->cachedTeam;
        }
        $team = Team::where('slug', $teamSlug)->first();
        if ($team) {
            $this->cachedTeam = $team;
            $this->cachedTeamSlug = $teamSlug;
        }

        return $team;
    }

    /**
     * Normalizza gli URL dal Repeater Filament (che salva come array di oggetti)
     * in un semplice array di stringhe
     */
    private function normalizeWebsites(array $websites): array
    {
        $normalized = [];
        foreach ($websites as $site) {
            if (is_string($site)) {
                // Se è già una stringa, aggiungila
                if (trim($site) !== '') {
                    $normalized[] = trim($site);
                }
            } elseif (is_array($site)) {
                // Se è un array (dal Repeater Filament), prendi il primo valore
                foreach ($site as $url) {
                    if (is_string($url) && trim($url) !== '') {
                        $normalized[] = trim($url);
                    }
                }
            }
        }

        return $normalized;
    }

    /**
     * Endpoint SSE: Analizza domanda dai siti web del Team con UNA SOLA chiamata GPT
     * GET /api/chatbot/website-stream?message=...&team=...&locale=it
     */
    public function websiteStream(Request $request)
    {
        $userInput = (string) $request->query('message', '');
        $teamSlug = (string) $request->query('team', '');
        $locale = (string) $request->query('locale', 'it');

        $response = new StreamedResponse(function () use ($userInput, $teamSlug, $locale) {
            $flush = function (array $payload, string $event = 'message') {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();
            };

            // Genera thread_id
            $streamThreadId = (string) (request()->query('thread_id') ?: str()->uuid());
            $flush(['token' => json_encode(['thread_id' => $streamThreadId])]);

            $flush(['status' => 'started']);

            if (trim($userInput) === '') {
                $flush(['token' => ''], 'done');

                return;
            }

            try {
                // Persisti messaggio utente
                Quoter::create([
                    'thread_id' => $streamThreadId,
                    'role' => 'user',
                    'content' => $userInput,
                ]);

                // Recupera team
                $team = $this->getTeamCached($teamSlug);
                if (!$team) {
                    $flush(['error' => 'Team non trovato'], 'error');
                    $flush(['token' => ''], 'done');

                    return;
                }

                // Verifica se il team ha siti web configurati
                $websites = $team->websites ?? [];
                if (empty($websites) || !is_array($websites)) {
                    $flush(['error' => 'Nessun sito web configurato per questo team'], 'error');
                    $flush(['token' => ''], 'done');

                    return;
                }

                // Normalizza gli URL dal Repeater Filament
                $normalizedWebsites = $this->normalizeWebsites($websites);

                // Scrapa tutti i siti web
                $websiteContent = $this->scraperService->scrapeTeamWebsites($normalizedWebsites, (string) $team->id);

                if (!$websiteContent || trim($websiteContent) === '') {
                    $flush(['error' => 'Impossibile recuperare il contenuto dai siti web'], 'error');
                    $flush(['token' => ''], 'done');

                    return;
                }

                // **UNA SOLA CHIAMATA GPT PER ANALIZZARE TUTTO IL CONTENUTO**
                $this->analyzeAndStreamResponse(
                    $websiteContent,
                    $userInput,
                    $streamThreadId,
                    $locale,
                    $flush
                );
            } catch (\Throwable $e) {
                Log::error('RealtimeChatWebsiteController.websiteStream error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $flush(['error' => 'Errore durante l\'analisi: ' . $e->getMessage()], 'error');
                $flush(['token' => ''], 'done');
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Analizza il contenuto con GPT e streamma la risposta parola per parola
     * LOGICA SEPARATA: una sola chiamata GPT, non due come nel controller principale
     */
    private function analyzeAndStreamResponse(
        string $websiteContent,
        string $userInput,
        string $streamThreadId,
        string $locale,
        callable $flush
    ): void {
        try {
            // Taglia il contenuto se troppo lungo (token limit)
            $maxContentLength = 12000;
            if (strlen($websiteContent) > $maxContentLength) {
                $websiteContent = mb_substr($websiteContent, 0, $maxContentLength) . "\n...[contenuto troncato]";
            }

            // UNA SOLA CHIAMATA GPT STREAMING
            $stream = $this->client->chat()->createStreamed([
                'model' => 'gpt-4o-mini',
                'temperature' => 0.6,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Sei un AI Assistant che analizza i siti web aziendali. '
                            . 'Rispondi in modo conciso e professionale basandoti SOLO sul contenuto dei siti fornito. '
                            . 'Se la risposta non è disponibile nei siti, comunica chiaramente. '
                            . "Lingua: {$locale}",
                    ],
                    [
                        'role' => 'system',
                        'content' => "Contenuto dai siti web:\n\n{$websiteContent}",
                    ],
                    [
                        'role' => 'user',
                        'content' => (string) $userInput,
                    ],
                ],
            ]);

            $fullResponse = '';

            // Streamma i token
            foreach ($stream as $response) {
                $piece = $response->choices[0]->delta->content ?? null;
                if ($piece !== null && $piece !== '') {
                    $fullResponse .= $piece;
                    $flush(['token' => $piece]);
                }
            }

            // Persisti la risposta
            Quoter::create([
                'thread_id' => $streamThreadId,
                'role' => 'chatbot',
                'content' => $fullResponse ?: 'Nessuna risposta generata.',
            ]);

            $flush(['token' => ''], 'done');
        } catch (\Throwable $e) {
            Log::error('RealtimeChatWebsiteController.analyzeAndStreamResponse', [
                'error' => $e->getMessage(),
            ]);
            $flush(['error' => 'Errore nell\'analisi GPT: ' . $e->getMessage()], 'error');
            $flush(['token' => ''], 'done');
        }
    }
}
