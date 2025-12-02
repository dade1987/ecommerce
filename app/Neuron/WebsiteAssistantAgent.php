<?php

declare(strict_types=1);

namespace App\Neuron;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quoter;
use App\Models\Team;
use App\Services\EmbeddingCacheService;
use App\Services\WebsiteScraperService;
use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Tools\ArrayProperty;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

/**
 * WebsiteAssistantAgent
 *
 * Agente Neuron AI per l'assistenza virtuale su siti web con streaming nativo.
 * Replica la funzionalità del RealtimeChatWebsiteController usando il framework Neuron.
 */
class WebsiteAssistantAgent extends Agent
{
    private ?Team $cachedTeam = null;

    private ?string $cachedTeamSlug = null;

    private ?EmbeddingCacheService $embeddingService = null;

    private ?WebsiteScraperService $scraperService = null;

    private ?string $teamSlug = null;

    private ?string $locale = null;

    private ?string $activityUuid = null;

    private ?string $websiteContent = '';

    /**
     * Configura lo agent con i parametri della richiesta
     */
    public function withWebsiteContext(
        string $teamSlug,
        string $locale = 'it',
        ?string $activityUuid = null,
        ?string $websiteContent = ''
    ): self {
        $this->teamSlug = $teamSlug;
        $this->locale = $locale;
        $this->activityUuid = $activityUuid;
        $this->websiteContent = $websiteContent ?? '';

        return $this;
    }

    /**
     * Lazy load dell'embedding service
     */
    private function getEmbeddingService(): EmbeddingCacheService
    {
        if ($this->embeddingService === null) {
            $apiKey = config('services.openai.key');
            $client = \OpenAI::client($apiKey);
            $this->embeddingService = new EmbeddingCacheService($client);
        }

        return $this->embeddingService;
    }

    /**
     * Provider OpenAI
     */
    protected function provider(): AIProviderInterface
    {
        return new OpenAI(
            key: config('services.openai.key'),
            model: 'gpt-4o-mini',
        );
    }

    /**
     * System prompt con contesto dei siti web e azienda
     */
    public function instructions(): string
    {
        $locale = $this->locale ?? 'it';
        $baseInstructions = (string) trans('enjoywork3d_prompts.instructions', ['locale' => $locale], $locale);

        if (! empty($this->websiteContent)) {
            $baseInstructions .= "\n\nPRIORITA': Contenuto dai siti web aziendali:\n\n".$this->websiteContent;
        }

        // Aggiungi la lista dei prodotti disponibili se il team è noto
        if ($this->teamSlug) {
            $team = $this->getTeamCached($this->teamSlug);
            if ($team) {
                $products = Product::where('team_id', $team->id)
                    ->select(['id', 'name', 'price', 'description'])
                    ->limit(20)
                    ->get();

                if ($products->isNotEmpty()) {
                    $productsList = $products->map(fn ($p) => "ID: {$p->id} | Nome: {$p->name} | Prezzo: {$p->price}")
                        ->join("\n");
                    $baseInstructions .= "\n\nProdotti/Servizi disponibili:\n".$productsList;
                }
            }
        }

        Log::debug('WebsiteAssistantAgent.instructions', [
            'prompt_length' => strlen($baseInstructions),
            'has_website_content' => ! empty($this->websiteContent),
        ]);

        return $baseInstructions;
    }

    /**
     * Definizione dei tools disponibili
     */
    protected function tools(): array
    {
        $toolsList = [
            $this->createGetProductInfoTool(),
            $this->createGetAddressInfoTool(),
            $this->createGetAvailableTimesTool(),
            $this->createCreateOrderTool(),
            $this->createSubmitUserDataTool(),
            $this->createGetFAQsTool(),
            $this->createSearchSiteTool(),
            $this->createFallbackTool(),
        ];

        Log::debug('WebsiteAssistantAgent.tools', [
            'tools_count' => count($toolsList),
            'tool_names' => array_map(fn ($t) => $t->getName(), $toolsList),
        ]);

        return $toolsList;
    }

