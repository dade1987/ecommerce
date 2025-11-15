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
use Modules\WebScraper\Facades\WebScraper;
use Modules\WebScraper\Services\AiAnalyzerService;
use Modules\WebScraper\Services\SearchResultCacheService;
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\ArrayProperty;

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

        if (!empty($this->websiteContent)) {
            $baseInstructions .= "\n\nPRIORITA': Contenuto dai siti web aziendali:\n\n" . $this->websiteContent;
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
                    $productsList = $products->map(fn($p) => "ID: {$p->id} | Nome: {$p->name} | Prezzo: {$p->price}")
                        ->join("\n");
                    $baseInstructions .= "\n\nProdotti/Servizi disponibili:\n" . $productsList;
                }
            }
        }

        Log::debug('WebsiteAssistantAgent.instructions', [
            'prompt_length' => strlen($baseInstructions),
            'has_website_content' => !empty($this->websiteContent),
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
            $this->createFallbackTool(),
            $this->createScrapeSiteTool(),
            $this->createScrapeUrlTool(),
            $this->createSearchSiteTool(),
        ];

        Log::debug('WebsiteAssistantAgent.tools', [
            'tools_count' => count($toolsList),
            'tool_names' => array_map(fn($t) => $t->getName(), $toolsList),
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
            ->setCallable(fn (string $user_phone, string $delivery_date, array $product_ids) => 
                $this->createOrder($user_phone, $delivery_date, $product_ids)
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
            ->setCallable(fn (string $user_phone, string $user_email, string $user_name) => 
                $this->submitUserData($user_phone, $user_email, $user_name)
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

    private function createFallbackTool(): Tool
    {
        return Tool::make(
            name: 'fallback',
            description: 'Risponde a domande non inerenti al contesto con il messaggio predefinito.'
        )
            ->setCallable(fn () => [
                'message' => trans('enjoywork3d_prompts.fallback_message', [], $this->locale ?? 'it')
            ]);
    }

    private function createScrapeSiteTool(): Tool
    {
        return Tool::make(
            name: 'scrapeSite',
            description: "Recupera il contenuto del sito web del cliente per rispondere a domande sull'attività."
        )
            ->addProperty(
                ToolProperty::make(
                    name: 'user_uuid',
                    type: PropertyType::STRING,
                    description: "UUID che identifica univocamente l'attività del cliente.",
                    required: true
                )
            )
            ->setCallable(fn (string $user_uuid) => $this->scrapeSite($user_uuid));
    }

    private function createScrapeUrlTool(): Tool
    {
        return Tool::make(
            name: 'scrapeUrl',
            description: "Estrae TUTTE le informazioni da una SINGOLA pagina web specifica.\n"
                . "Usa SEMPRE questa funzione quando:\n"
                . "- L'utente fornisce un URL specifico di una pagina prodotto (es: Amazon, eBay, e-commerce con /dp/, /product/, /item/)\n"
                . "- L'URL NON è una homepage ma una pagina interna specifica\n"
                . "- L'utente chiede \"caratteristiche\", \"dettagli\", \"specifiche\", \"descrizione\" di UN prodotto/articolo specifico\n"
                . "Questa funzione analizza in profondità UNA SOLA pagina ed estrae tutto il suo contenuto."
        )
            ->addProperty(
                ToolProperty::make(
                    name: 'url',
                    type: PropertyType::STRING,
                    description: "L'URL completo della pagina specifica da analizzare (es: https://www.amazon.it/prodotto/dp/B07MW4D7LG/)",
                    required: true
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'query',
                    type: PropertyType::STRING,
                    description: "Cosa estrarre dalla pagina (es: \"tutte le caratteristiche del prodotto\", \"prezzo e descrizione\", \"specifiche tecniche\")",
                    required: true
                )
            )
            ->setCallable(fn (string $url, string $query) => $this->scrapeUrl($url, $query));
    }

    private function createSearchSiteTool(): Tool
    {
        return Tool::make(
            name: 'searchSite',
            description: "Cerca informazioni attraverso MULTIPLE pagine di un sito web.\n"
                . "Usa questa funzione quando:\n"
                . "- L'utente fornisce ESPLICITAMENTE un URL specifico (es: \"cerca nel sito https://example.com\", \"trova servizi su https://isofin.it\")\n"
                . "- L'utente dice \"cerca nel sito web [URL]\" - usa SEMPRE quell'URL esatto, NON il sito del consumer corrente\n"
                . "- L'utente chiede di cercare qualcosa in \"tutto il sito\", \"nelle pagine del sito\"\n"
                . "- Serve esplorare più pagine per trovare informazioni distribuite\n\n"
                . "IMPORTANTE: Se l'utente specifica un URL esplicito nel prompt, usa SEMPRE quell'URL nel parametro \"url\", NON usare il sito del consumer corrente.\n"
                . "ESEMPI:\n"
                . "- \"cerca nel sito https://isofin.it i servizi\" → usa url=\"https://isofin.it\"\n"
                . "- \"trova prodotti su https://example.com\" → usa url=\"https://example.com\"\n"
                . "- \"cerca informazioni nel mio sito\" → NON usare questa funzione, usa scrapeSite invece\n\n"
                . "NON usare per singole pagine prodotto o URL specifici di una pagina."
        )
            ->addProperty(
                ToolProperty::make(
                    name: 'url',
                    type: PropertyType::STRING,
                    description: "L'URL del sito web da esplorare (homepage o URL di partenza)",
                    required: true
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'query',
                    type: PropertyType::STRING,
                    description: "Cosa cercare attraverso le pagine del sito (es: \"trova tutti i prezzi dei prodotti\", \"cerca informazioni sui servizi\", \"trova tutte le pagine di contatto\")",
                    required: true
                )
            )
            ->addProperty(
                ToolProperty::make(
                    name: 'max_pages',
                    type: PropertyType::INTEGER,
                    description: "Numero massimo di pagine da analizzare (default: 10)",
                    required: true
                )
            )
            ->setCallable(fn (string $url, string $query, int $max_pages = 10) => $this->searchSite($url, $query, $max_pages));
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

        if (!$this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (!$team) {
            return [];
        }

        $query = Product::where('team_id', $team->id);
        if (!empty($productNames)) {
            $query->where(function ($q) use ($productNames) {
                foreach ($productNames as $name) {
                    $q->orWhere('name', 'like', '%' . $name . '%');
                }
            });
        }

        return $query->get()->toArray();
    }

    private function fetchAddressData(): array
    {
        if (!$this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);
        return $team ? $team->toArray() : [];
    }

    private function fetchAvailableTimes(): array
    {
        if (!$this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);
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

        if (!$this->teamSlug) {
            return ['error' => 'Team non trovato'];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (!$team) {
            Log::error('WebsiteAssistantAgent.createOrder: Team not found', ['team_slug' => $this->teamSlug]);
            return ['error' => 'Team non trovato'];
        }

        if (!$userPhone || !$deliveryDate) {
            Log::warning('WebsiteAssistantAgent.createOrder: Missing required fields', [
                'phone' => $userPhone,
                'delivery_date' => $deliveryDate,
            ]);
            return [
                'error' => "Mancano informazioni necessarie per l'ordine: numero di telefono e data di consegna obbligatori.",
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

            Log::info('WebsiteAssistantAgent.createOrder: Order created', [
                'order_id' => $order->id,
                'team_id' => $team->id,
            ]);

            if (!empty($productIds)) {
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
                    'products' => count((array)$productIds),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('WebsiteAssistantAgent.createOrder: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'error' => 'Errore durante la creazione dell\'ordine: ' . $e->getMessage(),
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

        if (!$userPhone || !$userEmail || !$userName) {
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
                    if (!$this->teamSlug) {
                        return ['error' => 'Team non trovato'];
                    }
                    $team = $this->getTeamCached($this->teamSlug);
                    if (!$team) {
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
                if (!$this->teamSlug) {
                    return ['error' => 'Team non trovato'];
                }
                $team = $this->getTeamCached($this->teamSlug);
                if (!$team) {
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
                'error' => 'Errore durante il salvataggio dei dati: ' . $e->getMessage(),
            ];
        }
    }

    private function fetchFAQs(string $query): array
    {
        if (!$this->teamSlug) {
            return [];
        }

        $team = $this->getTeamCached($this->teamSlug);
        if (!$team) {
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
                    $q->where('question', 'like', '%' . $query . '%')
                        ->orWhere('answer', 'like', '%' . $query . '%');
                })
                ->get(['question', 'answer'])
                ->toArray();
        }
    }

    /**
     * Scrape website with intelligent parsing + AI analysis on how AI can help the business.
     * Usa il modulo WebScraper per estrarre e analizzare il contenuto del sito.
     */
    private function scrapeSite(?string $userUuid): array
    {
        Log::info('WebsiteAssistantAgent.scrapeSite: Inizio recupero Customer da uuid', ['userUuid' => $userUuid]);

        if (!$userUuid) {
            return ['error' => "Nessun UUID fornito per l'utente/attività."];
        }

        $customer = Customer::where('uuid', $userUuid)->first();
        if (!$customer) {
            Log::warning('WebsiteAssistantAgent.scrapeSite: Nessun customer trovato', ['userUuid' => $userUuid]);
            return ['error' => 'Nessun cliente trovato per l\'UUID fornito.'];
        }

        if (!$customer->website) {
            Log::warning('WebsiteAssistantAgent.scrapeSite: Nessun sito web associato a questo customer', ['userUuid' => $userUuid]);
            return ['error' => 'Nessun sito web specificato per questo utente.'];
        }

        try {
            $scrapedData = WebScraper::scrape($customer->website);

            if (isset($scrapedData['error'])) {
                Log::error('WebsiteAssistantAgent.scrapeSite: Errore nello scraping', ['error' => $scrapedData['error']]);
                return ['error' => 'Impossibile recuperare il contenuto del sito.'];
            }

            $analyzer = app(AiAnalyzerService::class);
            $analysis = $analyzer->analyzeBusinessInfo($scrapedData);

            if (isset($analysis['error'])) {
                Log::error('WebsiteAssistantAgent.scrapeSite: Errore durante l\'analisi AI', ['error' => $analysis['error']]);
                return ['error' => 'Impossibile generare un riepilogo. Errore AI.'];
            }

            Log::info('WebsiteAssistantAgent.scrapeSite: Scraping e analisi completati', [
                'url' => $customer->website,
                'content_length' => strlen($scrapedData['content']['main']),
                'tokens_used' => $analysis['usage']['total_tokens'] ?? 0,
            ]);

            return [
                'site_content' => $scrapedData['content']['main'],
                'ai_analysis' => $analysis['analysis'],
                'metadata' => $scrapedData['metadata'],
            ];
        } catch (\Throwable $e) {
            Log::error('WebsiteAssistantAgent.scrapeSite: Errore imprevisto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Si è verificato un errore durante l\'elaborazione.'];
        }
    }

    /**
     * Scrape a specific URL with custom query for targeted information extraction.
     */
    private function scrapeUrl(string $url, string $query): array
    {
        Log::info('WebsiteAssistantAgent.scrapeUrl: Inizio scraping URL con query personalizzata', [
            'url' => $url,
            'query' => $query,
        ]);

        if (empty($url)) {
            return ['error' => 'URL non fornito.'];
        }

        if (empty($query)) {
            return ['error' => 'Query di ricerca non fornita.'];
        }

        try {
            $isProductPage = $this->isProductPage($url);

            if ($isProductPage) {
                Log::info('WebsiteAssistantAgent.scrapeUrl: Detected product page, scraping entire content', ['url' => $url]);

                $scraper = app(\Modules\WebScraper\Services\WebScraperService::class);
                $scrapedData = $scraper->scrape($url, ['query' => $query]);

                if (isset($scrapedData['error'])) {
                    return ['error' => $scrapedData['error']];
                }

                $analyzer = app(AiAnalyzerService::class);
                $analysis = $analyzer->extractCustomInfo($scrapedData, $query);

                if (isset($analysis['error'])) {
                    Log::error('WebsiteAssistantAgent.scrapeUrl: Errore durante l\'analisi AI', [
                        'url' => $url,
                        'query' => $query,
                        'error' => $analysis['error'],
                    ]);
                    return ['error' => 'Impossibile analizzare il contenuto: ' . $analysis['error']];
                }

                Log::info('WebsiteAssistantAgent.scrapeUrl: Analisi AI completata (product page)', [
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
                        ],
                    ],
                ];
            }

            Log::info('WebsiteAssistantAgent.scrapeUrl: Using intelligent search with caching', ['url' => $url, 'query' => $query]);

            $cacheService = app(SearchResultCacheService::class);
            $cachedResults = $cacheService->getCachedOrSearch($url, $query, 3);
            $resultsCount = is_array($cachedResults['results']) ? count($cachedResults['results']) : $cachedResults['results'];

            if ($cachedResults['from_cache'] ?? false) {
                Log::info('WebsiteAssistantAgent.scrapeUrl: Returning cached results', [
                    'url' => $url,
                    'query' => $query,
                    'results_count' => $resultsCount,
                    'cached_at' => $cachedResults['cached_at'] ?? null,
                ]);

                if (isset($cachedResults['reformulated_summary'])) {
                    return [
                        'url' => $url,
                        'query' => $query,
                        'analysis' => $cachedResults['reformulated_summary'],
                        'pages_found' => $resultsCount,
                        'pages_visited' => $cachedResults['pages_visited'],
                        'results' => $cachedResults['results'],
                        'from_cache' => true,
                        'cached_at' => $cachedResults['cached_at'],
                    ];
                }
            }

            $searchResults = [
                'results' => $cachedResults['results'],
                'pages_visited' => $cachedResults['pages_visited'],
                'query' => $cachedResults['query'],
            ];

            if (empty($searchResults['results'])) {
                Log::warning('WebsiteAssistantAgent.scrapeUrl: Nessun risultato trovato', [
                    'url' => $url,
                    'query' => $query,
                    'pages_visited' => $searchResults['pages_visited'] ?? 0,
                ]);
                return ['error' => 'Non ho trovato informazioni rilevanti per la query richiesta.'];
            }

            Log::info('WebsiteAssistantAgent.scrapeUrl: Ricerca intelligente completata', [
                'url' => $url,
                'query' => $query,
                'results_found' => $resultsCount,
                'pages_visited' => $searchResults['pages_visited'],
            ]);

            $aggregatedContent = '';
            foreach ($searchResults['results'] as $result) {
                $aggregatedContent .= "\n\n=== {$result['title']} ({$result['url']}) ===\n";
                $aggregatedContent .= $result['content_excerpt'];
            }

            $analyzer = app(AiAnalyzerService::class);
            $mockScrapedData = [
                'url' => $url,
                'content' => ['main' => $aggregatedContent],
                'metadata' => ['title' => 'Risultati aggregati'],
            ];

            $analysis = $analyzer->extractCustomInfo($mockScrapedData, $query);

            if (isset($analysis['error'])) {
                Log::error('WebsiteAssistantAgent.scrapeUrl: Errore durante l\'analisi AI', [
                    'url' => $url,
                    'query' => $query,
                    'error' => $analysis['error'],
                ]);
                return ['error' => 'Impossibile analizzare il contenuto: ' . $analysis['error']];
            }

            Log::info('WebsiteAssistantAgent.scrapeUrl: Analisi AI completata', [
                'url' => $url,
                'query' => $query,
                'tokens_used' => $analysis['usage']['total_tokens'] ?? 0,
            ]);

            return [
                'url' => $url,
                'query' => $query,
                'analysis' => $analysis['analysis'],
                'pages_found' => $resultsCount,
                'pages_visited' => $searchResults['pages_visited'],
                'results' => $searchResults['results'],
            ];
        } catch (\Throwable $e) {
            Log::error('WebsiteAssistantAgent.scrapeUrl: Errore imprevisto', [
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
     * Usa ricerca intelligente multi-pagina con caching.
     */
    private function searchSite(string $url, string $query, int $maxPages = 10): array
    {
        Log::info('WebsiteAssistantAgent.searchSite: Inizio ricerca intelligente con caching', [
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
            $cacheService = app(SearchResultCacheService::class);
            $cachedResults = $cacheService->getCachedOrSearch($url, $query, 3);

            $resultsCount = is_array($cachedResults['results']) ? count($cachedResults['results']) : $cachedResults['results'];

            if ($cachedResults['from_cache'] ?? false) {
                Log::info('WebsiteAssistantAgent.searchSite: Returning cached results', [
                    'url' => $url,
                    'query' => $query,
                    'results_count' => $resultsCount,
                    'cached_at' => $cachedResults['cached_at'] ?? null,
                ]);

                if (isset($cachedResults['reformulated_summary'])) {
                    return [
                        'url' => $url,
                        'query' => $query,
                        'pages_visited' => $cachedResults['pages_visited'],
                        'results_found' => $resultsCount,
                        'analysis' => $cachedResults['reformulated_summary'],
                        'from_cache' => true,
                        'cached_at' => $cachedResults['cached_at'],
                    ];
                }

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
                    'results_found' => $resultsCount,
                    'analysis' => 'Risultati trovati: ' . implode(', ', array_column($foundPages, 'title')),
                    'found_pages' => $foundPages,
                    'from_cache' => true,
                ];
            }

            $searchResults = $cachedResults;

            if (empty($searchResults['results'])) {
                Log::warning('WebsiteAssistantAgent.searchSite: Nessun risultato trovato', ['url' => $url, 'query' => $query]);
                return [
                    'url' => $url,
                    'query' => $query,
                    'pages_visited' => $searchResults['pages_visited'],
                    'analysis' => 'Non sono state trovate informazioni specifiche su "' . $query . '" nelle pagine visitate.',
                ];
            }

            $foundPages = [];
            $aggregatedData = [];
            $scraper = app(\Modules\WebScraper\Services\WebScraperService::class);

            Log::info('WebsiteAssistantAgent.searchSite: Scraping individual pages', ['urls_count' => count($searchResults['results'])]);

            foreach ($searchResults['results'] as $result) {
                $foundPages[] = [
                    'url' => $result['url'],
                    'title' => $result['title'],
                    'depth' => $result['depth'] ?? 0,
                ];

                $scrapedData = $scraper->scrape($result['url'], ['query' => $query]);

                if (!isset($scrapedData['error'])) {
                    $aggregatedData[] = $scrapedData;
                }
            }

            Log::info('WebsiteAssistantAgent.searchSite: Pages scraped for AI analysis', ['pages_count' => count($aggregatedData)]);

            $analyzer = app(AiAnalyzerService::class);
            $analysis = $analyzer->searchMultiplePages($aggregatedData, $query);
            $aiAnalysisText = $analysis['analysis'] ?? 'Analisi completata';

            Log::info('WebsiteAssistantAgent.searchSite: Ricerca completata', [
                'url' => $url,
                'query' => $query,
                'pages_visited' => $searchResults['pages_visited'],
                'results_found' => count($searchResults['results']),
            ]);

            $cacheService->cacheResults(
                $url,
                $query,
                $searchResults['results'],
                $searchResults['pages_visited'],
                $aiAnalysisText
            );

            return [
                'url' => $url,
                'query' => $query,
                'pages_visited' => $searchResults['pages_visited'],
                'results_found' => count($searchResults['results']),
                'analysis' => $aiAnalysisText,
                'found_pages' => $foundPages,
                'from_cache' => false,
            ];
        } catch (\Throwable $e) {
            Log::error('WebsiteAssistantAgent.searchSite: Errore imprevisto', [
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
     */
    private function isProductPage(string $url): bool
    {
        $productPatterns = [
            '/amazon\.[a-z.]+\/(dp|product|gp\/product)\/[A-Z0-9]{10}/i',
            '/ebay\.[a-z.]+\/itm\//i',
            '/\/products\/[^\/]+\/?$/i',
            '/\/(item|prodotto|articolo|product)\/[^\/]+/i',
            '/\/product\/[^\/]+\/?$/i',
            '/\/[a-z0-9-]+\.html$/i',
        ];

        foreach ($productPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        $nonProductPatterns = [
            '/^https?:\/\/[^\/]+\/?$/i',
            '/^https?:\/\/[^\/]+\/index\.(html|php)$/i',
            '/\/(category|categories|collection|collections|c)\//i',
        ];

        foreach ($nonProductPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return false;
            }
        }

        return false;
    }

    private function tryEmbeddingSimilarity(string $textA, string $textB): ?float
    {
        try {
            $embeddingService = $this->getEmbeddingService();
            if (!$embeddingService) {
                return null;
            }
            return $embeddingService->textSimilarity($textA, $textB);
        } catch (\Throwable $e) {
            Log::warning('WebsiteAssistantAgent.tryEmbeddingSimilarity fallback', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
