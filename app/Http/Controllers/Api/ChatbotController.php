<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Quoter;
use App\Models\Team;
use App\Models\Product;
use App\Models\Event;
use App\Models\Order;
use App\Models\Faq;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\WebScraper\Facades\WebScraper;
use Modules\WebScraper\Services\AiAnalyzerService;
use Modules\WebScraper\Services\SearchResultCacheService;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\preg_replace;

class ChatbotController extends Controller
{
    public OpenAIClient $client;

    public function __construct()
    {
        $apiKey = config('openapi.key');
        $this->client = OpenAI::client($apiKey);
    }

    public function createThread()
    {
        $thread = $this->client->threads()->create([]);
        Log::info('thread id '.$thread->id);

        return response()->json([
            'thread_id' => $thread->id,
        ]);
    }

    public function handleChat(Request $request)
    {
        Log::info('handleChat: Inizio elaborazione richiesta');

        $threadId = $request->input('thread_id');
        $userInput = $request->input('message');
        $teamSlug = $request->input('team');
        $activityUuid = $request->input('uuid');  // UUID identificativo dell'attività
        $locale = $request->input('locale', 'it'); // Default to Italian
        $promptType = $request->input('prompt_type', 'business'); // 'business' o 'chat_libera'

        // Se non viene passato un thread_id, ne crea uno nuovo
        if (! $threadId) {
            $thread = $this->createThread();
            $threadId = $thread->getData()->thread_id;
            Log::info('handleChat: Creato nuovo thread', ['thread_id' => $threadId]);
        }

        Log::info('handleChat: Messaggio utente ricevuto', [
            'message'   => $userInput,
            'thread_id' => $threadId,
            'uuid'      => $activityUuid,
        ]);

        // Salva il messaggio dell'utente nel modello Quoter
        Quoter::create([
            'thread_id' => $threadId,
            'role'      => 'user',
            'content'   => $userInput,
        ]);

        // Aggiunge il messaggio dell'utente al thread su OpenAI
        $this->client->threads()->messages()->create($threadId, [
            'role'    => 'user',
            'content' => $userInput,
        ]);

        // Se l'utente scrive "buongiorno", recupera il welcome message dal modello Team
        if (strtolower($userInput) === strtolower(trans('enjoy-work.greeting', [], $locale))) {
            $team = Team::where('slug', $teamSlug)->first();
            $welcomeMessage = $team ? $team->welcome_message : trans('enjoy-work.welcome_message_fallback', [], $locale);

            Quoter::create([
                'thread_id' => $threadId,
                'role'      => 'chatbot',
                'content'   => $welcomeMessage,
            ]);

            return response()->json([
                'message'   => $welcomeMessage,
                'thread_id' => $threadId,
            ]);
        }

        // Prima di procedere con l'assistente, verifica se la domanda corrisponde a una FAQ
        $faqMatch = $this->findFaqAnswer($teamSlug, $userInput);
        if ($faqMatch) {
            Log::info('handleChat: Risposta trovata nelle FAQ', ['question' => $faqMatch['question']]);

            $faqAnswer = $faqMatch['answer'];

            // Salva il messaggio del chatbot (risposta FAQ) nel modello Quoter
            Quoter::create([
                'thread_id' => $threadId,
                'role'      => 'chatbot',
                'content'   => $faqAnswer,
            ]);

            return response()->json([
                'message'   => $this->formatResponseContent($faqAnswer),
                'thread_id' => $threadId,
            ]);
        }

        // Determina quale prompt usare in base al tipo
        $instructionKey = $promptType === 'chat_libera' ? 'instructions_chat_libera' : 'instructions';

        // Crea e gestisci il run con i vari tool disponibili
        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => config('openapi.assistant_id'),
                'instructions' => trans("chatbot_prompts.{$instructionKey}", ['locale' => $locale], $locale),
                'model'  => 'gpt-4o',
                'tools'  => $promptType === 'chat_libera' ? [] : [
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getProductInfo',
                            'description' => 'Recupera informazioni sui prodotti, servizi, attività del menu tramite i loro nomi.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'product_names' => [
                                        'type'        => 'array',
                                        'items'       => ['type' => 'string'],
                                        'description' => 'Nomi dei prodotti, servizi, attività da recuperare.',
                                    ],
                                ],
                                'required'   => [],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getAddressInfo',
                            'description' => 'Recupera informazioni sull\'indirizzo dell\'azienda, compreso indirizzo e numero di telefono.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'team_slug' => [
                                        'type'        => 'string',
                                        'description' => 'Slug del team per recuperare l\'indirizzo.',
                                    ],
                                ],
                                'required'   => ['team_slug'],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getAvailableTimes',
                            'description' => 'Recupera gli orari disponibili per un appuntamento.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'team_slug' => [
                                        'type'        => 'string',
                                        'description' => 'Slug del team per recuperare gli orari disponibili.',
                                    ],
                                ],
                                'required'   => ['team_slug'],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'createOrder',
                            'description' => 'Crea un ordine con i dati forniti.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'user_phone'  => [
                                        'type'        => 'string',
                                        'description' => 'Numero di telefono dell\'utente per la prenotazione.',
                                    ],
                                    'delivery_date' => [
                                        'type'        => 'string',
                                        'description' => 'Data di consegna dell\'ordine, includendo ora, minuti e secondi di inizio.',
                                    ],
                                    'product_ids' => [
                                        'type'        => 'array',
                                        'items'       => ['type' => 'integer'],
                                        'description' => 'ID dei prodotti, servizi, attività da includere nell\'ordine.',
                                    ],
                                ],
                                'required'   => ['user_phone', 'delivery_date', 'product_ids'],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'submitUserData',
                            'description' => 'Registra i dati anagrafici dell\'utente e risponde ringraziando. Dati trattati in conformità al GDPR.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'user_phone' => [
                                        'type'        => 'string',
                                        'description' => 'Numero di telefono dell\'utente',
                                    ],
                                    'user_email' => [
                                        'type'        => 'string',
                                        'description' => 'Email dell\'utente',
                                    ],
                                    'user_name'  => [
                                        'type'        => 'string',
                                        'description' => 'Nome dell\'utente',
                                    ],
                                ],
                                'required'   => ['user_phone', 'user_email', 'user_name'],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getFAQs',
                            'description' => 'Recupera le domande frequenti (FAQ) dal sistema in base a una query.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'team_slug' => [
                                        'type'        => 'string',
                                        'description' => 'Slug del team per recuperare le FAQ.',
                                    ],
                                    'query' => [
                                        'type'        => 'string',
                                        'description' => 'Query per cercare nelle FAQ.',
                                    ],
                                ],
                                'required'   => ['team_slug', 'query'],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'fallback',
                            'description' => 'Risponde a domande non inerenti al contesto con il messaggio predefinito.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => new \stdClass(),
                            ],
                        ],
                    ],
                    // FUNZIONE per "Che cosa può fare l'AI per la mia attività?"
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'scrapeSite',
                            'description' => 'Recupera il contenuto del sito web del cliente per rispondere a domande sull\'attività.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'user_uuid' => [
                                        'type' => 'string',
                                        'description' => 'UUID che identifica univocamente l\'attività del cliente.',
                                    ]
                                ],
                                'required' => ['user_uuid'],
                            ],
                        ],
                    ],
                    // FUNZIONE per scraping di URL specifico con query personalizzata
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'scrapeUrl',
                            'description' => 'Estrae TUTTE le informazioni da una SINGOLA pagina web specifica. Usa SEMPRE questa funzione quando:
