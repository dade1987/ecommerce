<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use App\Models\Team;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
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

        // Salva il messaggio dell'utente nel modello Quoter
        Quoter::create([
            'thread_id' => $threadId,
            'role' => 'user',
            'content' => $userInput,
        ]);

        // Aggiungi il messaggio dell'utente al thread
        $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $userInput,
        ]);

        // Se il messaggio è "buongiorno", recupera il welcome message dal modello Team
        if (strtolower($userInput) === 'buongiorno') {
            $team = Team::where('slug', $teamSlug)->first();
            $welcomeMessage = $team ? $team->welcome_message : 'Benvenuto!';

            // Salva il messaggio del chatbot nel modello Quoter
            Quoter::create([
                'thread_id' => $threadId,
                'role' => 'chatbot',
                'content' => $welcomeMessage,
            ]);

            return response()->json([
                'message' => $welcomeMessage,
                'thread_id' => $threadId,
            ]);
        }

        // Crea e gestisci il run con i vari tool disponibili
        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                'instructions' => 'Sei un chatbot che risponde esclusivamente a domande relative a prodotti, servizi, trattamenti, sessioni o attività offerti dal centro olistico. Se la domanda dell\'utente non rientra in questi ambiti, invoca la funzione "fallback" per rispondere: "Per un setup più specifico per la tua attività contatta 3487433620 Giuliano". Le domande consentite sono quindi solo quelle inerenti a: prodotti, servizi, trattamenti, sessioni o attività del centro olistico. Inoltre, se non conosci già il nome e l\'email, chiedili esplicitamente, spiegando che verranno utilizzati solo per la demo.',
                'model' => 'gpt-4o',
                'tools' => [
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'getProductInfo',
                            'description' => 'Recupera informazioni sui prodotti, servizi, trattamenti, sessioni o attività del menu tramite i loro nomi o recupera tutti chiamando l\'API senza specificare il nome.',
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
                    // Nuovo function call per gestire i dati anagrafici
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'submitUserData',
                            'description' => 'Registra i dati anagrafici dell\'utente (nome, email e numero di telefono) e risponde ringraziando per averli forniti. I dati verranno trattati in conformità al GDPR e all\'informativa sulla privacy del sito.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'user_phone' => [
                                        'type' => 'string',
                                        'description' => 'Numero di telefono dell\'utente',
                                    ],
                                    'user_email' => [
                                        'type' => 'string',
                                        'description' => 'Email dell\'utente',
                                    ],
                                    'user_name' => [
                                        'type' => 'string',
                                        'description' => 'Nome dell\'utente',
                                    ],
                                ],
                                'required' => ['user_phone', 'user_email', 'user_name'],
                            ],
                        ],
                    ],
                    // Funzione fallback per domande fuori contesto
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'fallback',
                            'description' => 'Risponde a domande non inerenti al contesto consentito con il messaggio predefinito: "Per un setup più specifico per la tua attività contatta 3487433620 Giuliano". Le domande consentite riguardano esclusivamente prodotti, servizi, trattamenti, sessioni o attività offerti dal centro olistico.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => new \stdClass(), // Nessun parametro richiesto
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
                $productIds = $arguments['product_ids'];

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
            } elseif ($functionCall->name === 'submitUserData') {
                $arguments = json_decode($functionCall->arguments, true);
                $userPhone = $arguments['user_phone'] ?? null;
                $userEmail = $arguments['user_email'] ?? null;
                $userName = $arguments['user_name'] ?? null;

                // Verifica che tutti i dati siano stati forniti
                if (empty($userPhone) || empty($userEmail) || empty($userName)) {
                    return response()->json([
                        'message' => 'Per favore fornisci nome, email e numero di telefono.',
                        'thread_id' => $threadId,
                    ]);
                }

                // Richiama l'API per registrare i dati anagrafici
                $userDataResponse = $this->submitUserData($userPhone, $userEmail, $userName, $teamSlug);

                // Invia i risultati a GPT
                $this->client->threads()->runs()->submitToolOutputs(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => [
                            [
                                'tool_call_id' => $requiredAction->submitToolOutputs->toolCalls[0]->id,
                                'output' => json_encode($userDataResponse),
                            ],
                        ],
                    ]
                );

                // Recupera la risposta finale
                $run = $this->retrieveRunResult($threadId, $run->id);
            } elseif ($functionCall->name === 'fallback') {
                // Gestione della funzione fallback: rispondi con il messaggio predefinito
                $fallbackMessage = 'Per un setup più specifico per la tua attività contatta 3487433620 Giuliano';
                $this->client->threads()->runs()->submitToolOutputs(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => [
                            [
                                'tool_call_id' => $requiredAction->submitToolOutputs->toolCalls[0]->id,
                                'output' => json_encode(['message' => $fallbackMessage]),
                            ],
                        ],
                    ]
                );
                // Recupera la risposta finale dopo aver inviato il fallback
                $run = $this->retrieveRunResult($threadId, $run->id);
            }
        }

        // Recupera i messaggi aggiornati
        $messages = $this->client->threads()->messages()->list($threadId)->data;
        $content = $messages[0]->content[0]->text->value;

        // Salva il messaggio del chatbot nel modello Quoter
        Quoter::create([
            'thread_id' => $threadId,
            'role' => 'chatbot',
            'content' => $content,
        ]);

        // Formatta il contenuto della risposta
        $formattedContent = $this->formatResponseContent($content);

        return response()->json([
            'message' => $formattedContent,
            'thread_id' => $threadId,
            'product_ids' => $productIds ?? [], // Aggiungi gli ID dei prodotti alla risposta, se presenti
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
        Log::info('createOrder: Inizio creazione ordine', [
            'userPhone' => $userPhone,
            'deliveryDate' => $deliveryDate,
            'productIds' => $productIds,
            'teamSlug' => $teamSlug,
        ]);
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

    private function submitUserData($userPhone, $userEmail, $userName, $teamSlug)
    {
        Log::info('submitUserData: Inizio invio dati utente', [
            'userPhone' => $userPhone,
            'userEmail' => $userEmail,
            'userName'  => $userName,
            'teamSlug'  => $teamSlug,
        ]);

        $client = new Client();

        // Costruisci l'URL completo dell'endpoint locale
        // Assumendo che config('app.url') contenga l'URL base della tua applicazione
        $url = 'https://cavalliniservice.com/api/customers';

        // Effettua la richiesta POST all'endpoint /customers
        $response = $client->post($url, [
            'json' => [
                'name'  => $userName,
                'phone' => $userPhone,
                'email' => $userEmail,
            ],
        ]);

        Log::info('submitUserData: Dati utente inviati', [
            'url'     => $url,
            'payload' => [
                'name'  => $userName,
                'phone' => $userPhone,
                'email' => $userEmail,
            ],
        ]);

        $responseData = json_decode($response->getBody(), true);

        return $responseData;
    }

    private function formatResponseContent($content)
    {
        // Formatta il contenuto della risposta
        $formattedContent = nl2br($content); // Aggiungi interruzioni di riga
        $formattedContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedContent); // Aggiungi grassetto
        $formattedContent = preg_replace('/\d+\.\s/', '<strong>$0</strong>', $formattedContent); // Aggiungi elenchi numerati

        return $formattedContent;
    }
}
