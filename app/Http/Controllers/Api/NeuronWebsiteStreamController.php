<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use App\Models\Team;
use App\Neuron\WebsiteAssistantAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
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
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();
            };

            $streamThreadId = (string) (request()->query('thread_id') ?: str()->uuid());

            Log::info('NeuronWebsiteStreamController.stream START', [
                'thread_id' => $streamThreadId,
                'received_thread_id' => request()->query('thread_id'),
                'is_new' => !request()->query('thread_id'),
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
                if (!$team) {
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

                // Scrapa i siti web del team
                $websites = $team->websites ?? [];
                $normalizedWebsites = empty($websites) || !is_array($websites) ? [] : $this->normalizeWebsites($websites);
                $websiteContent = '';
                
                if (!empty($normalizedWebsites)) {
                    $websiteContent = $scraperService->scrapeTeamWebsites($normalizedWebsites, (string) $team->id) ?? '';
                }

                // Taglia il contenuto se troppo lungo
                if (strlen($websiteContent) > 12000) {
                    $websiteContent = mb_substr($websiteContent, 0, 12000) . "\n...[contenuto troncato]";
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
                
                // Streaming nativo con history già caricata dal memory provider
                $stream = $agent->stream(new UserMessage($userInput));

                foreach ($stream as $chunk) {
                    // Se il chunk non è un oggetto di tool call, è testo
                    if (is_string($chunk)) {
                        $fullContent .= $chunk;
                        $flush(['token' => $chunk]);
                    }
                }

                // Se il contenuto è vuoto, usa un messaggio di fallback
                if (empty($fullContent)) {
                    $fullContent = 'Mi dispiace, non sono riuscito a generare una risposta.';
                }

                Log::info('NeuronWebsiteStreamController: Response completed', [
                    'thread_id' => $streamThreadId,
                    'content_length' => strlen($fullContent),
                ]);

                $flush(['token' => ''], 'done');
            } catch (\Throwable $e) {
                Log::error('NeuronWebsiteStreamController error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'thread_id' => $streamThreadId,
                    'error_class' => get_class($e),
                    'code' => $e->getCode(),
                ]);
                
                // Se è un errore OpenAI, tenta di estrarre più dettagli
                if (strpos($e->getMessage(), 'openai.com') !== false || strpos($e->getMessage(), 'Bad Request') !== false) {
                    Log::error('OpenAI API Error Detail', [
                        'full_message' => $e->getMessage(),
                        'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                    ]);
                }
                
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