- L\'utente fornisce un URL specifico di una pagina prodotto (es: Amazon, eBay, e-commerce con /dp/, /product/, /item/)
- L\'URL NON è una homepage ma una pagina interna specifica
- L\'utente chiede "caratteristiche", "dettagli", "specifiche", "descrizione" di UN prodotto/articolo specifico
Questa funzione analizza in profondità UNA SOLA pagina e estrae tutto il suo contenuto.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'url' => [
                                        'type' => 'string',
                                        'description' => 'L\'URL completo della pagina specifica da analizzare (es: https://www.amazon.it/prodotto/dp/B07MW4D7LG/)',
                                    ],
                                    'query' => [
                                        'type' => 'string',
                                        'description' => 'Cosa estrarre dalla pagina (es: "tutte le caratteristiche del prodotto", "prezzo e descrizione", "specifiche tecniche")',
                                    ]
                                ],
                                'required' => ['url', 'query'],
                            ],
                        ],
                    ],
                    // FUNZIONE per ricerca multi-pagina attraverso tutto il sito
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'searchSite',
                            'description' => 'Cerca informazioni attraverso MULTIPLE pagine di un sito web. Usa questa funzione quando:
- L\'utente fornisce ESPLICITAMENTE un URL specifico (es: "cerca nel sito https://example.com", "trova servizi su https://isofin.it")
- L\'utente dice "cerca nel sito web [URL]" - SEMPRE usa quell\'URL esatto, NON il sito del consumer corrente
- L\'utente chiede di cercare qualcosa in "tutto il sito", "nelle pagine del sito"
- Serve esplorare più pagine per trovare informazioni distribuite

IMPORTANTE: Se l\'utente specifica un URL esplicito nel prompt, usa SEMPRE quell\'URL nel parametro "url", NON usare il sito del consumer corrente.
ESEMPI:
- "cerca nel sito https://isofin.it i servizi" → usa url="https://isofin.it"
- "trova prodotti su https://example.com" → usa url="https://example.com"
- "cerca informazioni nel mio sito" → NON usare questa funzione, usa scrapeSite invece

