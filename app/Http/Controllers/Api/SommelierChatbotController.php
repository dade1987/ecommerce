<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\preg_replace;

class SommelierChatbotController extends Controller
{
    public OpenAIClient $client;

    public function __construct()
    {
        $apiKey = config('openapi.key');
        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Crea un nuovo thread di conversazione.
     */
    public function createThread()
    {
        $thread = $this->client->threads()->create([]);
        Log::info('Thread creato: '.$thread->id);

        return response()->json([
            'thread_id' => $thread->id,
        ]);
    }

    /**
     * Gestisce la conversazione del chatbot sommelier.
     */
    public function handleChat(Request $request)
    {
        Log::info('handleChat: Inizio elaborazione richiesta');
        $threadId = $request->input('thread_id');
        $userInput = $request->input('message');

        // Se non esiste thread_id, creane uno nuovo
        if (! $threadId) {
            $thread = $this->createThread();
            $threadId = $thread->getData()->thread_id;
            Log::info('handleChat: Nuovo thread creato', ['thread_id' => $threadId]);
        }

        Log::info('handleChat: Messaggio utente ricevuto', [
            'message' => $userInput,
            'thread_id' => $threadId,
        ]);

        // Salva il messaggio dell'utente su Quoter
        Quoter::create([
            'thread_id' => $threadId,
            'role'      => 'user',
            'content'   => $userInput,
        ]);

        // Invia il messaggio utente al thread OpenAI
        $this->client->threads()->messages()->create($threadId, [
            'role'    => 'user',
            'content' => $userInput,
        ]);

        // Risposta di benvenuto se l'utente scrive "buongiorno"
        if (strtolower($userInput) === 'buongiorno') {
            $welcomeMessage = 'Benvenuto nel nostro sommelier virtuale! Sono qui per consigliarti sui vini, abbinamenti cibo-vino, curiosità ed eventi. Come posso aiutarti oggi?';
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

        // Crea il run con i tool per la gestione del sommelier
        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => config('openapi.assistant_id'),
                'instructions' => 'Se l’utente richiede informazioni su un vino, esegui la function call getWineInfo. Se chiede suggerimenti per abbinamenti cibo-vino, esegui getWinePairing. Se vuole conoscere curiosità o aneddoti sul vino, esegui getWineTrivia. Se l’utente chiede eventi, degustazioni o promozioni, esegui getWineEvents. Per domande non inerenti al contesto enologico, utilizza la function fallback con il messaggio "Per ulteriori dettagli contatta il nostro sommelier". Prima di rispondere, chiedi il nome dell’utente per personalizzare l’esperienza.',
                'model'        => 'gpt-4o',
                'tools'        => [
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getWineInfo',
                            'description' => 'Recupera informazioni dettagliate su un vino specifico (nome, regione, annata).',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'wine_name'   => [
                                        'type'        => 'string',
                                        'description' => 'Nome del vino.',
                                    ],
                                    'wine_region' => [
                                        'type'        => 'string',
                                        'description' => 'Regione di provenienza.',
                                    ],
                                    'vintage'     => [
                                        'type'        => 'string',
                                        'description' => 'Annata del vino.',
                                    ],
                                ],
                                'required'   => [],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getWinePairing',
                            'description' => 'Suggerisce abbinamenti cibo-vino basati sul piatto indicato e le preferenze.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'food_dish'        => [
                                        'type'        => 'string',
                                        'description' => 'Nome o descrizione del piatto.',
                                    ],
                                    'wine_preferences' => [
                                        'type'        => 'string',
                                        'description' => 'Preferenze sul tipo di vino (es. rosso, bianco, frizzante).',
                                    ],
                                ],
                                'required'   => ['food_dish'],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getWineTrivia',
                            'description' => 'Fornisce curiosità, aneddoti e informazioni storiche sul mondo del vino.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'topic' => [
                                        'type'        => 'string',
                                        'description' => 'Argomento o tema specifico (opzionale).',
                                    ],
                                ],
                                'required'   => [],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'getWineEvents',
                            'description' => 'Recupera informazioni su eventi, degustazioni o promozioni legate ai vini.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => [
                                    'region' => [
                                        'type'        => 'string',
                                        'description' => 'Regione per la quale cercare eventi (opzionale).',
                                    ],
                                ],
                                'required'   => [],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'function',
                        'function' => [
                            'name'        => 'fallback',
                            'description' => 'Risponde a domande non inerenti al contesto enologico con un messaggio predefinito.',
                            'parameters'  => [
                                'type'       => 'object',
                                'properties' => new \stdClass(),
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Gestione del function calling
        $run = $this->retrieveRunResult($threadId, $run->id);

        while ($run->status === 'requires_action') {
            $requiredAction = $run->requiredAction;
            $toolOutputs = [];

            foreach ($requiredAction->submitToolOutputs->toolCalls as $toolCall) {
                $functionCall = $toolCall->function;
                Log::info('Esecuzione funzione', ['function_name' => $functionCall->name]);

                if ($functionCall->name === 'getWineInfo') {
                    $arguments = json_decode($functionCall->arguments, true);
                    $wineName = $arguments['wine_name'] ?? '';
                    $wineRegion = $arguments['wine_region'] ?? '';
                    $vintage = $arguments['vintage'] ?? '';
                    $wineData = $this->fetchWineInfo($wineName, $wineRegion, $vintage);
                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall->id,
                        'output'       => json_encode($wineData),
                    ];
                } elseif ($functionCall->name === 'getWinePairing') {
                    $arguments = json_decode($functionCall->arguments, true);
                    $foodDish = $arguments['food_dish'] ?? '';
                    $winePreferences = $arguments['wine_preferences'] ?? '';
                    $pairingData = $this->fetchWinePairing($foodDish, $winePreferences);
                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall->id,
                        'output'       => json_encode($pairingData),
                    ];
                } elseif ($functionCall->name === 'getWineTrivia') {
                    $arguments = json_decode($functionCall->arguments, true);
                    $topic = $arguments['topic'] ?? '';
                    $triviaData = $this->fetchWineTrivia($topic);
                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall->id,
                        'output'       => json_encode($triviaData),
                    ];
                } elseif ($functionCall->name === 'getWineEvents') {
                    $arguments = json_decode($functionCall->arguments, true);
                    $region = $arguments['region'] ?? '';
                    $eventsData = $this->fetchWineEvents($region);
                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall->id,
                        'output'       => json_encode($eventsData),
                    ];
                } elseif ($functionCall->name === 'fallback') {
                    $fallbackMessage = 'Per ulteriori dettagli contatta il nostro sommelier';
                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall->id,
                        'output'       => json_encode(['message' => $fallbackMessage]),
                    ];
                }
            }

            // Invia tutti gli output raccolti
            $this->client->threads()->runs()->submitToolOutputs(
                threadId: $threadId,
                runId: $run->id,
                parameters: [
                    'tool_outputs' => $toolOutputs,
                ]
            );

            $run = $this->retrieveRunResult($threadId, $run->id);
        }

        // Recupera il messaggio finale dal thread
        $messages = $this->client->threads()->messages()->list($threadId)->data;
        $content = $messages[0]->content[0]->text->value;

        // Salva la risposta del chatbot su Quoter
        Quoter::create([
            'thread_id' => $threadId,
            'role'      => 'chatbot',
            'content'   => $content,
        ]);

        $formattedContent = $this->formatResponseContent($content);

        return response()->json([
            'message'   => $formattedContent,
            'thread_id' => $threadId,
        ]);
    }

    /**
     * Recupera il risultato del run in modo sincrono.
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

    /**
     * Recupera informazioni sul vino dall'API.
     */
    private function fetchWineInfo($wineName, $wineRegion, $vintage)
    {
        Log::info('fetchWineInfo: Recupero dati vino', [
            'wineName'   => $wineName,
            'wineRegion' => $wineRegion,
            'vintage'    => $vintage,
        ]);
        $client = new Client();
        $url = 'https://cavalliniservice.com/api/wines';
        $query = [];
        if ($wineName) {
            $query['name'] = $wineName;
        }
        if ($wineRegion) {
            $query['region'] = $wineRegion;
        }
        if ($vintage) {
            $query['vintage'] = $vintage;
        }
        $response = $client->get($url, ['query' => $query]);
        $wineData = json_decode($response->getBody(), true);
        Log::info('fetchWineInfo: Dati vino ricevuti', ['wineData' => $wineData]);

        return $wineData;
    }

    /**
     * Suggerisce abbinamenti cibo-vino.
     */
    private function fetchWinePairing($foodDish, $winePreferences)
    {
        Log::info('fetchWinePairing: Recupero abbinamenti', [
            'foodDish'        => $foodDish,
            'winePreferences' => $winePreferences,
        ]);
        $client = new Client();
        $url = 'https://cavalliniservice.com/api/pairing';
        $query = ['food' => $foodDish];
        if ($winePreferences) {
            $query['preferences'] = $winePreferences;
        }
        $response = $client->get($url, ['query' => $query]);
        $pairingData = json_decode($response->getBody(), true);
        Log::info('fetchWinePairing: Abbinamenti ricevuti', ['pairingData' => $pairingData]);

        return $pairingData;
    }

    /**
     * Recupera curiosità o aneddoti sul vino.
     */
    private function fetchWineTrivia($topic)
    {
        Log::info('fetchWineTrivia: Recupero curiosità sul vino', ['topic' => $topic]);
        $client = new Client();
        $url = 'https://cavalliniservice.com/api/trivia';
        $query = [];
        if ($topic) {
            $query['topic'] = $topic;
        }
        $response = $client->get($url, ['query' => $query]);
        $triviaData = json_decode($response->getBody(), true);
        Log::info('fetchWineTrivia: Curiosità ricevute', ['triviaData' => $triviaData]);

        return $triviaData;
    }

    /**
     * Recupera informazioni su eventi, degustazioni o promozioni.
     */
    private function fetchWineEvents($region)
    {
        Log::info('fetchWineEvents: Recupero eventi e degustazioni', ['region' => $region]);
        $client = new Client();
        $url = 'https://cavalliniservice.com/api/events';
        $query = [];
        if ($region) {
            $query['region'] = $region;
        }
        $response = $client->get($url, ['query' => $query]);
        $eventsData = json_decode($response->getBody(), true);
        Log::info('fetchWineEvents: Eventi ricevuti', ['eventsData' => $eventsData]);

        return $eventsData;
    }

    /**
     * Formatta il contenuto della risposta per una migliore visualizzazione.
     */
    private function formatResponseContent($content)
    {
        $formattedContent = nl2br($content);
        $formattedContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedContent);
        $formattedContent = preg_replace('/\d+\.\s/', '<strong>$0</strong>', $formattedContent);

        return $formattedContent;
    }
}
