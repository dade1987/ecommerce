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
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\preg_replace;
use function Safe\preg_split;

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
        $activityUuid = $request->input('uuid');  // UUID identificativo dell’attività
        $locale = $request->input('locale', 'it'); // Default to Italian

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

        // Crea e gestisci il run con i vari tool disponibili
        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                'instructions' => trans('chatbot_prompts.instructions', ['locale' => $locale], $locale),
                'model'  => 'gpt-4o',
                'tools'  => [
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
                    // FUNZIONE per "Che cosa può fare l’AI per la mia attività?"
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
                                    ],
                                ],
                                'required' => ['user_uuid'],
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
                        case 'fallback':
                            $output = ['message' => trans('chatbot_prompts.fallback_message', [], $locale)];
                            break;
                    }

                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall->id,
                        'output'       => json_encode($output),
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

        if (! empty($productNames)) {
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

        if (! empty($productIds)) {
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
            'uuid'      => $activityUuid,
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

            if (! $teamSlug || ! $query || trim($query) === '') {
                return null;
            }

            $team = Team::where('slug', $teamSlug)->first();
            if (! $team) {
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
            'a', 'ad', 'al', 'allo', 'alla', 'ai', 'agli', 'alle', 'anche', 'avere', 'da', 'dal', 'dallo', 'dalla', 'dai', 'dagli', 'dalle', 'dei', 'degli', 'delle', 'del', 'dell', 'dello', 'della',
            'di', 'e', 'ed', 'che', 'chi', 'con', 'col', 'coi', 'come', 'dove', 'dunque', 'era', 'erano', 'essere', 'faccio', 'fai', 'fa', 'fanno', 'fate', 'fatto', 'fui', 'fu', 'furono', 'gli', 'il', 'lo', 'la', 'i', 'le',
            'in', 'nel', 'nello', 'nella', 'nei', 'negli', 'nelle', 'ma', 'mi', 'mia', 'mie', 'miei', 'mio', 'ne', 'non', 'o', 'od', 'per', 'perché', 'più', 'poi', 'quale', 'quali', 'qual', 'quanta', 'quanto', 'quanti', 'quante',
            'quasi', 'questo', 'questa', 'questi', 'queste', 'quello', 'quella', 'quelli', 'quelle', 'se', 'sei', 'si', 'sì', 'sia', 'siamo', 'siete', 'sono', 'su', 'sul', 'sullo', 'sulla', 'sui', 'sugli', 'sulle', 'tra', 'fra',
            'tu', 'tua', 'tue', 'tuo', 'tutti', 'tutte', 'tutto', 'un', 'uno', 'una', 'uno', 'va', 'vai', 'vado', 'vanno', 'voi', 'vostro', 'vostra', 'vostri', 'vostre', 'io', 'loro', 'noi', 'voi', 'dite', 'sono', 'buongiorno',
        ];
    }

    /**
     * Similarità via embedding, con catch su errori/reti e ritorno null.
     */
    private function tryEmbeddingSimilarity(string $textA, string $textB): ?float
    {
        try {
            if (! $this->client) {
                return null;
            }
            // Model embedding moderno
            $resp = $this->client->embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => [$textA, $textB],
            ]);
            $vecA = $resp->data[0]->embedding ?? null;
            $vecB = $resp->data[1]->embedding ?? null;
            if (! $vecA || ! $vecB) {
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
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
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
     * Scrape (ma estrai solo testo) + analisi con GPT su come l'AI può aiutare l'attività.
     */
    private function scrapeSite(?string $userUuid)
    {
        Log::info('scrapeSite: Inizio recupero Customer da uuid', ['userUuid' => $userUuid]);

        if (! $userUuid) {
            return ['error' => "Nessun UUID fornito per l'utente/attività."];
        }

        $customer = Customer::where('uuid', $userUuid)->first();
        if (! $customer) {
            Log::warning('scrapeSite: Nessun customer trovato', ['userUuid' => $userUuid]);

            return ['error' => 'Nessun cliente trovato per l\'UUID fornito.'];
        }

        if (! $customer->website) {
            Log::warning('scrapeSite: Nessun sito web associato a questo customer', ['userUuid' => $userUuid]);

            return ['error' => 'Nessun sito web specificato per questo utente.'];
        }

        // 1) Scarica il contenuto dal sito
        try {
            $client = new Client();
            $response = $client->get($customer->website);
            $html = $response->getBody()->getContents();
        } catch (\Exception $e) {
            Log::error('scrapeSite: Errore nello scraping', ['error' => $e->getMessage()]);

            return ['error' => 'Impossibile recuperare il contenuto del sito.'];
        }

        // 2) Estrai il contenuto testuale (rimuovendo i tag HTML)
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $body = $dom->getElementsByTagName('body')->item(0);
        $plainText = strip_tags($dom->saveHTML($body));

        // 3) Chiamata a GPT con il testo "pulito"
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

        // Restituiamo i dati della function: testo e analisi GPT
        return [
            'site_content' => mb_substr($plainText, 0, 4000).'...',
            'ai_analysis'  => $aiAnalysis,
        ];
    }

    private function formatResponseContent($content)
    {
        $formattedContent = nl2br($content);
        $formattedContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedContent);
        $formattedContent = preg_replace('/(\d+\.\s)/', '<strong>$1</strong>', $formattedContent);

        return $formattedContent;
    }
}
