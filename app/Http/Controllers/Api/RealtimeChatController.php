<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\Quoter;
use App\Models\Team;
use App\Models\Product;
use App\Models\Event;
use App\Models\Order;
use App\Services\EmbeddingCacheService;
use App\Services\WebsiteScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeChatController extends Controller
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
     * Endpoint SSE: restituisce token in streaming per risposta a bassa latenza.
     * GET /api/chatbot/stream?message=...&team=...&uuid=...&locale=it
     */
    public function stream(Request $request)
    {
        $userInput = (string) $request->query('message', '');
        $teamSlug  = (string) $request->query('team', '');
        $activityUuid = $request->query('uuid');
        $locale   = (string) $request->query('locale', 'it');

        $response = new StreamedResponse(function () use ($userInput, $teamSlug, $activityUuid, $locale) {
            $flush = function (array $payload, string $event = 'message') {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();
            };

            // Riusa thread_id se fornito dal client, altrimenti genera uno nuovo e restituiscilo
            $streamThreadId = (string) (request()->query('thread_id') ?: str()->uuid());
            $flush(['token' => json_encode(['thread_id' => $streamThreadId])]);

            $flush(['status' => 'started']);

            if (trim($userInput) === '') {
                $flush(['token' => ''], 'done');
                return;
            }

            try {
                // Log e persistenza messaggio utente
                Quoter::create([
                    'thread_id' => $streamThreadId,
                    'role'      => 'user',
                    'content'   => $userInput,
                ]);

                // Greeting immediato
                if (mb_strtolower($userInput) === mb_strtolower(trans('enjoy-work.greeting', [], $locale))) {
                    $team = $this->getTeamCached($teamSlug);
                    $welcomeMessage = $team ? (string) $team->welcome_message : (string) trans('enjoy-work.welcome_message_fallback', [], $locale);
                    $this->streamTextByWord($welcomeMessage, $flush);

                    Quoter::create([
                        'thread_id' => $streamThreadId,
                        'role'      => 'chatbot',
                        'content'   => $welcomeMessage,
                    ]);

                    $flush(['token' => ''], 'done');
                    return;
                }

                // **NUOVA FASE: Scrapa il sito web del team e prova a rispondere da lì**
                $team = $this->getTeamCached($teamSlug);
                $websiteAnswer = null;
                if ($team && $team->website) {
                    $websiteContent = $this->scraperService->scrapeTeamWebsite((string) $team->website, (string) $team->id);
                    if ($websiteContent) {
                        $websiteAnswer = $this->scraperService->analyzeWebsiteContent($websiteContent, $userInput, $locale);
                        if ($websiteAnswer && stripos($websiteAnswer, 'non è disponibile') === false && trim($websiteAnswer) !== '') {
                            // Se trova una risposta nel sito, usala
                            $this->streamTextByWord($websiteAnswer, $flush);

                            Quoter::create([
                                'thread_id' => $streamThreadId,
                                'role'      => 'chatbot',
                                'content'   => $websiteAnswer,
                            ]);

                            $flush(['token' => ''], 'done');
                            return;
                        }
                    }
                }

                // FAQ match rapido
                $faq = $this->findFaqAnswer($teamSlug, $userInput);
                if ($faq) {
                    $answer = (string) $faq['answer'];
                    $this->streamTextByWord($answer, $flush);

                    Quoter::create([
                        'thread_id' => $streamThreadId,
                        'role'      => 'chatbot',
                        'content'   => $answer,
                    ]);

                    $flush(['token' => ''], 'done');
                    return;
                }

                // Chiamata LLM con contesto operativo del team
                $context = $this->buildContextForMessage($teamSlug, $userInput, $locale);

                // Modalità: per default usa streaming nativo Chat Completions; se ?assistants=1 oppure esiste assistant_thread_id
                $incomingAssistantThreadId = (string) (request()->query('assistant_thread_id') ?? '');
                $useAssistants = (bool) request()->query('assistants', false) || ($incomingAssistantThreadId !== '');
                Log::debug('chat stream mode decision', [
                    'useAssistants' => $useAssistants,
                    'incomingAssistantThreadId' => $incomingAssistantThreadId,
                    'streamThreadId' => $streamThreadId,
                    'teamSlug' => $teamSlug,
                ]);

                if (! $useAssistants) {
                    try {
                        // Ricostruisci breve history dal nostro storage per mantenere il contesto nelle Chat Completions
                        $historyRecords = Quoter::where('thread_id', $streamThreadId)
                            ->orderBy('id', 'asc')
                            ->select(['id', 'role', 'content'])
                            ->limit(12)
                            ->get();
                        $historyMessages = [];
                        if ($historyRecords->count() > 0) {
                            // Evita di duplicare l'ultimo messaggio utente appena inserito
                            $records = $historyRecords->toArray();
                            $last = end($records);
                            if ($last && ($last['role'] ?? '') === 'user' && (string) ($last['content'] ?? '') === (string) $userInput) {
                                array_pop($records);
                            }
                            foreach ($records as $r) {
                                $role = ($r['role'] ?? '') === 'chatbot' ? 'assistant' : 'user';
                                $content = (string) ($r['content'] ?? '');
                                if ($content !== '') {
                                    $historyMessages[] = ['role' => $role, 'content' => $content];
                                }
                            }
                        }

                        Log::debug('chat history built', [
                            'thread_id' => $streamThreadId,
                            'history_count' => count($historyMessages),
                        ]);

                        $messages = [
                            ['role' => 'system', 'content' => (string) trans('enjoywork3d_prompts.instructions', ['locale' => $locale], $locale)],
                        ];
                        if ($context) {
                            $messages[] = ['role' => 'system', 'content' => "Contesto per il team {$teamSlug}:\n{$context}"];
                        }
                        // Suggerisci parametri fissi per i tool (aiuta il modello a compilare gli argomenti)
                        $messages[] = ['role' => 'system', 'content' => "Parametri tool fissi:\nteam_slug={$teamSlug}"];
                        // Regole rigide tool-calling
                        $messages[] = ['role' => 'system', 'content' => (
                            'Regole tool-calling (rigide):\n'.
                            '- Usa i tool quando l\'utente vuole compiere un\'azione.\n'.
                            '- Non dichiarare mai esiti (es. ordine creato) senza output del tool.\n'.
                            '- Non chiamare createOrder finché non hai: user_phone, delivery_date (data+ora), product_ids.\n'.
                            '- Se manca un dato, chiedilo esplicitamente prima di chiamare il tool.\n'.
                            '- Quando chiami un tool, fornisci tutti i campi required in snake_case.'
                        )];
                        if (!empty($historyMessages)) {
                            $messages = array_merge($messages, $historyMessages);
                        }
                        $messages[] = ['role' => 'user', 'content' => (string) $userInput];

                        // Log anteprima messaggi (senza testi lunghi)
                        try {
                            $preview = array_map(function ($m) {
                                $c = (string) ($m['content'] ?? '');
                                $len = mb_strlen($c);
                                return [
                                    'role' => $m['role'] ?? '',
                                    'content_preview' => mb_substr($c, 0, 180) . ($len > 180 ? '…' : ''),
                                    'content_len' => $len,
                                ];
                            }, $messages);
                            Log::debug('chat completion messages preview', ['messages_preview' => $preview]);
                        } catch (\Throwable $e) {
                            Log::debug('chat completion messages preview failed', ['error' => $e->getMessage()]);
                        }

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
                                    'name' => 'scrapeSite',
                                    'description' => "Recupera il contenuto del sito web del cliente per rispondere a domande sull'attività.",
                                    'parameters' => [
                                        'type' => 'object',
                                        'properties' => [ 'user_uuid' => [ 'type' => 'string' ] ],
                                        'required' => ['user_uuid'],
                                    ],
                                ],
                            ],
                        ];

                        // Log riepilogo tools
                        try {
                            $toolSummary = array_map(function ($t) {
                                $fn = $t['function'] ?? [];
                                $params = $fn['parameters']['properties'] ?? [];
                                $required = $fn['parameters']['required'] ?? [];
                                return [
                                    'name' => $fn['name'] ?? '',
                                    'required' => $required,
                                    'properties' => array_keys($params),
                                ];
                            }, $tools);
                            Log::debug('chat completion tools summary', ['tools' => $toolSummary]);
                        } catch (\Throwable $e) {
                            Log::debug('chat completion tools summary failed', ['error' => $e->getMessage()]);
                        }

                        $final = '';
                        $capturedToolCalls = [];

                        $stream = $this->client->chat()->createStreamed([
                            'model' => 'gpt-4o-mini',
                            'temperature' => 0.2,
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

                            // In alcuni SDK l'ultimo frame contiene i tool_calls completi nel message (non in delta)
                            $finalMessage = $response->choices[0]->message ?? null;
                            if ($finalMessage) {
                                $messageToolCalls = $finalMessage->tool_calls ?? ($finalMessage->toolCalls ?? null);
                                if ($messageToolCalls && is_array($messageToolCalls)) {
                                    foreach ($messageToolCalls as $tc) {
                                        $id = $tc->id ?? null;
                                        $fn = $tc->function->name ?? null;
                                        $argsFull = $tc->function->arguments ?? '';
                                        Log::debug('stream message tool_call summary', [
                                            'tool' => $fn,
                                            'args_len' => is_string($argsFull) ? mb_strlen($argsFull) : 0,
                                        ]);
                                        if ($id && $fn !== null) {
                                            if (!isset($capturedToolCalls[$id])) {
                                                $capturedToolCalls[$id] = ['id' => $id, 'name' => $fn, 'arguments' => ''];
                                            }
                                            // Sovrascrive con la versione completa se presente
                                            if ($argsFull !== '') {
                                                $capturedToolCalls[$id]['arguments'] = $argsFull;
                                            }
                                        }
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
                                if (($call['arguments'] ?? '') === '') {
                                    Log::debug('stream tool_call arguments missing; proceeding empty', [
                                        'tool' => $call['name'] ?? null,
                                        'call_id' => $call['id'] ?? null,
                                    ]);
                                }
                                Log::debug('stream tool_call arguments raw', [
                                    'tool' => $call['name'] ?? null,
                                    'raw'  => $call['arguments'] ?? null,
                                ]);
                                $args = $this->normalizeToolArgs($args, $call['name'] ?? null);
                                $out = '';
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
                                        $missing = [];
                                        foreach (['user_phone','delivery_date','product_ids'] as $rk) {
                                            if (!isset($args[$rk]) || ($rk === 'product_ids' && empty($args[$rk])) || ($rk !== 'product_ids' && trim((string)($args[$rk] ?? '')) === '')) {
                                                $missing[] = $rk;
                                            }
                                        }
                                        if (!empty($missing)) {
                                            $out = ['error' => 'missing_required_fields', 'missing' => $missing];
                                        } else {
                                            $out = $this->createOrder($args['user_phone'] ?? null, $args['delivery_date'] ?? null, $args['product_ids'] ?? [], $teamSlug, $locale);
                                        }
                                        break;
                                    case 'submitUserData':
                                        $out = $this->submitUserData($args['user_phone'] ?? null, $args['user_email'] ?? null, $args['user_name'] ?? null, $teamSlug, $locale, $activityUuid);
                                        break;
                                    case 'getFAQs':
                                        $out = $this->fetchFAQs($teamSlug, $args['query'] ?? '');
                                        break;
                                    case 'scrapeSite':
                                        $out = $this->scrapeSite($activityUuid);
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
                                'temperature' => 0.2,
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
                        return;
                    } catch (\Throwable $e) {
                        Log::warning('RealtimeChatController.stream (native streaming) failed, falling back to assistants', ['error' => $e->getMessage()]);
                        // Se fallisce lo streaming nativo, continua con Assistants API sotto
                    }
                }

                // Flusso Assistants API con tools; output streammato parola-per-parola
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

                    // 3) Avvia run con strumenti
                    $run = $this->client->threads()->runs()->create(
                        threadId: $assistantThreadId,
                        parameters: [
                            'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                            'instructions' => trans('enjoywork3d_prompts.instructions', ['locale' => $locale], $locale),
                            'model'  => 'gpt-4o',
                            'tools'  => [
                                [ 'type' => 'function', 'function' => [ 'name' => 'getProductInfo', 'description' => 'Recupera informazioni sui prodotti, servizi, attività del menu tramite i loro nomi.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'product_names' => [ 'type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Nomi dei prodotti, servizi, attività da recuperare.' ] ], 'required' => [] ] ] ],
                                [ 'type' => 'function', 'function' => [ 'name' => 'getAddressInfo', 'description' => 'Recupera informazioni sull\'indirizzo dell\'azienda, compreso indirizzo e numero di telefono.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'team_slug' => [ 'type' => 'string', 'description' => 'Slug del team per recuperare l\'indirizzo.' ] ], 'required' => ['team_slug'] ] ] ],
                                [ 'type' => 'function', 'function' => [ 'name' => 'getAvailableTimes', 'description' => 'Recupera gli orari disponibili per un appuntamento.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'team_slug' => [ 'type' => 'string', 'description' => 'Slug del team per recuperare gli orari disponibili.' ] ], 'required' => ['team_slug'] ] ] ],
                                [ 'type' => 'function', 'function' => [ 'name' => 'createOrder', 'description' => 'Crea un ordine con i dati forniti.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'user_phone' => [ 'type' => 'string', 'description' => 'Numero di telefono dell\'utente per la prenotazione.' ], 'delivery_date' => [ 'type' => 'string', 'description' => 'Data di consegna dell\'ordine, includendo ora, minuti e secondi di inizio.' ], 'product_ids' => [ 'type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'ID dei prodotti, servizi, attività da includere nell\'ordine.' ] ], 'required' => ['user_phone','delivery_date','product_ids'] ] ] ],
                                [ 'type' => 'function', 'function' => [ 'name' => 'submitUserData', 'description' => 'Registra i dati anagrafici dell\'utente e risponde ringraziando. Dati trattati in conformità al GDPR.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'user_phone' => [ 'type' => 'string', 'description' => 'Numero di telefono dell\'utente' ], 'user_email' => [ 'type' => 'string', 'description' => 'Email dell\'utente' ], 'user_name' => [ 'type' => 'string', 'description' => 'Nome dell\'utente' ] ], 'required' => ['user_phone','user_email','user_name'] ] ] ],
                                [ 'type' => 'function', 'function' => [ 'name' => 'getFAQs', 'description' => 'Recupera le domande frequenti (FAQ) dal sistema in base a una query.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'team_slug' => [ 'type' => 'string', 'description' => 'Slug del team per recuperare le FAQ.' ], 'query' => [ 'type' => 'string', 'description' => 'Query per cercare nelle FAQ.' ] ], 'required' => ['team_slug','query'] ] ] ],
                                [ 'type' => 'function', 'function' => [ 'name' => 'fallback', 'description' => 'Risponde a domande non inerenti al contesto con il messaggio predefinito.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass() ] ] ],
                                [ 'type' => 'function', 'function' => [ 'name' => 'scrapeSite', 'description' => 'Recupera il contenuto del sito web del cliente per rispondere a domande sull\'attività.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'user_uuid' => [ 'type' => 'string', 'description' => 'UUID che identifica univocamente l\'attività del cliente.' ] ], 'required' => ['user_uuid'] ] ] ],
                            ],
                        ]
                    );

                    // 4) Loop finché non termina o richiede azione
                    $pollDelayMs = 100; // Inizio con 100ms
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
                                    case 'scrapeSite':
                                        $output = $this->scrapeSite($activityUuid);
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
                            // Reset delay dopo tool call completato
                            $pollDelayMs = 100;
                        }

                        usleep($pollDelayMs * 1000); // Converti ms in microsecondi
                        $pollDelayMs = min($pollDelayMs * 1.5, $maxPollDelayMs); // Backoff esponenziale: 100 -> 150 -> 225 -> 338 -> 500
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
                        Log::error('RealtimeChatController.stream Assistants run not completed', ['status' => $run->status]);
                        $flush(['error' => 'assistants_failed: '.$run->status], 'error');
                        $flush(['token' => ''], 'done');
                    }
                } catch (\Throwable $e) {
                    Log::error('RealtimeChatController.stream (assistants) error', ['error' => $e->getMessage()]);
                    $flush(['error' => 'assistants_exception: '.$e->getMessage()], 'error');
                    $flush(['token' => ''], 'done');
                }
            } catch (\Throwable $e) {
                Log::error('RealtimeChatController.stream error (outer)', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $flush(['error' => 'streaming_failed_outer: '.$e->getMessage()], 'error');
                $flush(['token' => ''], 'done');
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    private function streamTextByWord(string $text, callable $flusher): void
    {
        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($words as $w) {
            $trimmed = $w;
            if ($trimmed === '') {
                continue;
            }
            $flusher(['token' => $trimmed]);
            usleep(28_000); // ~35 tokens/sec percepiti (tunable)
        }
    }

    private function splitIntoChunks(string $text): array
    {
        // suddivide per frasi brevi per streaming più fluido
        $out = [];
        $pattern = '/([^.!?\n]{25,}[.!?]+)\s+/u';
        while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE)) {
            $out[] = $m[1][0];
            $text = mb_substr($text, $m[0][1] + mb_strlen($m[0][0]));
        }
        if (trim($text) !== '') $out[] = $text;
        return $out;
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

            // Prodotti/servizi candidati per nome (heuristic)
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
            Log::error('RealtimeChatController.findFaqAnswer error', ['error' => $e->getMessage()]);
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
            Log::warning('RealtimeChatController.tryEmbeddingSimilarity fallback', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        return EmbeddingCacheService::cosineSimilarity($a, $b);
    }

    private function formatResponseContent(string $content): string
    {
        $formattedContent = nl2br($content);
        $formattedContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedContent);
        $formattedContent = preg_replace('/(\d+\.\s)/', '<strong>$1</strong>', $formattedContent);
        return $formattedContent;
    }

    /**
     * Normalizza le chiavi degli argomenti dei tool (camelCase -> snake_case) e applica una tipizzazione leggera
     */
    private function normalizeToolArgs($args, ?string $functionName = null): array
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

    // ====== Metodi helper copiati da ChatbotController ======


    private function fetchProductData(array $productNames, $teamSlug)
    {
        Log::info('fetchProductData: Inizio recupero dati prodotti', [
            'productNames' => $productNames,
            'teamSlug'     => $teamSlug,
        ]);

        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            Log::error('fetchProductData: Team non trovato', ['teamSlug' => $teamSlug]);
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

        $products = $query->get()->toArray();
        Log::info('fetchProductData: Dati prodotti e servizi finali', ['products' => $products]);
        return $products;
    }

    private function fetchAddressData($teamSlug)
    {
        Log::info('fetchAddressData: Inizio recupero dati indirizzo', ['teamSlug' => $teamSlug]);
        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            Log::error('fetchAddressData: Team non trovato', ['teamSlug' => $teamSlug]);
            return [];
        }
        Log::info('fetchAddressData: Dati indirizzo ricevuti', ['addressData' => $team->toArray()]);
        return $team->toArray();
    }

    private function fetchAvailableTimes($teamSlug)
    {
        Log::info('fetchAvailableTimes: Inizio recupero orari disponibili', ['teamSlug' => $teamSlug]);
        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            Log::error('fetchAvailableTimes: Team non trovato', ['teamSlug' => $teamSlug]);
            return [];
        }
        $events = Event::where('team_id', $team->id)
            ->where('name', 'Disponibile')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc')
            ->get(['starts_at', 'ends_at', 'name', 'featured_image_id', 'description'])
            ->toArray();
        Log::info('fetchAvailableTimes: Orari disponibili ricevuti', ['availableTimes' => $events]);
        return $events;
    }

    private function createOrder($userPhone, $deliveryDate, $productIds, $teamSlug, string $locale)
    {
        Log::info('createOrder: Inizio creazione ordine', [
            'userPhone'    => $userPhone,
            'deliveryDate' => $deliveryDate,
            'productIds'   => $productIds,
            'teamSlug'     => $teamSlug,
        ]);
        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            Log::error('createOrder: Team non trovato', ['teamSlug' => $teamSlug]);
            return ['error' => 'Team non trovato'];
        }

        $order = new Order();
        $order->team_id = $team->id;
        $order->delivery_date = $deliveryDate;
        $order->phone = $userPhone;
        $order->save();

        if (!empty($productIds)) {
            $order->products()->attach($productIds);
        }

        $orderData = [
            'order_id' => $order->id,
            'message'  => trans('enjoywork3d_prompts.order_created_successfully', [], $locale),
        ];
        Log::info('createOrder: Ordine creato', ['orderData' => $orderData]);
        return $orderData;
    }

    private function submitUserData($userPhone, $userEmail, $userName, $teamSlug, string $locale, ?string $activityUuid)
    {
        Log::info('submitUserData: Inizio salvataggio dati utente', [
            'userPhone' => $userPhone,
            'userEmail' => $userEmail,
            'userName'  => $userName,
            'teamSlug'  => $teamSlug,
            'uuid'      => $activityUuid
        ]);

        if ($activityUuid) {
            $customer = Customer::where('uuid', $activityUuid)->first();
            if ($customer) {
                $customer->phone = $userPhone;
                $customer->email = $userEmail;
                $customer->name = $userName;
                $customer->save();
            } else {
                $team = $this->getTeamCached($teamSlug);
                if (!$team) {
                    Log::error('submitUserData: Team non trovato', ['teamSlug' => $teamSlug]);
                    return ['error' => 'Team non trovato'];
                }
                Customer::create([
                    'uuid' => $activityUuid,
                    'phone' => $userPhone,
                    'email' => $userEmail,
                    'name' => $userName,
                    'team_id' => $team->id,
                ]);
            }
        } else {
            $team = $this->getTeamCached($teamSlug);
            if (!$team) {
                Log::error('submitUserData: Team non trovato', ['teamSlug' => $teamSlug]);
                return ['error' => 'Team non trovato'];
            }
            Customer::create([
                'phone' => $userPhone,
                'email' => $userEmail,
                'name' => $userName,
                'team_id' => $team->id,
            ]);
        }

        return trans('enjoywork3d_prompts.user_data_submitted', [], $locale);
    }

    private function fetchFAQs($teamSlug, $query)
    {
        Log::info('fetchFAQs: Inizio recupero FAQ', ['teamSlug' => $teamSlug, 'query' => $query]);
        $team = $this->getTeamCached($teamSlug);
        if (!$team) {
            Log::error('fetchFAQs: Team non trovato', ['teamSlug' => $teamSlug]);
            return [];
        }
        try {
            $faqs = Faq::where('team_id', $team->id)
                ->whereRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
                ->get(['question', 'answer'])
                ->toArray();
        } catch (\Throwable $e) {
            // Fallback LIKE se FULLTEXT non disponibile
            $faqs = Faq::where('team_id', $team->id)
                ->where(function ($q) use ($query) {
                    $q->where('question', 'like', '%'.$query.'%')
                      ->orWhere('answer', 'like', '%'.$query.'%');
                })
                ->get(['question', 'answer'])
                ->toArray();
        }
        Log::info('fetchFAQs: FAQ ricevute', ['faqData' => $faqs]);
        return $faqs;
    }

    private function scrapeSite(?string $userUuid)
    {
        Log::info('scrapeSite: Inizio recupero Customer da uuid', ['userUuid' => $userUuid]);

        if (!$userUuid) {
            return ['error' => "Nessun UUID fornito per l'utente/attività."];
        }

        $customer = Customer::where('uuid', $userUuid)->first();
        if (!$customer) {
            Log::warning('scrapeSite: Nessun customer trovato', ['userUuid' => $userUuid]);
            return ['error' => "Nessun cliente trovato per l'UUID fornito."];
        }

        if (!$customer->website) {
            Log::warning('scrapeSite: Nessun sito web associato a questo customer', ['userUuid' => $userUuid]);
            return ['error' => 'Nessun sito web specificato per questo utente.'];
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($customer->website);
            $html = $response->getBody()->getContents();
        } catch (\Exception $e) {
            Log::error('scrapeSite: Errore nello scraping', ['error' => $e->getMessage()]);
            return ['error' => 'Impossibile recuperare il contenuto del sito.'];
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $body = $dom->getElementsByTagName('body')->item(0);
        $plainText = strip_tags($dom->saveHTML($body));

        try {
            Log::info('scrapeSite: Invio richiesta a GPT con testo pulito.');
            $analysisResponse = $this->client->chat()->create([
                'model'    => 'gpt-4o',
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'Sei un AI Assistant specializzato in consulenza aziendale, marketing e automazione dei processi.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Ecco il testo estratto dal sito:\n\n{$plainText}\n\n".
                                     "In base a questi contenuti, descrivi in modo conciso come l'AI può aiutare questa attività ".
                                     'a migliorare processi, marketing, vendite o altri aspetti rilevanti.',
                    ],
                ],
                'temperature' => 0.7,
            ]);

            $aiAnalysis = $analysisResponse->choices[0]->message->content ?? 'Nessuna analisi disponibile.';
        } catch (\Exception $e) {
            Log::error('scrapeSite: Errore durante la chiamata GPT.', ['error' => $e->getMessage()]);
            return ['error' => 'Impossibile generare un riepilogo. Errore GPT.'];
        }

        return [
            'site_content' => mb_substr($plainText, 0, 4000).'...',
            'ai_analysis'  => $aiAnalysis,
        ];
    }
}


