<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use App\Models\Team;
use App\Models\Thread;
use App\Neuron\WebsiteAssistantAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\ob_flush;
use function Safe\preg_split;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * NeuronWebsiteStreamController
 *
 * Controller SSE che usa l'Agent Neuron per gestire il chatbot dei siti web.
 * Fornisce streaming nativo tramite il framework Neuron AI.
 *
 * Endpoint: GET /api/chatbot/neuron-website-stream?message=...&team=...&locale=it
 */
class NeuronWebsiteStreamController extends Controller
{
    /**
     * Endpoint SSE: Streaming nativo con Neuron Agent
     */
    public function stream(Request $request)
    {
        $userInput = (string) $request->query('message', '');
        $teamSlug = (string) $request->query('team', '');
        $locale = (string) $request->query('locale', 'it');
        $activityUuid = $request->query('uuid');

        // Istanzia il service FUORI dal closure
        $apiKey = config('services.openai.key');
        $client = \OpenAI::client($apiKey);
        $scraperService = new \App\Services\WebsiteScraperService($client);

        $response = new StreamedResponse(function () use ($userInput, $teamSlug, $locale, $activityUuid, $scraperService) {
            $flush = function (array $payload, string $event = 'message') {
                echo "event: {$event}\n";
                echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
                @ob_flush();
                @flush();
            };

            $streamThreadId = (string) (request()->query('thread_id') ?: str()->uuid());

            // Registra i metadati del thread (solo alla prima inizializzazione)
            Thread::captureFromRequest($streamThreadId, request(), [
                'team_slug' => $teamSlug,
                'activity_uuid' => $activityUuid,
            ]);

            Log::info('NeuronWebsiteStreamController.stream START', [
                'thread_id' => $streamThreadId,
                'received_thread_id' => request()->query('thread_id'),
                'is_new' => ! request()->query('thread_id'),
                'team_slug' => $teamSlug,
            ]);

            $flush(['token' => json_encode(['thread_id' => $streamThreadId])]);
            $flush(['status' => 'started']);

            if (trim($userInput) === '') {
                $flush(['token' => ''], 'done');

                return;
            }

            try {
                // Verifica che il team esista
                $team = Team::where('slug', $teamSlug)->first();
                if (! $team) {
                    $flush(['error' => 'Team non trovato'], 'error');
                    $flush(['token' => ''], 'done');

                    return;
                }

                // Controllo greeting
                if (mb_strtolower($userInput) === mb_strtolower(trans('enjoy-work.greeting', [], $locale))) {
                    $welcomeMessage = (string) $team->welcome_message ?: (string) trans('enjoy-work.welcome_message_fallback', [], $locale);
                    $this->streamTextByWord($welcomeMessage, $flush);

                    Quoter::create([
                        'thread_id' => $streamThreadId,
                        'role' => 'chatbot',
                        'content' => $welcomeMessage,
                    ]);

                    $flush(['token' => ''], 'done');

                    return;
                }

                // Scrapa i siti web del team (fallback tradizionale)
                // L'agent può usare il tool searchSite per ricerche specifiche con RAG
                $websites = $team->websites ?? [];
                $normalizedWebsites = empty($websites) || ! is_array($websites) ? [] : $this->normalizeWebsites($websites);
                $websiteContent = '';

                if (! empty($normalizedWebsites)) {
                    // Fallback tradizionale: scraping solo se necessario per contesto generale
                    // Per ricerche specifiche, l'agent userà il tool searchSite che usa RAG
                    $websiteContent = $scraperService->scrapeTeamWebsites($normalizedWebsites, (string) $team->id) ?? '';
                }

                // Taglia il contenuto se troppo lungo
                if (strlen($websiteContent) > 12000) {
                    $websiteContent = mb_substr($websiteContent, 0, 12000)."\n...[contenuto troncato]";
                }

                // Crea e configura l'agent Neuron
                $agent = WebsiteAssistantAgent::make()
                    ->withWebsiteContext($teamSlug, $locale, $activityUuid, $websiteContent)
                    ->withChatHistory(new \App\Neuron\QuoterChatHistory($streamThreadId));

                Log::debug('NeuronWebsiteStreamController: Agent created and configured', [
                    'thread_id' => $streamThreadId,
                    'team_slug' => $teamSlug,
                    'locale' => $locale,
                ]);

                // Streaming nativo da Neuron
                $fullContent = '';
                $chunksTotal = 0;
                $textChunks = 0;
                $emptyTextChunks = 0;
                $nonStringChunks = 0;
                $nonStringTypes = [];

                // Streaming nativo con history già caricata dal memory provider
                $stream = $agent->stream(new UserMessage($userInput));

                foreach ($stream as $chunk) {
                    $chunksTotal++;
                    // Se il chunk non è un oggetto di tool call, è testo
                    if (is_string($chunk)) {
                        $textChunks++;
                        if ($chunk === '') {
                            $emptyTextChunks++;
                        }
                        $fullContent .= $chunk;
                        $flush(['token' => $chunk]);
                    } else {
                        $nonStringChunks++;
                        $t = is_object($chunk) ? get_class($chunk) : gettype($chunk);
                        if (count($nonStringTypes) < 5) {
                            $nonStringTypes[] = $t;
                        }
                    }
                }

                // Se il contenuto è vuoto, usa un messaggio di fallback
                if (empty($fullContent)) {
                    $fullContent = 'Mi dispiace, non sono riuscito a generare una risposta.';
                }

                // Debug mirato: quando succede la "risposta vuota", logga cosa è arrivato dallo stream
                try {
                    if (strtolower((string) $teamSlug) === 'cavalliniservice') {
                        Log::warning('NeuronWebsiteStreamController.empty_or_weird_stream', [
                            'thread_id' => $streamThreadId,
                            'team_slug' => $teamSlug,
                            'user_input_preview' => mb_substr($userInput, 0, 300),
                            'website_content_length' => isset($websiteContent) ? strlen((string) $websiteContent) : null,
                            'chunks_total' => $chunksTotal,
                            'text_chunks' => $textChunks,
                            'empty_text_chunks' => $emptyTextChunks,
                            'non_string_chunks' => $nonStringChunks,
                            'non_string_types_sample' => $nonStringTypes,
                            'final_content_length' => strlen($fullContent),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('NeuronWebsiteStreamController.stream debug log failed', [
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('NeuronWebsiteStreamController: Response completed', [
                    'thread_id' => $streamThreadId,
                    'content_length' => strlen($fullContent),
                ]);

                $flush(['token' => ''], 'done');
            } catch (\Throwable $e) {
                $errorDetails = [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'thread_id' => $streamThreadId,
                    'error_class' => get_class($e),
                    'code' => $e->getCode(),
                ];

                // Estrai il body della risposta HTTP se è un errore Guzzle
                if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                    try {
                        $responseBody = $e->getResponse()->getBody()->getContents();
                        $errorDetails['http_status'] = $e->getResponse()->getStatusCode();
                        $errorDetails['response_body'] = $responseBody;
                        $errorDetails['response_json'] = json_decode($responseBody, true);
                    } catch (\Throwable $parseError) {
                        $errorDetails['response_body_error'] = $parseError->getMessage();
                    }
                }

                Log::error('NeuronWebsiteStreamController error', $errorDetails);

                // Se è un errore OpenAI/vLLM, tenta di estrarre più dettagli
                if (strpos($e->getMessage(), 'openai.com') !== false ||
                    strpos($e->getMessage(), 'Bad Request') !== false ||
                    strpos($e->getMessage(), 'runpod.net') !== false) {
                    Log::error('API Error Detail', [
                        'full_message' => $e->getMessage(),
                        'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                        'response_details' => $errorDetails['response_body'] ?? null,
                    ]);
                }

                $flush(['error' => 'Errore durante l\'analisi: '.$e->getMessage()], 'error');
                $flush(['token' => ''], 'done');
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Normalizza gli URL dal Repeater Filament
     */
    private function normalizeWebsites(array $websites): array
    {
        $normalized = [];
        foreach ($websites as $site) {
            if (is_string($site)) {
                if (trim($site) !== '') {
                    $normalized[] = trim($site);
                }
            } elseif (is_array($site)) {
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
     * Streamma il testo parola per parola (con delays)
     */
    private function streamTextByWord(string $text, callable $flusher): void
    {
        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($words as $w) {
            if ($w === '') {
                continue;
            }
            $flusher(['token' => $w]);
            usleep(28_000);
        }
    }
}
