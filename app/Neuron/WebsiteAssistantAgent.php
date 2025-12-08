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
            model: 'gpt-4o', // Changed from gpt-4o-mini to gpt-4o for better instruction following
        );
    }

    /**
     * System prompt con contesto dei siti web e azienda
     */
    public function instructions(): string
    {
        // Normalizza la locale in forma breve ('it', 'en', ecc.) per usare i file di lingua corretti.
        // Esempio: 'it-IT' o 'it_IT' -> 'it'; 'en-US' -> 'en'.
        $rawLocale = $this->locale ?? 'it';
        $normalized = str_replace('_', '-', (string) $rawLocale);
        $parts = explode('-', $normalized);
        $baseLocale = strtolower($parts[0] ?: 'it');

        // Limita alle lingue effettivamente disponibili per questo prompt
        if (! in_array($baseLocale, ['it', 'en'], true)) {
            $baseLocale = config('app.locale', 'it');
        }

        $locale = $baseLocale;

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

        // Comandi speciali per controllare in modo NON ambiguo quali tool usare.
        $baseInstructions .= <<<'TXT'


COMANDI SPECIALI PER LA SCELTA DEI TOOL (MOLTO IMPORTANTI):

- Se il messaggio dell'utente INIZIA con la frase esatta "cerca nel sito" (case-insensitive, ad es. "Cerca nel sito ..."):
  * considera tutto il testo DOPO "cerca nel sito" come query di ricerca;
  * DEVI usare SOLO il tool `searchSite` per rispondere a quella richiesta;
  * in questa modalità NON devi mai chiamare tool che leggono direttamente dal database
    (`getProductInfo`, `getAddressInfo`, `getAvailableTimes`, `createOrder`, `submitUserData`, `getFAQs`).

- Se il messaggio dell'utente INIZIA con la frase esatta "cerca nel database" (case-insensitive):
  * considera tutto il testo DOPO "cerca nel database" come domanda sui dati strutturati del gestionale
    (prodotti, servizi, ordini, clienti, FAQ, orari, ecc.);
  * DEVI usare SOLO i tool che lavorano sul database applicativo:
    `getProductInfo`, `getAddressInfo`, `getAvailableTimes`, `createOrder`, `submitUserData`, `getFAQs`;
  * in questa modalità NON devi mai usare il tool `searchSite`.

- Se il messaggio NON inizia con nessuna di queste due frasi speciali:
  * scegli liberamente i tool più adatti in base alla domanda,
    seguendo comunque le descrizioni dei tool;
  * se le parole "cerca nel sito" o "cerca nel database" compaiono solo nel mezzo della frase,
    NON attivare i comandi speciali: contano solo se la frase dell'utente INIZIA con quel testo.
TXT;

        Log::debug('WebsiteAssistantAgent.instructions', [
            'prompt_length' => strlen($baseInstructions),
            'has_website_content' => ! empty($this->websiteContent),
            'raw_locale' => (string) $rawLocale,
            'effective_locale' => $locale,
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
            $this->createScrapeSiteTool(),
            $this->createScrapeUrlTool(),
            $this->createSearchSiteTool(),
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
                . "Usa SEMPRE questa funzione quando:\n"
                . "- L'utente chiede di CERCARE informazioni, prodotti, servizi, contenuti (es: \"cerca tagliatelle\", \"trova prodotti\", \"cerca articoli su XYZ\")\n"
                . "- L'utente fornisce ESPLICITAMENTE un URL specifico (es: \"cerca nel sito https://example.com\", \"trova servizi su https://isofin.it\")\n"
                . "- L'utente dice \"cerca nel sito web [URL]\" - usa SEMPRE quell'URL esatto, NON il sito del consumer corrente\n"
                . "- L'utente chiede di cercare qualcosa in \"tutto il sito\", \"nelle pagine del sito\"\n"
                . "- Serve esplorare più pagine per trovare informazioni distribuite\n\n"
                . "IMPORTANTE:\n"
                . "- Se l'utente specifica un URL esplicito nel prompt, usa SEMPRE quell'URL nel parametro \"url\"\n"
                . "- Se l'utente NON specifica un URL ma chiede di CERCARE qualcosa, usa il sito del consumer corrente come url\n"
                . "- Se hai già il consumer.website disponibile nel contesto e l'utente fa una query di ricerca generica, usa consumer.website come url\n\n"
                . "ESEMPI:\n"
                . "- \"cerca tagliatelle al ragù\" → searchSite(url=consumer.website, query=\"tagliatelle al ragù\")\n"
                . "- \"trova prodotti con infissi\" → searchSite(url=consumer.website, query=\"prodotti con infissi\")\n"
                . "- \"cerca nel sito https://isofin.it i servizi\" → searchSite(url=\"https://isofin.it\", query=\"servizi\")\n"
                . "- \"trova prodotti su https://example.com\" → searchSite(url=\"https://example.com\", query=\"prodotti\")\n\n"
                . "NON usare per singole pagine prodotto con URL specifico di una pagina - usa scrapeUrl invece."
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
                    description: "Numero massimo di pagine da analizzare (opzionale, gestito automaticamente in base al tipo di ricerca)",
                    required: false
                )
            )
            ->setCallable(fn (string $url, string $query, ?int $max_pages = null) => $this->searchSite($url, $query, $max_pages));
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

                $scrapedData = WebScraper::scrape($url, ['query' => $query]);

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
     * max_pages is now managed by SearchStrategy pattern - ignored here
     */
    private function searchSite(string $url, string $query, ?int $maxPages = null): array
    {
        Log::info('WebsiteAssistantAgent.searchSite: Starting RAG-powered search', [
            'url' => $url,
            'query' => $query,
            'max_pages' => $maxPages,
        ]);

        if (empty($url)) {
            return ['error' => 'URL non fornito.'];
        }

        if (empty($query)) {
            return ['error' => 'Query di ricerca non fornita.'];
        }

        try {
            // Use new RAG-powered search (tries indexed content first, falls back to scraping)
            $scraper = app(\Modules\WebScraper\Services\WebScraperService::class);
            $ragResult = $scraper->searchWithRag(
                $url,
                $query,
                [
                    'max_pages' => $maxPages ?? 10,
                    'ttl_days' => 30,
                    'top_k' => 10, // Reduced to fit GPT-3.5-turbo 16K context limit
                    'min_similarity' => 0.7,
                ]
            );

            // Format output for Agent
            if ($ragResult['success']) {
                $method = $ragResult['method'] ?? 'unknown';

                Log::info('WebsiteAssistantAgent.searchSite: Search completed', [
                    'url' => $url,
                    'query' => $query,
                    'method' => $method,
                    'chunks_found' => $ragResult['chunks_found'] ?? null,
                ]);

                $output = [
                    'url' => $url,
                    'query' => $query,
                    'analysis' => $ragResult['answer'],
                    'method' => $method,
                    'from_cache' => false,
                ];

                // Add method-specific metadata
                if ($method === 'rag') {
                    $output['chunks_found'] = $ragResult['chunks_found'];
                    $output['sources'] = $ragResult['sources'];
                    $output['summary'] = sprintf(
                        'Ho trovato %d contenuti rilevanti nel database indicizzato per "%s".',
                        $ragResult['chunks_found'],
                        $query
                    );
                } elseif ($method === 'scraping_with_indexing') {
                    $output['pages_visited'] = $ragResult['pages_visited'];
                    $output['indexed_for_future'] = true;
                    $output['summary'] = sprintf(
                        'Ho analizzato %d pagine del sito %s per "%s" e le ho indicizzate per ricerche future.',
                        $ragResult['pages_visited'],
                        $url,
                        $query
                    );
                }

                return $output;
            }

            // Handle failure
            return [
                'url' => $url,
                'query' => $query,
                'analysis' => $ragResult['answer'] ?? 'Nessuna informazione trovata.',
                'error' => $ragResult['error'] ?? null,
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
            $answer = $result['answer'] ?? 'Non ho trovato informazioni rilevanti.';
            $method = $result['method'] ?? 'unknown';

            $response = [
                'answer' => $answer,
                'method' => $method,
            ];

            // Aggiungi le fonti (sia come metadato che in coda al testo, con link cliccabili)
            if (! empty($result['sources']) && is_array($result['sources'])) {
                $sourcesList = [];
                $markdownLinks = [];

                foreach ($result['sources'] as $source) {
                    $title = $source['title'] ?? 'Senza titolo';
                    $url = $source['url'] ?? '';

                    $sourcesList[] = $title.' - '.$url;

                    if ($url !== '') {
                        $markdownLinks[] = '['.$title.']('.$url.')';
                    } else {
                        $markdownLinks[] = $title;
                    }
                }

                $response['sources'] = $sourcesList;
                $response['sources_count'] = count($sourcesList);
                $response['answer'] = rtrim($answer)."\n\n".'📚 Fonti: '.implode(' · ', $markdownLinks);
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