    private function createGetProductInfoTool(): Tool
    {
        return Tool::make(
            name: 'getProductInfo',
            description: 'Recupera informazioni sui prodotti, servizi, attività del menu tramite i loro nomi.'
        )
            ->addProperty(
                ArrayProperty::make(
                    name: 'product_names',
                    description: 'Nomi dei prodotti, servizi, attività da recuperare.',
                    required: true,
                    items: ToolProperty::make(
                        name: 'product_name',
                        type: PropertyType::STRING,
                        description: 'Nome del prodotto',
                        required: true
                    )
                )
            )
            ->setCallable(fn (array $product_names) => $this->fetchProductData($product_names));
    }

    private function createGetAddressInfoTool(): Tool
    {
        return Tool::make(
            name: 'getAddressInfo',
            description: "Recupera informazioni sull'indirizzo dell'azienda, compreso indirizzo e numero di telefono."
        )
            ->setCallable(fn () => $this->fetchAddressData());
    }

    private function createGetAvailableTimesTool(): Tool
    {
        return Tool::make(
            name: 'getAvailableTimes',
            description: 'Recupera gli orari disponibili per un appuntamento.'
        )
            ->setCallable(fn () => $this->fetchAvailableTimes());
    }

    private function createCreateOrderTool(): Tool
    {
        return Tool::make(
            name: 'createOrder',
            description: 'Crea un ordine con i dati forniti.'
        )
            ->addProperty(
                ToolProperty::make(
                    name: 'user_phone',
                    type: PropertyType::STRING,
                    description: "Numero di telefono dell'utente per la prenotazione.",
                    required: true
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'delivery_date',
                    type: PropertyType::STRING,
                    description: "Data di consegna dell'ordine, includendo ora, minuti e secondi di inizio.",
                    required: true
                )
            )
            ->addProperty(
                ArrayProperty::make(
                    name: 'product_ids',
                    description: "ID dei prodotti, servizi, attività da includere nell'ordine.",
                    required: true,
                    items: ToolProperty::make(
                        name: 'product_id',
                        type: PropertyType::INTEGER,
                        description: 'ID del prodotto',
                        required: true
                    )
                )
            )
            ->setCallable(fn (string $user_phone, string $delivery_date, array $product_ids) => $this->createOrder($user_phone, $delivery_date, $product_ids)
            );
    }

    private function createSubmitUserDataTool(): Tool
    {
        return Tool::make(
            name: 'submitUserData',
            description: "Registra i dati anagrafici dell'utente e risponde ringraziando. Dati trattati in conformità al GDPR."
        )
            ->addProperty(
                ToolProperty::make(
                    name: 'user_phone',
                    type: PropertyType::STRING,
                    description: "Numero di telefono dell'utente",
                    required: true
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'user_email',
                    type: PropertyType::STRING,
                    description: "Email dell'utente",
                    required: true
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'user_name',
                    type: PropertyType::STRING,
                    description: "Nome dell'utente",
                    required: true
                )
            )
            ->setCallable(fn (string $user_phone, string $user_email, string $user_name) => $this->submitUserData($user_phone, $user_email, $user_name)
            );
    }

    private function createGetFAQsTool(): Tool
    {
        return Tool::make(
            name: 'getFAQs',
            description: 'Recupera le domande frequenti (FAQ) dal sistema in base a una query.'
        )
            ->addProperty(
                ToolProperty::make(
                    name: 'query',
                    type: PropertyType::STRING,
                    description: 'Query per cercare nelle FAQ.',
                    required: true
                )
            )
            ->setCallable(fn (string $query) => $this->fetchFAQs($query));
    }

