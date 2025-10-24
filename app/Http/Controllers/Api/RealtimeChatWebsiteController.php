<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;
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
 * con UNA SOLA chiamata GPT che include contesto, tools e siti web.
 * Mantiene logica separata da RealtimeChatController per chiarezza.
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
     * Endpoint SSE: Analizza domanda dai siti web + contesto completo + streaming nativo Chat API
     * GET /api/chatbot/website-stream?message=...&team=...&locale=it&assistants=1
     */
    public function websiteStream(Request $request)
    {
        $userInput = (string) $request->query('message', '');
        $teamSlug = (string) $request->query('team', '');
        $locale = (string) $request->query('locale', 'it');
        $activityUuid = $request->query('uuid');

        $response = new StreamedResponse(function () use ($userInput, $teamSlug, $locale, $activityUuid) {
            $flush = function (array $payload, string $event = 'message') {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();
            };

            $streamThreadId = (string) (request()->query('thread_id') ?: str()->uuid());
            
            // LOG: quale thread stiamo usando e se è nuovo o ricevuto dal client
            Log::info('RealtimeChatWebsiteController.websiteStream START', [
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
                Quoter::create([
                    'thread_id' => $streamThreadId,
                    'role' => 'user',
                    'content' => $userInput,
                ]);

                $team = $this->getTeamCached($teamSlug);
                if (!$team) {
                    $flush(['error' => 'Team non trovato'], 'error');
                    $flush(['token' => ''], 'done');
                    return;
                }

                // Greeting immediato
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

                // Determina se usare Assistants o Chat Completions
                $incomingAssistantThreadId = (string) (request()->query('assistant_thread_id') ?? '');
                $useAssistants = (bool) request()->query('assistants', false) || ($incomingAssistantThreadId !== '');
                Log::debug('website stream mode decision', [
                    'useAssistants' => $useAssistants,
                    'incomingAssistantThreadId' => $incomingAssistantThreadId,
                    'streamThreadId' => $streamThreadId,
                    'teamSlug' => $teamSlug,
                ]);

                if (! $useAssistants) {
                    // MODALITÀ CHAT COMPLETIONS: Scrapa i siti web del team
                    $websites = $team->websites ?? [];
                    $normalizedWebsites = empty($websites) || !is_array($websites) ? [] : $this->normalizeWebsites($websites);
                    $websiteContent = '';
                    if (!empty($normalizedWebsites)) {
                        $websiteContent = $this->scraperService->scrapeTeamWebsites($normalizedWebsites, (string) $team->id) ?? '';
                    }

                    // Costruisci contesto completo (FAQ, indirizzo, prodotti)
                    $context = $this->buildContextForMessage($teamSlug, $userInput, $locale);

                    // CHAT API STREAMING CON WEBSITE CONTENT E CONTESTO
                    $this->analyzeAndStreamResponse(
                        websiteContent: $websiteContent,
                        context: $context,
                        userInput: $userInput,
                        teamSlug: $teamSlug,
                        streamThreadId: $streamThreadId,
                        locale: $locale,
                        activityUuid: $activityUuid,
                        flush: $flush
                    );
                } else {
                    // MODALITÀ ASSISTANTS API
                    try {
                        // 1) Determina/crea thread Assistants
                        $assistantThreadId = (string) (request()->query('assistant_thread_id') ?? '');
                        $maybeThreadId = (string) (request()->query('thread_id') ?? '');
                        if ($assistantThreadId === '' && (str_starts_with($maybeThreadId, 'thread_') || substr($maybeThreadId, 0, 7) === 'thread_')) {
                            $assistantThreadId = $maybeThreadId;
                        }
                        if ($assistantThreadId === '') {
                            $assistantThreadId = $this->client->threads()->create([])->id;
                        }
                        // Comunica al client l'assistant_thread_id così lo persiste
                        $flush(['token' => json_encode(['assistant_thread_id' => $assistantThreadId])]);

                        // 2) Aggiunge messaggio utente al thread
                        $this->client->threads()->messages()->create($assistantThreadId, [
                            'role'    => 'user',
                            'content' => (string) $userInput,
                        ]);

                        // Scrapa i siti web per il contesto
                        $websites = $team->websites ?? [];
                        $normalizedWebsites = empty($websites) || !is_array($websites) ? [] : $this->normalizeWebsites($websites);
                        $websiteContent = '';
                        if (!empty($normalizedWebsites)) {
                            $websiteContent = $this->scraperService->scrapeTeamWebsites($normalizedWebsites, (string) $team->id) ?? '';
                        }

                        // Taglia il contenuto se troppo lungo
                        if (strlen($websiteContent) > 12000) {
                            $websiteContent = mb_substr($websiteContent, 0, 12000) . "\n...[contenuto troncato]";
                        }

                        // Costruisci system prompt con website content
                        $systemPrompt = (string) trans('enjoywork3d_prompts.instructions', ['locale' => $locale], $locale);
                        if (!empty($websiteContent)) {
                            $systemPrompt .= "\n\nPRIORITA': Contenuto dai siti web aziendali:\n\n{$websiteContent}";
                        }

                        // 3) Avvia run con strumenti
                        $run = $this->client->threads()->runs()->create(
                            threadId: $assistantThreadId,
                            parameters: [
                                'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                                'instructions' => $systemPrompt,
                                'model'  => 'gpt-4o',
                                'tools'  => [
                                    [ 'type' => 'function', 'function' => [ 'name' => 'getProductInfo', 'description' => 'Recupera informazioni sui prodotti, servizi, attività del menu tramite i loro nomi.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'product_names' => [ 'type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Nomi dei prodotti, servizi, attività da recuperare.' ] ], 'required' => [] ] ] ],
                                    [ 'type' => 'function', 'function' => [ 'name' => 'getAddressInfo', 'description' => 'Recupera informazioni sull\'indirizzo dell\'azienda, compreso indirizzo e numero di telefono.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'team_slug' => [ 'type' => 'string', 'description' => 'Slug del team per recuperare l\'indirizzo.' ] ], 'required' => ['team_slug'] ] ] ],
                                    [ 'type' => 'function', 'function' => [ 'name' => 'getAvailableTimes', 'description' => 'Recupera gli orari disponibili per un appuntamento.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'team_slug' => [ 'type' => 'string', 'description' => 'Slug del team per recuperare gli orari disponibili.' ] ], 'required' => ['team_slug'] ] ] ],
                                    [ 'type' => 'function', 'function' => [ 'name' => 'createOrder', 'description' => 'Crea un ordine con i dati forniti.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'user_phone' => [ 'type' => 'string', 'description' => 'Numero di telefono dell\'utente per la prenotazione.' ], 'delivery_date' => [ 'type' => 'string', 'description' => 'Data di consegna dell\'ordine, includendo ora, minuti e secondi di inizio.' ], 'product_ids' => [ 'type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'ID dei prodotti, servizi, attività da includere nell\'ordine.' ] ], 'required' => ['user_phone','delivery_date','product_ids'] ] ] ],
                                    [ 'type' => 'function', 'function' => [ 'name' => 'submitUserData', 'description' => 'Registra i dati anagrafici dell\'utente e risponde ringraziando. Dati trattati in conformità al GDPR.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'user_phone' => [ 'type' => 'string', 'description' => 'Numero di telefono dell\'utente' ], 'user_email' => [ 'type' => 'string', 'description' => 'Email dell\'utente' ], 'user_name' => [ 'type' => 'string', 'description' => 'Nome dell\'utente' ] ], 'required' => ['user_phone','user_email','user_name'] ] ] ],
                                    [ 'type' => 'function', 'function' => [ 'name' => 'getFAQs', 'description' => 'Recupera le domande frequenti (FAQ) dal sistema in base a una query.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'team_slug' => [ 'type' => 'string', 'description' => 'Slug del team per recuperare le FAQ.' ], 'query' => [ 'type' => 'string', 'description' => 'Query per cercare nelle FAQ.' ] ], 'required' => ['team_slug','query'] ] ] ],
                                    [ 'type' => 'function', 'function' => [ 'name' => 'fallback', 'description' => 'Risponde a domande non inerenti al contesto con il messaggio predefinito.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass() ] ] ],
                                ],
                            ]
                        );

                        // 4) Loop finché non termina o richiede azione
                        $pollDelayMs = 100;
                        $maxPollDelayMs = 500;
                        while (in_array($run->status, ['queued', 'in_progress', 'requires_action'])) {
                            if ($run->status === 'requires_action' && isset($run->requiredAction)) {
                                $toolCalls = $run->requiredAction->submitToolOutputs->toolCalls;
                                $toolOutputs = [];

                                foreach ($toolCalls as $toolCall) {
                                    $functionName = $toolCall->function->name;
                                    $arguments = json_decode($toolCall->function->arguments, true);
                                    Log::debug('assistants tool_call arguments raw', [
                                        'tool' => $functionName,
                                        'raw'  => $toolCall->function->arguments ?? null,
                                    ]);
                                    $arguments = $this->normalizeToolArgs($arguments, $functionName);
                                    $output = '';

                                    switch ($functionName) {
                                        case 'getProductInfo':
                                            $output = $this->fetchProductData($arguments['product_names'] ?? [], $teamSlug);
                                            break;
                                        case 'getAddressInfo':
                                            $output = $this->fetchAddressData($teamSlug);
                                            break;
                                        case 'getAvailableTimes':
                                            $output = $this->fetchAvailableTimes($teamSlug);
                                            break;
                                        case 'createOrder':
                                            $output = $this->createOrder($arguments['user_phone'] ?? null, $arguments['delivery_date'] ?? null, $arguments['product_ids'] ?? [], $teamSlug, $locale);
                                            break;
                                        case 'submitUserData':
                                            $output = $this->submitUserData($arguments['user_phone'] ?? null, $arguments['user_email'] ?? null, $arguments['user_name'] ?? null, $teamSlug, $locale, $activityUuid);
                                            break;
                                        case 'getFAQs':
                                            $output = $this->fetchFAQs($teamSlug, $arguments['query'] ?? '');
                                            break;
                                        case 'fallback':
                                            $output = ['message' => trans('enjoywork3d_prompts.fallback_message', [], $locale)];
                                            break;
                                    }

                                    $toolOutputs[] = [
                                        'tool_call_id' => $toolCall->id,
                                        'output'       => json_encode($output),
                                    ];
                                }

                                $run = $this->client->threads()->runs()->submitToolOutputs(
                                    threadId: $assistantThreadId,
                                    runId: $run->id,
                                    parameters: [ 'tool_outputs' => $toolOutputs ]
                                );
                                $pollDelayMs = 100;
                            }

                            usleep($pollDelayMs * 1000);
                            $pollDelayMs = min($pollDelayMs * 1.5, $maxPollDelayMs);
                            $run = $this->client->threads()->runs()->retrieve(threadId: $assistantThreadId, runId: $run->id);
                        }

                        // 5) Recupera messaggio finale e streamma
                        if ($run->status === 'completed') {
                            $messages = $this->client->threads()->messages()->list($assistantThreadId)->data;
                            $content = $messages[0]->content[0]->text->value ?? 'Nessuna risposta trovata.';

                            Quoter::create([
                                'thread_id' => $streamThreadId,
                                'role'      => 'chatbot',
                                'content'   => $content,
                            ]);

                            $this->streamTextByWord((string) $content, $flush);
                            $flush(['token' => ''], 'done');
                        } else {
                            Log::error('RealtimeChatWebsiteController.assistants run not completed', ['status' => $run->status]);
                            $flush(['error' => 'assistants_failed: '.$run->status], 'error');
                            $flush(['token' => ''], 'done');
                        }
                    } catch (\Throwable $e) {
                        Log::error('RealtimeChatWebsiteController.assistants error', ['error' => $e->getMessage()]);
                        $flush(['error' => 'assistants_exception: '.$e->getMessage()], 'error');
                        $flush(['token' => ''], 'done');
                    }
                }
            } catch (\Throwable $e) {
                Log::error('RealtimeChatWebsiteController.websiteStream error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'thread_id' => $streamThreadId,
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
     * Chat API STREAMING: analizza website content + contesto + tools con streaming nativo
     */
    private function analyzeAndStreamResponse(
        string $websiteContent,
        string $context,
        string $userInput,
        string $teamSlug,
        string $streamThreadId,
        string $locale,
        ?string $activityUuid,
        callable $flush
    ): void {
        try {
            // Taglia il contenuto se troppo lungo
            $maxContentLength = 12000;
            if (strlen($websiteContent) > $maxContentLength) {
                $websiteContent = mb_substr($websiteContent, 0, $maxContentLength) . "\n...[contenuto troncato]";
            }

            // Costruisci system prompt con website content
            $systemPrompt = (string) trans('enjoywork3d_prompts.instructions', ['locale' => $locale], $locale);
            
            if (!empty($websiteContent)) {
                $systemPrompt .= "\n\nPRIORITA': Contenuto dai siti web aziendali:\n\n{$websiteContent}";
            }

            if (!empty($context)) {
                $systemPrompt .= "\n\nContesto aggiuntivo per il team {$teamSlug}:\n{$context}";
            }

            // Ricostruisci history dal nostro storage per mantenere il contesto
            // Prendi gli ULTIMI 50 messaggi (DESC), poi riordina per cronologia (ASC)
            $historyRecords = Quoter::where('thread_id', $streamThreadId)
                ->orderBy('id', 'desc')
                ->limit(12)
                ->get(['id', 'role', 'content', 'created_at'])
                ->reverse();  // Riinverte per ordine cronologico crescente
            
            Log::info('RealtimeChatWebsiteController.analyzeAndStreamResponse history', [
                'thread_id' => $streamThreadId,
                'history_count' => $historyRecords->count(),
                'history_roles' => $historyRecords->pluck('role')->toArray(),
                'history_preview' => $historyRecords->map(fn($r) => [
                    'role' => $r->role,
                    'created_at' => $r->created_at,
                    'content' => mb_substr($r->content, 0, 100),
                ])->toArray(),
            ]);
            
            $historyMessages = [];
            if ($historyRecords->count() > 0) {
                $records = $historyRecords->toArray();
                
                // Rimuovi TUTTI i record che hanno role='user' e content uguale al messaggio attuale
                // (potrebbe esserci il messaggio appena inserito)
                $records = array_filter($records, function($r) use ($userInput) {
                    $isUserMsg = ($r['role'] ?? '') === 'user';
                    $isSameContent = (string)($r['content'] ?? '') === (string)$userInput;
                    return !($isUserMsg && $isSameContent);
                });
                
                // Riordina dopo il filter
                $records = array_values($records);
                
                foreach ($records as $r) {
                    $role = ($r['role'] ?? '') === 'chatbot' ? 'assistant' : 'user';
                    $content = (string)($r['content'] ?? '');
                    if ($content !== '') {
                        // Passa il content pulito senza timestamp a GPT
                        $historyMessages[] = [
                            'role' => $role,
                            'content' => $content
                        ];
                    }
                }
                
                Log::info('RealtimeChatWebsiteController.analyzeAndStreamResponse history filtered', [
                    'thread_id' => $streamThreadId,
                    'history_before_filter' => $historyRecords->count(),
                    'history_after_filter' => count($historyMessages),
                ]);
            }

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];
            if (!empty($historyMessages)) {
                $messages = array_merge($messages, $historyMessages);
            }
            $messages[] = ['role' => 'system', 'content' => "Parametri tool fissi:\nteam_slug={$teamSlug}"];
            $messages[] = ['role' => 'user', 'content' => (string) $userInput];

            // Definizione tools per function-calling (Chat Completions)
            $tools = [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'getProductInfo',
                        'description' => 'Recupera informazioni sui prodotti, servizi, attività del menu tramite i loro nomi.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'product_names' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Nomi dei prodotti, servizi, attività da recuperare.'
                                ],
                            ],
                            'required' => [],
                        ],
                    ],
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'getAddressInfo',
                        'description' => "Recupera informazioni sull'indirizzo dell'azienda, compreso indirizzo e numero di telefono.",
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'team_slug' => [ 'type' => 'string', 'description' => "Slug del team per recuperare l'indirizzo." ],
                            ],
                            'required' => ['team_slug'],
                        ],
                    ],
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'getAvailableTimes',
                        'description' => 'Recupera gli orari disponibili per un appuntamento.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [ 'team_slug' => [ 'type' => 'string', 'description' => 'Slug del team per recuperare gli orari disponibili.' ] ],
                            'required' => ['team_slug'],
                        ],
                    ],
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'createOrder',
                        'description' => 'Crea un ordine con i dati forniti.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'user_phone' => [ 'type' => 'string', 'description' => "Numero di telefono dell'utente per la prenotazione." ],
                                'delivery_date' => [ 'type' => 'string', 'description' => "Data di consegna dell'ordine, includendo ora, minuti e secondi di inizio." ],
                                'product_ids' => [ 'type' => 'array', 'items' => ['type' => 'integer'], 'description' => "ID dei prodotti, servizi, attività da includere nell'ordine." ],
                            ],
                            'required' => ['user_phone','delivery_date','product_ids'],
                        ],
                    ],
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'submitUserData',
                        'description' => "Registra i dati anagrafici dell'utente e risponde ringraziando. Dati trattati in conformità al GDPR.",
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'user_phone' => [ 'type' => 'string' ],
                                'user_email' => [ 'type' => 'string' ],
                                'user_name'  => [ 'type' => 'string' ],
                            ],
                            'required' => ['user_phone','user_email','user_name'],
                        ],
                    ],
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'getFAQs',
                        'description' => 'Recupera le domande frequenti (FAQ) dal sistema in base a una query.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [ 'team_slug' => [ 'type' => 'string' ], 'query' => [ 'type' => 'string' ] ],
                            'required' => ['team_slug','query'],
                        ],
                    ],
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'fallback',
                        'description' => 'Risponde a domande non inerenti al contesto con il messaggio predefinito.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => new \stdClass()
                        ],
                    ],
                ],
            ];

            $final = '';
            $capturedToolCalls = [];

            // STREAMING NATIVO Chat API
            $stream = $this->client->chat()->createStreamed([
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'messages' => $messages,
                'tools' => $tools,
                'tool_choice' => 'auto',
            ]);

            foreach ($stream as $response) {
                $delta = $response->choices[0]->delta ?? null;
                if ($delta === null) { continue; }

                // Token di testo
                $piece = $delta->content ?? null;
                if ($piece !== null && $piece !== '') {
                    $final .= $piece;
                    $flush(['token' => $piece]);
                }

                // Tool calls (supporta snake_case e camelCase)
                $toolCallsDelta = $delta->tool_calls ?? ($delta->toolCalls ?? null);
                if ($toolCallsDelta && is_array($toolCallsDelta)) {
                    foreach ($toolCallsDelta as $tc) {
                        $id = $tc->id ?? null;
                        $fn = $tc->function->name ?? null;
                        $argsPart = $tc->function->arguments ?? '';
                        if ($id && $fn !== null) {
                            if (!isset($capturedToolCalls[$id])) {
                                $capturedToolCalls[$id] = ['id' => $id, 'name' => $fn, 'arguments' => ''];
                            }
                            $capturedToolCalls[$id]['arguments'] .= $argsPart;
                        }
                    }
                }
            }

            // Se sono state richieste tool calls, esegui e poi prosegui con un secondo streaming
            if (!empty($capturedToolCalls)) {
                $assistantToolMessage = [
                    'role' => 'assistant',
                    'tool_calls' => array_values(array_map(function($c){
                        return [
                            'id' => $c['id'],
                            'type' => 'function',
                            'function' => [ 'name' => $c['name'], 'arguments' => $c['arguments'] ],
                        ];
                    }, $capturedToolCalls)),
                ];

                $toolOutputMessages = [];
                foreach ($capturedToolCalls as $call) {
                    $args = json_decode($call['arguments'] ?? '{}', true) ?: [];
                    $out = '';
                    
                    // LOG: Cosa stiamo ricevendo da GPT
                    Log::info('RealtimeChatWebsiteController.toolCall executed', [
                        'thread_id' => $streamThreadId,
                        'tool_name' => $call['name'],
                        'arguments' => $args,
                    ]);
                    
                    switch ($call['name']) {
                        case 'getProductInfo':
                            $out = $this->fetchProductData($args['product_names'] ?? [], $teamSlug);
                            break;
                        case 'getAddressInfo':
                            $out = $this->fetchAddressData($teamSlug);
                            break;
                        case 'getAvailableTimes':
                            $out = $this->fetchAvailableTimes($teamSlug);
                            break;
                        case 'createOrder':
                            $out = $this->createOrder($args['user_phone'] ?? null, $args['delivery_date'] ?? null, $args['product_ids'] ?? [], $teamSlug, $locale);
                            break;
                        case 'submitUserData':
                            $out = $this->submitUserData($args['user_phone'] ?? null, $args['user_email'] ?? null, $args['user_name'] ?? null, $teamSlug, $locale, $activityUuid);
                            break;
                        case 'getFAQs':
                            $out = $this->fetchFAQs($teamSlug, $args['query'] ?? '');
                            break;
                        case 'fallback':
                            $out = ['message' => trans('enjoywork3d_prompts.fallback_message', [], $locale)];
                            break;
                    }
                    $toolOutputMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $call['id'],
                        'content' => json_encode($out),
                    ];
                }

                // Prosegui con secondo streaming che produce il testo finale e streamma token
                $secondMessages = array_merge($messages, [$assistantToolMessage], $toolOutputMessages);
                $final2 = '';
                $stream2 = $this->client->chat()->createStreamed([
                    'model' => 'gpt-4o-mini',
                    'temperature' => 0.7,
                    'messages' => $secondMessages,
                ]);
                foreach ($stream2 as $response2) {
                    $piece2 = $response2->choices[0]->delta->content
                        ?? ($response2->choices[0]->message->content ?? null);
                    if ($piece2 !== null && $piece2 !== '') {
                        $final2 .= $piece2;
                        $flush(['token' => $piece2]);
                    }
                }

                $finalOut = $final2 !== '' ? $final2 : ($final !== '' ? $final : 'Mi dispiace, non sono riuscito a generare una risposta.');
                Quoter::create([
                    'thread_id' => $streamThreadId,
                    'role'      => 'chatbot',
                    'content'   => $finalOut,
                ]);
                $flush(['token' => ''], 'done');
                return;
            }

            // Nessuna tool call: abbiamo già streammato il testo
            $finalOut = $final !== '' ? $final : 'Mi dispiace, non sono riuscito a generare una risposta.';
            Quoter::create([
                'thread_id' => $streamThreadId,
                'role'      => 'chatbot',
                'content'   => $finalOut,
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

    private function streamTextByWord(string $text, callable $flusher): void
    {
        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($words as $w) {
            if ($w === '') { continue; }
            $flusher(['token' => $w]);
            usleep(28_000);
        }
    }

    private function buildContextForMessage(string $teamSlug, string $userInput, string $locale): string
    {
        try {
            $team = $this->getTeamCached($teamSlug);
            if (! $team) return '';

            // FAQ pertinenti
            $faq = $this->findFaqAnswer($teamSlug, $userInput);
            $faqText = $faq ? ("FAQ pertinente:\nQ: ".$faq['question']."\nA: ".$faq['answer']) : '';

            // Indirizzo/contatti
            $addressText = "Azienda: ".$team->name."\nIndirizzo: ".($team->address ?? '')."\nTelefono: ".($team->phone ?? '');

            // Prodotti/servizi candidati
            $products = Product::where('team_id', $team->id)
                ->where(function($q) use ($userInput) {
                    $q->where('name', 'like', '%'.$userInput.'%')
                      ->orWhere('description', 'like', '%'.$userInput.'%');
                })
                ->limit(5)->get(['name','price','description'])->toArray();
            $prodText = '';
            if (!empty($products)) {
                $lines = collect($products)->map(function($p){
                    $name = $p['name'] ?? '';
                    $price = $p['price'] ?? '';
                    $desc = mb_substr($p['description'] ?? '', 0, 120);
                    return $name.' - '.$price."\n".$desc;
                })->implode("\n\n");
                $prodText = "Prodotti/Servizi correlati:\n".$lines;
            }

            return trim($addressText."\n\n".$faqText."\n\n".$prodText);
        } catch (\Throwable $e) {
            Log::warning('buildContextForMessage error', ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function findFaqAnswer(?string $teamSlug, ?string $query): ?array
    {
        try {
            if (!$teamSlug || !$query || trim($query) === '') {
                return null;
            }

            $team = $this->getTeamCached($teamSlug);
            if (!$team) {
                return null;
            }

            $builder = Faq::where('team_id', $team->id)->where('active', true);

            $useEmbeddings = (bool) env('OPENAI_USE_EMBEDDINGS', false);
            $semanticThreshold = (float) env('FAQ_SEMANTIC_THRESHOLD', 0.85);
            $lexicalThreshold = (float) env('FAQ_LEXICAL_THRESHOLD', 0.60);

            $candidates = collect();
            try {
                $candidates = $builder
                    ->select(['question', 'answer'])
                    ->selectRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance', [$query])
                    ->whereRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
                    ->orderByDesc('relevance')
                    ->limit(5)
                    ->get();
            } catch (\Throwable $e) {
                $candidates = $builder
                    ->where(function ($q) use ($query) {
                        $q->where('question', 'like', '%'.$query.'%')
                          ->orWhere('answer', 'like', '%'.$query.'%');
                    })
                    ->limit(5)
                    ->get(['question', 'answer']);
            }

            if ($candidates->isEmpty()) {
                return null;
            }

            $best = null; $bestScore = -1.0;
            foreach ($candidates as $faq) {
                $q = (string) $faq->question;
                $a = (string) $faq->answer;
                $scoreQuestion = $useEmbeddings ? ($this->tryEmbeddingSimilarity($query, $q) ?? $this->computeLexicalSimilarity($query, $q)) : $this->computeLexicalSimilarity($query, $q);
                $scoreAnswer   = $useEmbeddings ? ($this->tryEmbeddingSimilarity($query, $a) ?? $this->computeLexicalSimilarity($query, $a)) : $this->computeLexicalSimilarity($query, $a);
                $score = max($scoreQuestion * 0.7 + $scoreAnswer * 0.3, $scoreQuestion);
                if ($score > $bestScore) { $bestScore = $score; $best = $faq; }
            }

            $threshold = $useEmbeddings ? $semanticThreshold : $lexicalThreshold;
            if ($best && $bestScore >= $threshold) {
                return $best->only(['question', 'answer']);
            }
            return null;
        } catch (\Throwable $e) {
            Log::error('RealtimeChatWebsiteController.findFaqAnswer error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function computeLexicalSimilarity(string $textA, string $textB): float
    {
        $tokensA = $this->tokenizeAndFilter($textA);
        $tokensB = $this->tokenizeAndFilter($textB);
        if (empty($tokensA) || empty($tokensB)) { return 0.0; }
        $setA = array_unique($tokensA); $setB = array_unique($tokensB);
        $intersection = array_values(array_intersect($setA, $setB));
        $union = array_values(array_unique(array_merge($setA, $setB)));
        $jaccard = count($union) > 0 ? count($intersection) / count($union) : 0.0;
        $containment = min(count($setA), count($setB)) > 0 ? count($intersection) / min(count($setA), count($setB)) : 0.0;
        $score = 0.5 * $jaccard + 0.5 * $containment;
        return max(0.0, min(1.0, $score));
    }

    private function tokenizeAndFilter(string $text): array
    {
        $normalized = mb_strtolower($text);
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized);
        $parts = preg_split('/\s+/', trim($normalized));
        $stopwords = $this->getItalianStopwords();
        $filtered = [];
        foreach ($parts as $tok) {
            if ($tok === '' || in_array($tok, $stopwords, true)) { continue; }
            if (mb_strlen($tok) >= 4) { $filtered[] = $tok; }
        }
        return $filtered;
    }

    private function getItalianStopwords(): array
    {
        return [
            'a','ad','al','allo','alla','ai','agli','alle','anche','avere','da','dal','dallo','dalla','dai','dagli','dalle','dei','degli','delle','del','dell','dello','della',
            'di','e','ed','che','chi','con','col','coi','come','dove','dunque','era','erano','essere','faccio','fai','fa','fanno','fate','fatto','fui','fu','furono','gli','il','lo','la','i','le',
            'in','nel','nello','nella','nei','negli','nelle','ma','mi','mia','mie','miei','mio','ne','non','o','od','per','perché','più','poi','quale','quali','qual','quanta','quanto','quanti','quante',
            'quasi','questo','questa','questi','queste','quello','quella','quelli','quelle','se','sei','si','sì','sia','siamo','siete','sono','su','sul','sullo','sulla','sui','sugli','sulle','tra','fra',
            'tu','tua','tue','tuo','tutti','tutte','tutto','un','uno','una','uno','va','vai','vado','vanno','voi','vostro','vostra','vostri','vostre','io','loro','noi','voi','dite','sono','buongiorno'
        ];
    }

    private function tryEmbeddingSimilarity(string $textA, string $textB): ?float
    {
        try {
            if (!$this->embeddingService) {
                return null;
            }
            return $this->embeddingService->textSimilarity($textA, $textB);
        } catch (\Throwable $e) {
            Log::warning('RealtimeChatWebsiteController.tryEmbeddingSimilarity fallback', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ====== Helper Methods ======

    private function fetchProductData(array $productNames, $teamSlug)
    {
        Log::info('fetchProductData', ['productNames' => $productNames, 'teamSlug' => $teamSlug]);
        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            return [];
        }
        $query = Product::where('team_id', $team->id);
        if (!empty($productNames)) {
            $query->where(function ($q) use ($productNames) {
                foreach ($productNames as $name) {
                    $q->orWhere('name', 'like', '%'.$name.'%');
                }
            });
        }
        return $query->get()->toArray();
    }

    private function fetchAddressData($teamSlug)
    {
        $team = $this->getTeamCached($teamSlug);
        return $team ? $team->toArray() : [];
    }

    private function fetchAvailableTimes($teamSlug)
    {
        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            return [];
        }
        return Event::where('team_id', $team->id)
            ->where('name', 'Disponibile')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc')
            ->get(['starts_at', 'ends_at', 'name', 'featured_image_id', 'description'])
            ->toArray();
    }

    private function createOrder($userPhone, $deliveryDate, $productIds, $teamSlug, string $locale)
    {
        Log::info('createOrder called', [
            'user_phone' => $userPhone,
            'delivery_date' => $deliveryDate,
            'product_ids' => $productIds,
            'team_slug' => $teamSlug,
        ]);

        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            Log::error('createOrder: Team not found', ['team_slug' => $teamSlug]);
            return ['error' => 'Team non trovato'];
        }

        // Validazione: verifica che almeno phone e date siano presenti
        if (!$userPhone || !$deliveryDate) {
            Log::warning('createOrder: Missing required fields', [
                'phone' => $userPhone,
                'delivery_date' => $deliveryDate,
            ]);
            return [
                'error' => 'Mancano informazioni necessarie per l\'ordine: numero di telefono e data di consegna obbligatori.',
                'received' => [
                    'phone' => $userPhone,
                    'delivery_date' => $deliveryDate,
                    'product_ids_count' => count((array)$productIds),
                ],
            ];
        }

        try {
            $order = new Order();
            $order->team_id = $team->id;
            $order->delivery_date = $deliveryDate;
            $order->phone = $userPhone;
            $order->save();

            Log::info('createOrder: Order created', [
                'order_id' => $order->id,
                'team_id' => $team->id,
            ]);

            if (!empty($productIds)) {
                $order->products()->attach($productIds);
                Log::info('createOrder: Products attached', [
                    'order_id' => $order->id,
                    'product_count' => count($productIds),
                ]);
            }

            return [
                'order_id' => $order->id,
                'message' => trans('enjoywork3d_prompts.order_created_successfully', [], $locale),
                'details' => [
                    'phone' => $userPhone,
                    'delivery_date' => $deliveryDate,
                    'products' => count((array)$productIds),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('createOrder: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'error' => 'Errore durante la creazione dell\'ordine: ' . $e->getMessage(),
            ];
        }
    }

    private function submitUserData($userPhone, $userEmail, $userName, $teamSlug, string $locale, ?string $activityUuid)
    {
        Log::info('submitUserData called', [
            'user_phone' => $userPhone,
            'user_email' => $userEmail,
            'user_name' => $userName,
            'team_slug' => $teamSlug,
            'activity_uuid' => $activityUuid,
        ]);

        // Validazione
        if (!$userPhone || !$userEmail || !$userName) {
            Log::warning('submitUserData: Missing required fields', [
                'phone' => $userPhone,
                'email' => $userEmail,
                'name' => $userName,
            ]);
            return [
                'error' => 'Mancano informazioni anagrafiche: nome, email e telefono obbligatori.',
                'received' => [
                    'phone' => $userPhone,
                    'email' => $userEmail,
                    'name' => $userName,
                ],
            ];
        }

        try {
            if ($activityUuid) {
                $customer = Customer::where('uuid', $activityUuid)->first();
                if ($customer) {
                    $customer->phone = $userPhone;
                    $customer->email = $userEmail;
                    $customer->name = $userName;
                    $customer->save();
                    Log::info('submitUserData: Updated existing customer', [
                        'customer_id' => $customer->id,
                    ]);
                } else {
                    $team = $this->getTeamCached($teamSlug);
                    if (!$team) {
                        Log::error('submitUserData: Team not found', ['team_slug' => $teamSlug]);
                        return ['error' => 'Team non trovato'];
                    }
                    $customer = Customer::create([
                        'uuid' => $activityUuid,
                        'phone' => $userPhone,
                        'email' => $userEmail,
                        'name' => $userName,
                        'team_id' => $team->id,
                    ]);
                    Log::info('submitUserData: Created new customer', [
                        'customer_id' => $customer->id,
                    ]);
                }
            } else {
                $team = $this->getTeamCached($teamSlug);
                if (!$team) {
                    Log::error('submitUserData: Team not found', ['team_slug' => $teamSlug]);
                    return ['error' => 'Team non trovato'];
                }
                $customer = Customer::create([
                    'phone' => $userPhone,
                    'email' => $userEmail,
                    'name' => $userName,
                    'team_id' => $team->id,
                ]);
                Log::info('submitUserData: Created new customer (no uuid)', [
                    'customer_id' => $customer->id,
                ]);
            }

            return [
                'success' => true,
                'message' => trans('enjoywork3d_prompts.user_data_submitted', [], $locale),
                'customer_name' => $userName,
            ];
        } catch (\Throwable $e) {
            Log::error('submitUserData: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'error' => 'Errore durante il salvataggio dei dati: ' . $e->getMessage(),
            ];
        }
    }

    private function fetchFAQs($teamSlug, $query)
    {
        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            return [];
        }
        try {
            $faqs = Faq::where('team_id', $team->id)
                ->whereRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
                ->get(['question', 'answer'])
                ->toArray();
        } catch (\Throwable $e) {
            $faqs = Faq::where('team_id', $team->id)
                ->where(function ($q) use ($query) {
                    $q->where('question', 'like', '%'.$query.'%')
                      ->orWhere('answer', 'like', '%'.$query.'%');
                })
                ->get(['question', 'answer'])
                ->toArray();
        }
        return $faqs;
    }

    private function normalizeToolArgs($args, string $functionName): array
    {
        if (!is_array($args)) {
            return [];
        }

        $normalized = [];
        foreach ($args as $key => $value) {
            $k = $key;
            if ($k === 'userPhone') { $k = 'user_phone'; }
            if ($k === 'userEmail') { $k = 'user_email'; }
            if ($k === 'userName')  { $k = 'user_name'; }
            if ($k === 'deliveryDate') { $k = 'delivery_date'; }
            if ($k === 'productIds') { $k = 'product_ids'; }
            if ($k === 'teamSlug') { $k = 'team_slug'; }
            $normalized[$k] = $value;
        }

        if ($functionName === 'createOrder' || $functionName === null) {
            if (isset($normalized['product_ids']) && is_array($normalized['product_ids'])) {
                $normalized['product_ids'] = array_values(array_map(function ($v) { return (int) $v; }, $normalized['product_ids']));
            }
            if (isset($normalized['user_phone'])) {
                $normalized['user_phone'] = (string) $normalized['user_phone'];
            }
            if (isset($normalized['delivery_date'])) {
                $normalized['delivery_date'] = (string) $normalized['delivery_date'];
            }
        }

        if ($functionName === 'submitUserData' || $functionName === null) {
            foreach (['user_phone','user_email','user_name'] as $k) {
                if (isset($normalized[$k])) {
                    $normalized[$k] = (string) $normalized[$k];
                }
            }
        }

        return $normalized;
    }
}
