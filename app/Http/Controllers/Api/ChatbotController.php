<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log; // Importa il modello Team
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

        // Se non viene passato un thread_id, ne crea uno nuovo
        if (! $threadId) {
            $thread = $this->createThread();
            $threadId = $thread->getData()->thread_id;
            Log::info('handleChat: Creato nuovo thread', ['thread_id' => $threadId]);
        }

        Log::info('handleChat: Messaggio utente ricevuto', ['message' => $userInput, 'thread_id' => $threadId]);

        // Aggiungi il messaggio dell'utente al thread
        $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $userInput,
        ]);

        // Se il messaggio è "Intro", recupera il welcome message dal modello Team
        if (strtolower($userInput) === 'buongiorno') {
            $team = Team::where('slug', $teamSlug)->first();
            $welcomeMessage = $team ? $team->welcome_message : 'Benvenuto!';

            return response()->json([
                'message' => $welcomeMessage,
                'thread_id' => $threadId,
            ]);
        }

        // Crea e gestisci il run
        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                'instructions' => 'Sei un chatbot che risponde a domande sui prodotti, servizi, trattamenti, sessioni o attività offerti dal centro olistico.',
                'model' => 'gpt-4o',
                'tools' => [
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'getProductInfo',
                            'description' => 'Recupera informazioni sui prodotti, servizi, trattamenti, sessioni o attività del menu tramite i loro nomi.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'product_names' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                        'description' => 'Nomi dei prodotti, servizi, trattamenti, sessioni o attività da recuperare.',
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
                            'description' => 'Recupera informazioni sull\'indirizzo del centro olistico, compreso indirizzo e numero di telefono.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'team_slug' => [
                                        'type' => 'string',
                                        'description' => 'Slug del team per recuperare l\'indirizzo.',
                                    ],
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
                                'properties' => [
                                    'team_slug' => [
                                        'type' => 'string',
                                        'description' => 'Slug del team per recuperare gli orari disponibili.',
                                    ],
                                ],
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
                                    'user_phone' => [
                                        'type' => 'string',
                                        'description' => 'Numero di telefono dell\'utente per la prenotazione.',
                                    ],
                                    'delivery_date' => [
                                        'type' => 'string',
                                        'description' => 'Data di consegna dell\'ordine.',
                                    ],
                                    'product_ids' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'description' => 'ID dei prodotti, servizi, trattamenti, sessioni o attività da includere nell\'ordine.',
                                    ],
                                ],
                                'required' => ['user_phone', 'delivery_date', 'product_ids'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Recupera il risultato del run
        $run = $this->retrieveRunResult($threadId, $run->id);

        // Gestione del function calling
        if ($run->status === 'requires_action') {
            $requiredAction = $run->requiredAction;
            $functionCall = $requiredAction->submitToolOutputs->toolCalls[0]->function;

            if ($functionCall->name === 'getProductInfo') {
                $arguments = json_decode($functionCall->arguments, true);
                $productNames = $arguments['product_names'] ?? [];

                // Recupera i dati dei prodotti
                $productData = $this->fetchProductData($productNames, $teamSlug);

                // Memorizza gli ID dei prodotti in un cookie
                $productIds = array_column($productData, 'id');

                // Invia i risultati a GPT
                $this->client->threads()->runs()->submitToolOutputs(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => [
                            [
                                'tool_call_id' => $requiredAction->submitToolOutputs->toolCalls[0]->id,
                                'output' => json_encode($productData),
                            ],
                        ],
                    ]
                );

                // Recupera la risposta finale
                $run = $this->retrieveRunResult($threadId, $run->id);
            } elseif ($functionCall->name === 'getAddressInfo') {
                $arguments = json_decode($functionCall->arguments, true);

                // Recupera i dati dell'indirizzo
                $addressData = $this->fetchAddressData($teamSlug);

                // Invia i risultati a GPT
                $this->client->threads()->runs()->submitToolOutputs(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => [
                            [
                                'tool_call_id' => $requiredAction->submitToolOutputs->toolCalls[0]->id,
                                'output' => json_encode($addressData),
                            ],
                        ],
                    ]
                );

                // Recupera la risposta finale
                $run = $this->retrieveRunResult($threadId, $run->id);
            } elseif ($functionCall->name === 'getAvailableTimes') {
                $arguments = json_decode($functionCall->arguments, true);

                // Recupera gli orari disponibili
                $availableTimes = $this->fetchAvailableTimes($teamSlug);

                // Invia i risultati a GPT
                $this->client->threads()->runs()->submitToolOutputs(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => [
                            [
                                'tool_call_id' => $requiredAction->submitToolOutputs->toolCalls[0]->id,
                                'output' => json_encode($availableTimes),
                            ],
                        ],
                    ]
                );

                // Recupera la risposta finale
                $run = $this->retrieveRunResult($threadId, $run->id);
            } elseif ($functionCall->name === 'createOrder') {
                $arguments = json_decode($functionCall->arguments, true);
                $userPhone = $arguments['user_phone'];
                $deliveryDate = $arguments['delivery_date'];
                $productIds = $arguments['product_ids']; // Prendi gli ID dei prodotti dai parametri

                // Verifica se il numero di telefono è presente
                if (empty($userPhone)) {
                    return response()->json([
                        'message' => 'Per favore fornisci un numero di telefono fisso o cellulare per completare la prenotazione.',
                        'thread_id' => $threadId,
                    ]);
                }

                // Verifica se ci sono product_ids
                if (empty($productIds)) {
                    return response()->json([
                        'message' => 'Per favore fornisci informazioni aggiuntive sul prodotto che vorresti acquistare.',
                        'thread_id' => $threadId,
                    ]);
                }

                // Crea l'ordine
                $orderData = $this->createOrder($userPhone, $deliveryDate, $productIds, $teamSlug);

                // Invia i risultati a GPT
                $this->client->threads()->runs()->submitToolOutputs(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => [
                            [
                                'tool_call_id' => $requiredAction->submitToolOutputs->toolCalls[0]->id,
                                'output' => json_encode($orderData),
                            ],
                        ],
                    ]
                );

                // Recupera la risposta finale
                $run = $this->retrieveRunResult($threadId, $run->id);
            }
        }

        // Recupera i messaggi aggiornati
        $messages = $this->client->threads()->messages()->list($threadId)->data;
        $content = $messages[0]->content[0]->text->value;

        // Formatta il contenuto della risposta
        $formattedContent = $this->formatResponseContent($content);

        return response()->json([
            'message' => $formattedContent,
            'thread_id' => $threadId,
            'product_ids' => $productIds ?? [], // Aggiungi gli ID dei prodotti alla risposta
        ]);
    }

    private function retrieveRunResult($threadId, $runId)
    {
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);

            if ($run->status === 'completed' || $run->status === 'requires_action') {
                return $run;
            }

            sleep(1);
        }
    }

    private function fetchProductData(array $productNames, $teamSlug)
    {
        Log::info('fetchProductData: Inizio recupero dati prodotti', ['productNames' => $productNames, 'teamSlug' => $teamSlug]);
        $client = new Client();
        $products = [];

        if (empty($productNames)) {
            Log::info('fetchProductData: Richiesta indice di tutti i prodotti');
            $response = $client->get("https://cavalliniservice.com/api/products/{$teamSlug}");
            $products = json_decode($response->getBody(), true);
        } else {
            foreach ($productNames as $name) {
                Log::info('fetchProductData: Richiesta dati per prodotto', ['name' => $name]);
                $response = $client->get("https://cavalliniservice.com/api/products/{$teamSlug}", [
                    'query' => ['name' => $name],
                ]);

                $productData = json_decode($response->getBody(), true);
                Log::info('fetchProductData: Dati prodotto ricevuti', ['productData' => $productData]);
                $products = array_merge($products, $productData);
            }
        }

        Log::info('fetchProductData: Dati prodotti finali', ['products' => $products]);

        return $products;
    }

    private function fetchAddressData($teamSlug)
    {
        Log::info('fetchAddressData: Inizio recupero dati indirizzo', ['teamSlug' => $teamSlug]);
        $client = new Client();
        $response = $client->get("https://cavalliniservice.com/api/teams/{$teamSlug}");
        $addressData = json_decode($response->getBody(), true);
        Log::info('fetchAddressData: Dati indirizzo ricevuti', ['addressData' => $addressData]);

        return $addressData;
    }

    private function fetchAvailableTimes($teamSlug)
    {
        Log::info('fetchAvailableTimes: Inizio recupero orari disponibili', ['teamSlug' => $teamSlug]);
        $client = new Client();
        $response = $client->get("https://cavalliniservice.com/api/events/{$teamSlug}");
        $availableTimes = json_decode($response->getBody(), true);
        Log::info('fetchAvailableTimes: Orari disponibili ricevuti', ['availableTimes' => $availableTimes]);

        return $availableTimes;
    }

    private function createOrder($userPhone, $deliveryDate, $productIds, $teamSlug)
    {
        Log::info('createOrder: Inizio creazione ordine', ['userPhone' => $userPhone, 'deliveryDate' => $deliveryDate, 'productIds' => $productIds, 'teamSlug' => $teamSlug]);
        $client = new Client();
        $response = $client->post("https://cavalliniservice.com/api/order/{$teamSlug}", [
            'json' => [
                'user_phone' => $userPhone,
                'delivery_date' => $deliveryDate,
                'product_ids' => $productIds,
            ],
        ]);

        Log::info('createOrder: Richiesta inviata', [
            'url' => "https://cavalliniservice.com/api/order/{$teamSlug}",
            'payload' => [
                'user_phone' => $userPhone,
                'delivery_date' => $deliveryDate,
                'product_ids' => $productIds,
            ],
        ]);
        $orderData = json_decode($response->getBody(), true);
        Log::info('createOrder: Ordine creato', ['orderData' => $orderData]);

        return $orderData;
    }

    private function formatResponseContent($content)
    {
        // Formatta il contenuto della risposta
        $formattedContent = nl2br($content); // Aggiungi interruzioni di riga
        $formattedContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedContent); // Aggiungi grassetto
        $formattedContent = preg_replace('/\d+\.\s/', '<br><br><strong>$0</strong>', $formattedContent); // Aggiungi elenchi numerati

        return $formattedContent;
    }
}