    private function createSearchSiteTool(): Tool
    {
        return Tool::make(
            name: 'searchSite',
            description: 'Cerca informazioni specifiche sui siti web aziendali. Usa questo tool quando l\'utente chiede informazioni dettagliate che potrebbero non essere nel contesto generale fornito.'
        )
            ->addProperty(
                ToolProperty::make(
                    name: 'url',
                    type: PropertyType::STRING,
                    description: 'URL del sito web su cui cercare (es. https://www.esempio.it). Se non specificato, cerca su tutti i siti del team.',
                    required: false
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'query',
                    type: PropertyType::STRING,
                    description: 'Query di ricerca o domanda specifica da cercare nel sito.',
                    required: true
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'max_pages',
                    type: PropertyType::INTEGER,
                    description: 'Numero massimo di pagine da analizzare (default: 10). Usato solo se la ricerca RAG non trova risultati.',
                    required: false
                )
            )
            ->setCallable(fn (string $query, ?string $url = null, ?int $max_pages = null) => $this->searchSite($query, $url, $max_pages)
            );
    }

    private function createFallbackTool(): Tool
    {
        return Tool::make(
            name: 'fallback',
            description: 'Risponde a domande non inerenti al contesto con il messaggio predefinito.'
        )
            ->setCallable(fn () => [
                'message' => trans('enjoywork3d_prompts.fallback_message', [], $this->locale ?? 'it'),
            ]);
    }

    // ====== Helper Methods ======

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