NON usare per singole pagine prodotto o URL specifici di una pagina.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'url' => [
                                        'type' => 'string',
                                        'description' => 'L\'URL del sito web da esplorare (homepage o URL di partenza)',
                                    ],
                                    'query' => [
                                        'type' => 'string',
                                        'description' => 'Cosa cercare attraverso le pagine del sito (es: "trova tutti i prezzi dei prodotti", "cerca informazioni sui servizi", "trova tutte le pagine di contatto")',
                                    ],
                                    'max_pages' => [
                                        'type' => 'integer',
                                        'description' => 'Numero massimo di pagine da analizzare (default: 10)',
                                    ]
                                ],
                                'required' => ['url', 'query'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Loop until the run is in a terminal state
        while (in_array($run->status, ['queued', 'in_progress', 'requires_action'])) {
            if ($run->status === 'requires_action' && isset($run->requiredAction)) {
                $toolCalls = $run->requiredAction->submitToolOutputs->toolCalls;
                $toolOutputs = [];

                foreach ($toolCalls as $toolCall) {
                    $functionName = $toolCall->function->name;
                    $arguments = json_decode($toolCall->function->arguments, true);
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
                            $output = $this->createOrder($arguments['user_phone'], $arguments['delivery_date'], $arguments['product_ids'] ?? [], $teamSlug, $locale);
                            break;
                        case 'submitUserData':
                            $output = $this->submitUserData($arguments['user_phone'], $arguments['user_email'], $arguments['user_name'], $teamSlug, $locale, $activityUuid);
                            break;
                        case 'getFAQs':
                            $output = $this->fetchFAQs($teamSlug, $arguments['query'] ?? '');
                            break;
                        case 'scrapeSite':
                            $output = $this->scrapeSite($activityUuid);
                            break;
                        case 'scrapeUrl':
                            $output = $this->scrapeUrl($arguments['url'] ?? '', $arguments['query'] ?? '');
                            break;
                        case 'searchSite':
                            // Use new RAG-powered search (tries indexed content first, falls back to scraping)
                            $scraper = app(\Modules\WebScraper\Services\WebScraperService::class);
                            $ragResult = $scraper->searchWithRag(
                                $arguments['url'] ?? '',
                                $arguments['query'] ?? '',
                                [
                                    'max_pages' => $arguments['max_pages'] ?? 10,
                                    'ttl_days' => 30,
                                    'top_k' => 5,
                                    'min_similarity' => 0.7,
                                ]
                            );

                            // Format output for OpenAI
                            if ($ragResult['success']) {
                                $output = [
                                    'url' => $arguments['url'] ?? '',
                                    'query' => $arguments['query'] ?? '',
                                    'analysis' => $ragResult['answer'],
                                    'method' => $ragResult['method'],
                                    'sources' => $ragResult['sources'],
                                ];

                                // Add method-specific metadata
                                if ($ragResult['method'] === 'rag') {
                                    $output['chunks_found'] = $ragResult['chunks_found'];
                                } elseif ($ragResult['method'] === 'scraping_with_indexing') {
                                    $output['pages_visited'] = $ragResult['pages_visited'];
                                    $output['indexed_for_future'] = true;
                                }
                            } else {
                                $output = [
                                    'url' => $arguments['url'] ?? '',
                                    'query' => $arguments['query'] ?? '',
                                    'analysis' => $ragResult['answer'] ?? 'Nessuna informazione trovata.',
                                    'error' => $ragResult['error'] ?? null,
                                ];
                            }
                            break;
                        case 'fallback':
                            $output = ['message' => trans('chatbot_prompts.fallback_message', [], $locale)];
                            break;
                    }

                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall->id,
                        'output'       => \json_encode($output, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE),
                    ];
                }

                $run = $this->client->threads()->runs()->submitToolOutputs(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => $toolOutputs,
                    ]
                );
            }

            // Wait for a second before polling again
            sleep(1);
            $run = $this->client->threads()->runs()->retrieve(threadId: $threadId, runId: $run->id);
            Log::info('handleChat: Polling run status', ['status' => $run->status, 'runId' => $run->id]);
        }


        if ($run->status === 'completed') {
            $messages = $this->client->threads()->messages()->list($threadId)->data;
            $content = $messages[0]->content[0]->text->value ?? 'Nessuna risposta trovata.';

            // Salva il messaggio del chatbot nel modello Quoter
            Quoter::create([
                'thread_id' => $threadId,
                'role'      => 'chatbot',
                'content'   => $content,
            ]);

            // Formatta il contenuto della risposta
            $formattedContent = $this->formatResponseContent($content);

            return response()->json([
                'message'     => $formattedContent,
                'thread_id'   => $threadId,
                'product_ids' => $productIds ?? [],
            ]);
        } else {
             Log::error('handleChat: Run did not complete successfully.', ['status' => $run->status, 'run_data' => $run->toArray()]);
            return response()->json(['error' => "The AI assistant could not process the request. Status: {$run->status}"], 500);
        }
    }

    /**
     * Recupera il risultato del run, attendendo finché non è 'completed' o 'requires_action'.
     */
    private function retrieveRunResult($threadId, $runId)
    {
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);
            Log::info('retrieveRunResult: Stato del run', [
                'threadId' => $threadId,
                'runId'    => $runId,
                'status'   => $run->status,
            ]);

            if ($run->status === 'completed' || $run->status === 'requires_action') {
                return $run;
            }
            sleep(1);
        }
    }

    private function fetchProductData(array $productNames, $teamSlug)
    {
        Log::info('fetchProductData: Inizio recupero dati prodotti', [
            'productNames' => $productNames,
            'teamSlug'     => $teamSlug,
        ]);

        $team = Team::where('slug', $teamSlug)->firstOrFail();
        $query = Product::where('team_id', $team->id);

        if (!empty($productNames)) {
            $query->where(function ($q) use ($productNames) {
                foreach ($productNames as $name) {
                    $q->orWhere('name', 'like', '%' . $name . '%');
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
        $team = Team::where('slug', $teamSlug)->firstOrFail();
        Log::info('fetchAddressData: Dati indirizzo ricevuti', ['addressData' => $team->toArray()]);
        return $team->toArray();
    }

    private function fetchAvailableTimes($teamSlug)
    {
        Log::info('fetchAvailableTimes: Inizio recupero orari disponibili', ['teamSlug' => $teamSlug]);
        $team = Team::where('slug', $teamSlug)->firstOrFail();
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
        $team = Team::where('slug', $teamSlug)->firstOrFail();

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
            'message'  => trans('chatbot_prompts.order_created_successfully', [], $locale),
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

        // Aggiorna il modello Customer con l'UUID dell'attività
        if ($activityUuid) {
            $customer = Customer::where('uuid', $activityUuid)->first();
            if ($customer) {
                $customer->phone = $userPhone;
                $customer->email = $userEmail;
                $customer->name = $userName;
                $customer->save();
            } else {
                // Se non esiste un cliente con questo UUID, potresti volerlo creare
                 Customer::create([
                    'uuid' => $activityUuid,
                    'phone' => $userPhone,
                    'email' => $userEmail,
                    'name' => $userName,
                    'team_id' => Team::where('slug', $teamSlug)->first()->id,
                ]);
            }
        } else {
             // Gestisci il caso in cui non c'è UUID
             Customer::create([
                'phone' => $userPhone,
                'email' => $userEmail,
                'name' => $userName,
                'team_id' => Team::where('slug', $teamSlug)->first()->id,
            ]);
        }


        // Messaggio di conferma in base alla lingua
        return trans('chatbot_prompts.user_data_submitted', [], $locale);
    }

    private function fetchFAQs($teamSlug, $query)
    {
        Log::info('fetchFAQs: Inizio recupero FAQ', ['teamSlug' => $teamSlug, 'query' => $query]);
        $team = Team::where('slug', $teamSlug)->firstOrFail();
        $faqs = Faq::where('team_id', $team->id)
            ->whereRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->get(['question', 'answer'])
            ->toArray();
        Log::info('fetchFAQs: FAQ ricevute', ['faqData' => $faqs]);
        return $faqs;
    }

    /**
     * Trova una risposta FAQ pertinente alla query per il team indicato.
     * Restituisce ['question' => ..., 'answer' => ...] oppure null se non trovata.
     */
    private function findFaqAnswer(?string $teamSlug, ?string $query): ?array
    {
        try {
            Log::info('findFaqAnswer: Avvio ricerca FAQ', ['teamSlug' => $teamSlug, 'query' => $query]);

            if (!$teamSlug || !$query || trim($query) === '') {
                return null;
            }

            $team = Team::where('slug', $teamSlug)->first();
            if (!$team) {
                return null;
            }

            $builder = Faq::where('team_id', $team->id)
                ->where('active', true);

            // Parametri di soglia (configurabili via env)
            $useEmbeddings = (bool) env('OPENAI_USE_EMBEDDINGS', false);
            $semanticThreshold = (float) env('FAQ_SEMANTIC_THRESHOLD', 0.85);
            $lexicalThreshold = (float) env('FAQ_LEXICAL_THRESHOLD', 0.60);

            // Recupera una rosa di candidati ordinati per rilevanza
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
                Log::warning('findFaqAnswer: FULLTEXT non disponibile, fallback LIKE', ['error' => $e->getMessage()]);
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

            $best = null;
            $bestScore = -1.0;

            foreach ($candidates as $faq) {
                $q = (string) $faq->question;
                $a = (string) $faq->answer;

                // Similarità semantica (se attivata) con fallback lessicale
                $scoreQuestion = $useEmbeddings ? ($this->tryEmbeddingSimilarity($query, $q) ?? $this->computeLexicalSimilarity($query, $q)) : $this->computeLexicalSimilarity($query, $q);
                $scoreAnswer = $useEmbeddings ? ($this->tryEmbeddingSimilarity($query, $a) ?? $this->computeLexicalSimilarity($query, $a)) : $this->computeLexicalSimilarity($query, $a);

                // Pesa maggiormente la domanda della FAQ
                $score = max($scoreQuestion * 0.7 + $scoreAnswer * 0.3, $scoreQuestion);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $faq;
                }
            }

            // Applica soglia adeguata al metodo usato
            $threshold = $useEmbeddings ? $semanticThreshold : $lexicalThreshold;
            if ($best && $bestScore >= $threshold) {
                Log::info('findFaqAnswer: FAQ selezionata con score', ['score' => $bestScore, 'threshold' => $threshold, 'question' => $best->question]);
                return $best->only(['question', 'answer']);
            }

            Log::info('findFaqAnswer: Nessuna FAQ supera la soglia', ['bestScore' => $bestScore, 'threshold' => $threshold]);
            return null;
        } catch (\Throwable $e) {
            Log::error('findFaqAnswer: Errore inatteso durante la ricerca FAQ', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Calcola una similarità lessicale [0..1] robusta a stopword.
     * Combina Jaccard e containment su token con lunghezza >= 4.
     */
    private function computeLexicalSimilarity(string $textA, string $textB): float
    {
        $tokensA = $this->tokenizeAndFilter($textA);
        $tokensB = $this->tokenizeAndFilter($textB);

        if (empty($tokensA) || empty($tokensB)) {
            return 0.0;
        }

        $setA = array_unique($tokensA);
        $setB = array_unique($tokensB);

        $intersection = array_values(array_intersect($setA, $setB));
        $union = array_values(array_unique(array_merge($setA, $setB)));

        $jaccard = count($union) > 0 ? count($intersection) / count($union) : 0.0;
        $containment = min(count($setA), count($setB)) > 0 ? count($intersection) / min(count($setA), count($setB)) : 0.0;

        // Ponderazione semplice: penalizza match deboli, premia copertura
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
            if ($tok === '' || in_array($tok, $stopwords, true)) {
                continue;
            }
            // Considera solo parole informative
            if (mb_strlen($tok) >= 4) {
                $filtered[] = $tok;
            }
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

    /**
     * Similarità via embedding, con catch su errori/reti e ritorno null.
     */
    private function tryEmbeddingSimilarity(string $textA, string $textB): ?float
    {
        try {
            if (!$this->client) {
                return null;
            }
            // Model embedding moderno
            $resp = $this->client->embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => [$textA, $textB],
            ]);
            $vecA = $resp->data[0]->embedding ?? null;
            $vecB = $resp->data[1]->embedding ?? null;
            if (!$vecA || !$vecB) {
                return null;
            }
            return $this->cosineSimilarity($vecA, $vecB);
        } catch (\Throwable $e) {
            Log::warning('tryEmbeddingSimilarity: fallback per errore embeddings', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0; $normA = 0.0; $normB = 0.0;
        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }
        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }
        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Scrape website with intelligent parsing + AI analysis on how AI can help the business.
     * Uses WebScraper module for improved content extraction.
     */
    private function scrapeSite(?string $userUuid)
    {
        Log::info('scrapeSite: Inizio recupero Customer da uuid', ['userUuid' => $userUuid]);

        if (!$userUuid) {
            return ['error' => "Nessun UUID fornito per l'utente/attività."];
        }

        $customer = Customer::where('uuid', $userUuid)->first();
        if (!$customer) {
            Log::warning('scrapeSite: Nessun customer trovato', ['userUuid' => $userUuid]);
            return ['error' => 'Nessun cliente trovato per l\'UUID fornito.'];
        }

        if (!$customer->website) {
            Log::warning('scrapeSite: Nessun sito web associato a questo customer', ['userUuid' => $userUuid]);
            return ['error' => 'Nessun sito web specificato per questo utente.'];
        }

        try {
            // Step 1: Scrape the website with intelligent parsing
            $scrapedData = WebScraper::scrape($customer->website);

            if (isset($scrapedData['error'])) {
                Log::error('scrapeSite: Errore nello scraping', ['error' => $scrapedData['error']]);
                return ['error' => 'Impossibile recuperare il contenuto del sito.'];
            }

            // Step 2: Perform AI analysis on the scraped content
            $analyzer = app(AiAnalyzerService::class);
            $analysis = $analyzer->analyzeBusinessInfo($scrapedData);

            if (isset($analysis['error'])) {
                Log::error('scrapeSite: Errore durante l\'analisi AI', ['error' => $analysis['error']]);
                return ['error' => 'Impossibile generare un riepilogo. Errore AI.'];
            }

            Log::info('scrapeSite: Scraping e analisi completati', [
                'url' => $customer->website,
                'content_length' => strlen($scrapedData['content']['main']),
                'tokens_used' => $analysis['usage']['total_tokens'] ?? 0,
            ]);

            // Return data in the same format as before for compatibility
            return [
                'site_content' => $scrapedData['content']['main'],
                'ai_analysis' => $analysis['analysis'],
                'metadata' => $scrapedData['metadata'],
            ];

        } catch (\Exception $e) {
            Log::error('scrapeSite: Errore imprevisto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Si è verificato un errore durante l\'elaborazione.'];
        }
    }

    /**
     * Scrape a specific URL with custom query for targeted information extraction.
     * Example: "Ottieni informazioni da questo url https://example.com trova i prezzi"
     */
    private function scrapeUrl(string $url, string $query): array
    {
        if (empty($url)) {
            return ['error' => 'URL non fornito.'];
        }

        if (empty($query)) {
            return ['error' => 'Query di ricerca non fornita.'];
        }

        // Validazione URL per prevenire SSRF
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['error' => 'Formato URL non valido.'];
        }

        $parsedUrl = parse_url($url);

        // Permetti solo HTTP/HTTPS
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return ['error' => 'Solo protocolli HTTP e HTTPS sono consentiti.'];
        }

        // Blocca IP privati e localhost per prevenire SSRF
        $host = $parsedUrl['host'] ?? '';
        $ip = gethostbyname($host);

        // Verifica se l'IP è privato o riservato
        if ($ip !== $host) { // gethostbyname ha risolto l'IP
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return ['error' => 'Accesso a indirizzi IP privati o riservati non consentito.'];
            }
        }

        // Blocca esplicitamente localhost e varianti
        $blockedHosts = ['localhost', '127.0.0.1', '0.0.0.0', '::1'];
        if (in_array(strtolower($host), $blockedHosts)) {
            return ['error' => 'Accesso a localhost non consentito.'];
        }

        // Limita lunghezza query
        if (strlen($query) > 500) {
            $query = substr($query, 0, 500);
        }

        try {
            // Check if this is a specific product page (Amazon, eBay, etc.)
            $isProductPage = $this->isProductPage($url);

            if ($isProductPage) {
                // For product pages, scrape the ENTIRE page directly (no keyword search)
                Log::info('scrapeUrl: Detected product page, scraping entire content', ['url' => $url]);

                $scraper = app(\Modules\WebScraper\Services\WebScraperService::class);
                $scrapedData = $scraper->scrape($url, ['query' => $query]);

                if (isset($scrapedData['error'])) {
                    return ['error' => $scrapedData['error']];
                }

                // Perform AI analysis on the FULL page content
                $analyzer = app(\Modules\WebScraper\Services\AiAnalyzerService::class);
                $analysis = $analyzer->extractCustomInfo($scrapedData, $query);

                if (isset($analysis['error'])) {
                    Log::error('scrapeUrl: Errore durante l\'analisi AI', [
                        'url' => $url,
                        'query' => $query,
                        'error' => $analysis['error'],
                    ]);
                    return ['error' => 'Impossibile analizzare il contenuto: ' . $analysis['error']];
                }

                Log::info('scrapeUrl: Analisi AI completata (product page)', [
                    'url' => $url,
                    'query' => $query,
                    'tokens_used' => $analysis['usage']['total_tokens'] ?? 0,
                ]);

                return [
                    'url' => $url,
                    'query' => $query,
                    'analysis' => $analysis['analysis'],
                    'pages_found' => 1,
                    'pages_visited' => 1,
                    'results' => [
                        [
                            'url' => $url,
                            'title' => $scrapedData['metadata']['title'] ?? 'Product Page',
                            'relevance' => 1.0,
                        ]
                    ],
                ];
            }

            // For non-product pages, use intelligent search with keywords AND CACHING
            Log::info('scrapeUrl: Using intelligent search with caching', ['url' => $url, 'query' => $query]);

            $cacheService = app(SearchResultCacheService::class);
            $cachedResults = $cacheService->getCachedOrSearch($url, $query, 3); // max depth 3
            $results_count = is_array($cachedResults['results']) ? count($cachedResults['results']) : $cachedResults['results'];

            // Check if results come from cache
            if ($cachedResults['from_cache'] ?? false) {
                Log::info('scrapeUrl: Returning cached results', [
                    'url' => $url,
                    'query' => $query,
                    'results_count' =>$results_count,
                    'cached_at' => $cachedResults['cached_at'] ?? null,
                ]);

                // If we have a reformulated summary from cache, use it directly
                if (isset($cachedResults['reformulated_summary'])) {
                    return [
                        'url' => $url,
                        'query' => $query,
                        'analysis' => $cachedResults['reformulated_summary'],
                        'pages_found' => $results_count,
                        'pages_visited' => $cachedResults['pages_visited'],
                        'results' => $cachedResults['results'],
                        'from_cache' => true,
                        'cached_at' => $cachedResults['cached_at'],
                    ];
                }
            }

            // Use fresh or cached search results
            $searchResults = [
                'results' => $cachedResults['results'],
                'pages_visited' => $cachedResults['pages_visited'],
                'query' => $cachedResults['query'],
            ];

            if (empty($searchResults['results'])) {
                Log::warning('scrapeUrl: Nessun risultato trovato', [
                    'url' => $url,
                    'query' => $query,
                    'pages_visited' => $searchResults['pages_visited'] ?? 0,
                ]);
                return ['error' => 'Non ho trovato informazioni rilevanti per la query richiesta.'];
            }

            Log::info('scrapeUrl: Ricerca intelligente completata', [
                'url' => $url,
                'query' => $query,
                'results_found' => $results_count,
                'pages_visited' => $searchResults['pages_visited'],
            ]);

            // Aggregate all found results
            $aggregatedContent = '';
            foreach ($searchResults['results'] as $result) {
                $aggregatedContent .= "\n\n=== {$result['title']} ({$result['url']}) ===\n";
                $aggregatedContent .= $result['content_excerpt'];
            }

            // Perform AI analysis on aggregated results
            $analyzer = app(\Modules\WebScraper\Services\AiAnalyzerService::class);
            $mockScrapedData = [
                'url' => $url,
                'content' => ['main' => $aggregatedContent],
                'metadata' => ['title' => 'Risultati aggregati'],
            ];

            $analysis = $analyzer->extractCustomInfo($mockScrapedData, $query);

            if (isset($analysis['error'])) {
                Log::error('scrapeUrl: Errore durante l\'analisi AI', [
                    'url' => $url,
                    'query' => $query,
                    'error' => $analysis['error'],
                ]);
                return ['error' => 'Impossibile analizzare il contenuto: ' . $analysis['error']];
            }

            Log::info('scrapeUrl: Analisi AI completata', [
                'url' => $url,
                'query' => $query,
                'tokens_used' => $analysis['usage']['total_tokens'] ?? 0,
            ]);

            // Return structured data
            return [
                'url' => $url,
                'query' => $query,
                'analysis' => $analysis['analysis'],
                'pages_found' => $results_count,
                'pages_visited' => $searchResults['pages_visited'],
                'results' => $searchResults['results'],
            ];

        } catch (\Exception $e) {
            Log::error('scrapeUrl: Errore imprevisto', [
                'url' => $url,
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Si è verificato un errore durante l\'elaborazione: ' . $e->getMessage()];
        }
    }

    /**
     * Search through multiple pages of a website for specific information.
     * Uses intelligent menu-guided crawling with AI assistance + result caching.
     * Example: "Cerca nel sito https://example.com i prezzi e i servizi"
     */
    private function searchSite(string $url, string $query, int $maxPages = 10): array
    {
        Log::info('searchSite: Inizio ricerca intelligente con caching', [
            'url' => $url,
            'query' => $query,
            'max_depth' => 3,
        ]);

        if (empty($url)) {
            return ['error' => 'URL non fornito.'];
        }

        if (empty($query)) {
            return ['error' => 'Query di ricerca non fornita.'];
        }

        try {
            // Use SearchResultCacheService for intelligent caching
            $cacheService = app(SearchResultCacheService::class);
            $cachedResults = $cacheService->getCachedOrSearch($url, $query, 3); // max depth 3

            $results_count = is_array($cachedResults['results']) ? count($cachedResults['results']) : $cachedResults['results'];
            // If results come from cache, return reformulated response immediately
            if ($cachedResults['from_cache'] ?? false) {
                Log::info('searchSite: Returning cached results', [
                    'url' => $url,
                    'query' => $query,
                    //'results_count' => count($cachedResults['results']),
                    'results_count' => $results_count,
                    'cached_at' => $cachedResults['cached_at'] ?? null,
                ]);

                // If we have a reformulated summary, use it
                if (isset($cachedResults['reformulated_summary'])) {
                    return [
                        'url' => $url,
                        'query' => $query,
                        'pages_visited' => $cachedResults['pages_visited'],
                        'results_found' => $results_count,
                        'analysis' => $cachedResults['reformulated_summary'],
                        'from_cache' => true,
                        'cached_at' => $cachedResults['cached_at'],
                    ];
                }

                // Fallback if reformulation failed
                $foundPages = [];
                foreach ($cachedResults['results'] as $result) {
                    $foundPages[] = [
                        'url' => $result['url'],
                        'title' => $result['title'],
                    ];
                }

                return [
                    'url' => $url,
                    'query' => $query,
                    'pages_visited' => $cachedResults['pages_visited'],
                    'results_found' => $results_count,
                    'analysis' => 'Risultati trovati: ' . implode(', ', array_column($foundPages, 'title')),
                    'found_pages' => $foundPages,
                    'from_cache' => true,
                ];
            }

            // Fresh search results - need to scrape pages and do AI analysis
            $searchResults = $cachedResults;

            if (empty($searchResults['results'])) {
                Log::warning('searchSite: Nessun risultato trovato', ['url' => $url, 'query' => $query]);
                return [
                    'url' => $url,
                    'query' => $query,
                    'pages_visited' => $searchResults['pages_visited'],
                    'analysis' => 'Non sono state trovate informazioni specifiche su "' . $query . '" nelle pagine visitate.',
                ];
            }

            // Scrape each found URL to get fresh, complete content
            $foundPages = [];
            $aggregatedData = [];
            $scraper = app(\Modules\WebScraper\Services\WebScraperService::class);

            Log::info('searchSite: Scraping individual pages', ['urls_count' => count($searchResults['results'])]);

            foreach ($searchResults['results'] as $result) {
                $foundPages[] = [
                    'url' => $result['url'],
                    'title' => $result['title'],
                    'depth' => $result['depth'] ?? 0,
                ];

                // Scrape the full page (will use cache if available, or fetch and cache if not)
                // Pass query to determine if footer content is needed
                $scrapedData = $scraper->scrape($result['url'], ['query' => $query]);

                if (!isset($scrapedData['error'])) {
                    // Add full scraped data for AI analysis
                    $aggregatedData[] = $scrapedData;
                }
            }

            Log::info('searchSite: Pages scraped for AI analysis', ['pages_count' => count($aggregatedData)]);

            // Use AI to analyze and summarize all found results
            $analyzer = app(AiAnalyzerService::class);
            $analysis = $analyzer->searchMultiplePages($aggregatedData, $query);
            $aiAnalysisText = $analysis['analysis'] ?? 'Analisi completata';

            Log::info('searchSite: Ricerca completata', [
                'url' => $url,
                'query' => $query,
                'pages_visited' => $searchResults['pages_visited'],
                'results_found' => count($searchResults['results']),
            ]);

            // Update cache with AI analysis
            // Note: getCachedOrSearch already cached the raw results, now we update with AI analysis
            $cacheService->cacheResults($url, $query, $searchResults['results'], $searchResults['pages_visited'], $aiAnalysisText);

            // Return structured data
            return [
                'url' => $url,
                'query' => $query,
                'pages_visited' => $searchResults['pages_visited'],
                'results_found' => count($searchResults['results']),
                'analysis' => $aiAnalysisText,
                'found_pages' => $foundPages,
                'from_cache' => false,
            ];

        } catch (\Exception $e) {
            Log::error('searchSite: Errore imprevisto', [
                'url' => $url,
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Si è verificato un errore durante la ricerca: ' . $e->getMessage()];
        }
    }

    /**
     * Detect if a URL is a specific product page (Amazon, eBay, Shopify, etc.)
     * Returns true for product pages, false for homepages and category pages
     */
    private function isProductPage(string $url): bool
    {
        // Product page URL patterns for common e-commerce sites
        $productPatterns = [
            // Amazon product pages (with /dp/ or /product/)
            '/amazon\.[a-z.]+\/(dp|product|gp\/product)\/[A-Z0-9]{10}/i',

            // eBay item pages
            '/ebay\.[a-z.]+\/itm\//i',

            // Shopify product pages (common pattern /products/)
            '/\/products\/[^\/]+\/?$/i',

            // Generic e-commerce product patterns
            '/\/(item|prodotto|articolo|product)\/[^\/]+/i',

            // Woocommerce and similar (common pattern /product/)
            '/\/product\/[^\/]+\/?$/i',

            // Magento (common pattern /[product-name].html)
            '/\/[a-z0-9-]+\.html$/i',
        ];

        foreach ($productPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        // Check if it's a homepage or category page (return false for these)
        $nonProductPatterns = [
            // Homepage patterns
            '/^https?:\/\/[^\/]+\/?$/i',
            '/^https?:\/\/[^\/]+\/index\.(html|php)$/i',

            // Category/collection pages
            '/\/(category|categories|collection|collections|c)\//i',
        ];

        foreach ($nonProductPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return false;
            }
        }

        // Default: assume non-product page for safety
        return false;
    }

    private function formatResponseContent($content)
    {
        $formattedContent = nl2br($content);
        $formattedContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedContent);
        $formattedContent = preg_replace('/(\d+\.\s)/', '<strong>$1</strong>', $formattedContent);

        return $formattedContent;
    }
}