    private function fetchProductData(array $productNames): array
    {
        Log::info('WebsiteAssistantAgent.fetchProductData', [
            'productNames' => $productNames,
            'teamSlug' => $this->teamSlug,
        ]);

        if (! $this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (! $team) {
            return [];
        }

        $query = Product::where('team_id', $team->id);
        if (! empty($productNames)) {
            $query->where(function ($q) use ($productNames) {
                foreach ($productNames as $name) {
                    $q->orWhere('name', 'like', '%'.$name.'%');
                }
            });
        }

        return $query->get()->toArray();
    }

    private function fetchAddressData(): array
    {
        if (! $this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);

        return $team ? $team->toArray() : [];
    }

    private function fetchAvailableTimes(): array
    {
        if (! $this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (! $team) {
            return [];
        }

        return Event::where('team_id', $team->id)
            ->where('name', 'Disponibile')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc')
            ->get(['starts_at', 'ends_at', 'name', 'featured_image_id', 'description'])
            ->toArray();
    }

    private function createOrder(string $userPhone, string $deliveryDate, array $productIds): array
    {
        Log::info('WebsiteAssistantAgent.createOrder', [
            'user_phone' => $userPhone,
            'delivery_date' => $deliveryDate,
            'product_ids' => $productIds,
            'product_ids_count' => count($productIds),
            'product_ids_type' => gettype($productIds),
            'team_slug' => $this->teamSlug,
        ]);

        if (! $this->teamSlug) {
            return ['error' => 'Team non trovato'];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (! $team) {
            Log::error('WebsiteAssistantAgent.createOrder: Team not found', ['team_slug' => $this->teamSlug]);

            return ['error' => 'Team non trovato'];
        }

        if (! $userPhone || ! $deliveryDate) {
            Log::warning('WebsiteAssistantAgent.createOrder: Missing required fields', [
                'phone' => $userPhone,
                'delivery_date' => $deliveryDate,
            ]);

            return [
                'error' => "Mancano informazioni necessarie per l'ordine: numero di telefono e data di consegna obbligatori.",
                'received' => [
                    'phone' => $userPhone,
                    'delivery_date' => $deliveryDate,
                    'product_ids_count' => count((array) $productIds),
                ],
            ];
        }

        try {
            $order = new Order();
            $order->team_id = $team->id;
            $order->delivery_date = $deliveryDate;
            $order->phone = $userPhone;
            $order->save();

            Log::info('WebsiteAssistantAgent.createOrder: Order created', [
                'order_id' => $order->id,
                'team_id' => $team->id,
            ]);

            if (! empty($productIds)) {
                Log::info('WebsiteAssistantAgent.createOrder: Attaching products', [
                    'order_id' => $order->id,
                    'product_ids' => $productIds,
                    'count' => count($productIds),
                ]);

                $order->products()->attach($productIds);

                Log::info('WebsiteAssistantAgent.createOrder: Products attached', [
                    'order_id' => $order->id,
                    'product_count' => count($productIds),
                ]);
            } else {
                Log::warning('WebsiteAssistantAgent.createOrder: No products to attach', [
                    'order_id' => $order->id,
                    'product_ids' => $productIds,
                ]);
            }

            return [
                'order_id' => $order->id,
                'message' => trans('enjoywork3d_prompts.order_created_successfully', [], $this->locale ?? 'it'),
                'details' => [
                    'phone' => $userPhone,
                    'delivery_date' => $deliveryDate,
                    'products' => count((array) $productIds),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('WebsiteAssistantAgent.createOrder: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Errore durante la creazione dell\'ordine: '.$e->getMessage(),
            ];
        }
    }

    private function submitUserData(string $userPhone, string $userEmail, string $userName): array
    {
        Log::info('WebsiteAssistantAgent.submitUserData', [
            'user_phone' => $userPhone,
            'user_email' => $userEmail,
            'user_name' => $userName,
            'team_slug' => $this->teamSlug,
            'activity_uuid' => $this->activityUuid,
        ]);

        if (! $userPhone || ! $userEmail || ! $userName) {
            Log::warning('WebsiteAssistantAgent.submitUserData: Missing required fields', [
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
            if ($this->activityUuid) {
                $customer = Customer::where('uuid', $this->activityUuid)->first();
                if ($customer) {
                    $customer->phone = $userPhone;
                    $customer->email = $userEmail;
                    $customer->name = $userName;
                    $customer->save();
                    Log::info('WebsiteAssistantAgent.submitUserData: Updated existing customer', [
                        'customer_id' => $customer->id,
                    ]);
                } else {
                    if (! $this->teamSlug) {
                        return ['error' => 'Team non trovato'];
                    }
                    $team = $this->getTeamCached($this->teamSlug);
                    if (! $team) {
                        Log::error('WebsiteAssistantAgent.submitUserData: Team not found', ['team_slug' => $this->teamSlug]);

                        return ['error' => 'Team non trovato'];
                    }
                    $customer = Customer::create([
                        'uuid' => $this->activityUuid,
                        'phone' => $userPhone,
                        'email' => $userEmail,
                        'name' => $userName,
                        'team_id' => $team->id,
                    ]);
                    Log::info('WebsiteAssistantAgent.submitUserData: Created new customer', [
                        'customer_id' => $customer->id,
                    ]);
                }
            } else {
                if (! $this->teamSlug) {
                    return ['error' => 'Team non trovato'];
                }
                $team = $this->getTeamCached($this->teamSlug);
                if (! $team) {
                    Log::error('WebsiteAssistantAgent.submitUserData: Team not found', ['team_slug' => $this->teamSlug]);

                    return ['error' => 'Team non trovato'];
                }
                $customer = Customer::create([
                    'phone' => $userPhone,
                    'email' => $userEmail,
                    'name' => $userName,
                    'team_id' => $team->id,
                ]);
                Log::info('WebsiteAssistantAgent.submitUserData: Created new customer (no uuid)', [
                    'customer_id' => $customer->id,
                ]);
            }

            return [
                'success' => true,
                'message' => trans('enjoywork3d_prompts.user_data_submitted', [], $this->locale ?? 'it'),
                'customer_name' => $userName,
            ];
        } catch (\Throwable $e) {
            Log::error('WebsiteAssistantAgent.submitUserData: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Errore durante il salvataggio dei dati: '.$e->getMessage(),
            ];
        }
    }

    private function fetchFAQs(string $query): array
    {
        if (! $this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (! $team) {
            return [];
        }

        try {
            return Faq::where('team_id', $team->id)
                ->whereRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
                ->get(['question', 'answer'])
                ->toArray();
        } catch (\Throwable $e) {
            return Faq::where('team_id', $team->id)
                ->where(function ($q) use ($query) {
                    $q->where('question', 'like', '%'.$query.'%')
                        ->orWhere('answer', 'like', '%'.$query.'%');
                })
                ->get(['question', 'answer'])
                ->toArray();
        }
    }

    private function tryEmbeddingSimilarity(string $textA, string $textB): ?float
    {
        try {
            $embeddingService = $this->getEmbeddingService();
            if (! $embeddingService) {
                return null;
            }

            return $embeddingService->textSimilarity($textA, $textB);
        } catch (\Throwable $e) {
            Log::warning('WebsiteAssistantAgent.tryEmbeddingSimilarity fallback', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Cerca informazioni su un sito web usando RAG con Atlas Search, con fallback a scraping
     *
     * @param string $query Query di ricerca
     * @param string|null $url URL specifico del sito (opzionale)
     * @param int|null $maxPages Numero massimo di pagine da analizzare (solo per fallback)
     * @return array Risultato della ricerca con answer, sources e metadata
     */
    private function searchSite(string $query, ?string $url = null, ?int $maxPages = null): array
    {
        Log::info('WebsiteAssistantAgent.searchSite', [
            'query' => $query,
            'url' => $url,
            'team_slug' => $this->teamSlug,
            'max_pages' => $maxPages,
        ]);

        if (! $this->teamSlug) {
            return [
                'error' => 'Team non trovato',
                'answer' => 'Non posso cercare informazioni senza un team specificato.',
            ];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (! $team) {
            return [
                'error' => 'Team non trovato',
                'answer' => 'Non posso cercare informazioni senza un team valido.',
            ];
        }

        // Se URL non fornito, usa il primo sito del team
        if (! $url) {
            $websites = $team->websites ?? [];
            $normalizedWebsites = empty($websites) || ! is_array($websites) ? [] : $this->normalizeWebsites($websites);

            if (empty($normalizedWebsites)) {
                return [
                    'error' => 'Nessun sito web configurato',
                    'answer' => 'Non ci sono siti web configurati per questo team.',
                ];
            }

            $url = $normalizedWebsites[0];
        }

        try {
            // Usa WebScraperService che già implementa RAG con fallback
            /** @var \Modules\WebScraper\Services\WebScraperService $webScraperService */
            $webScraperService = app(\Modules\WebScraper\Services\WebScraperService::class);

            $options = [
                'max_pages' => $maxPages ?? 10,
                'top_k' => 5,
                'min_similarity' => 0.7,
            ];

            $result = $webScraperService->searchWithRag($url, $query, $options);

            Log::info('WebsiteAssistantAgent.searchSite completed', [
                'method' => $result['method'] ?? 'unknown',
                'success' => $result['success'] ?? false,
                'chunks_found' => $result['chunks_found'] ?? 0,
                'sources_count' => isset($result['sources']) ? count($result['sources']) : 0,
            ]);

            // Formatta la risposta per l'agent
            $response = [
                'answer' => $result['answer'] ?? 'Non ho trovato informazioni rilevanti.',
                'method' => $result['method'] ?? 'unknown',
            ];

            // Aggiungi le fonti se disponibili
            if (! empty($result['sources'])) {
                $sourcesList = array_map(function ($source) {
                    return ($source['title'] ?? 'Senza titolo').' - '.($source['url'] ?? '');
                }, $result['sources']);
                $response['sources'] = $sourcesList;
                $response['sources_count'] = count($result['sources']);
            }

            return $response;

        } catch (\Throwable $e) {
            Log::error('WebsiteAssistantAgent.searchSite error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $query,
                'url' => $url,
            ]);

            return [
                'error' => 'Errore durante la ricerca',
                'answer' => 'Si è verificato un errore durante la ricerca. Riprova più tardi.',
            ];
        }
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
}
