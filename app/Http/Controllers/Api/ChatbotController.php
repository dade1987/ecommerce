<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use function Safe\json_decode;
use function Safe\json_encode;

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

        // Crea e gestisci il run
        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                'instructions' => 'You are a chatbot that answers questions about menu products.',
                'tools' => [
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'getProductInfo',
                            'description' => 'Retrieve information about menu products by their names.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'product_names' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                        'description' => 'Names of the products to retrieve.',
                                    ],
                                ],
                                'required' => ['product_names'],
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
                $productNames = $arguments['product_names'];

                // Recupera i dati dei prodotti
                $productData = $this->fetchProductData($productNames);

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
            }
        }

        // Recupera i messaggi aggiornati
        $messages = $this->client->threads()->messages()->list($threadId)->data;
        $content = $messages[0]->content[0]->text->value;

        return response()->json([
            'message' => $content,
            'thread_id' => $threadId,
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

    private function fetchProductData(array $productNames)
    {
        Log::info('fetchProductData: Inizio recupero dati prodotti', ['productNames' => $productNames]);
        $client = new Client();
        $products = [];

        foreach ($productNames as $name) {
            Log::info('fetchProductData: Richiesta dati per prodotto', ['name' => $name]);
            $response = $client->get('https://cavalliniservice.com/api/products', [
                'query' => ['name' => $name],
            ]);

            $productData = json_decode($response->getBody(), true);
            Log::info('fetchProductData: Dati prodotto ricevuti', ['productData' => $productData]);
            $products = array_merge($products, $productData);
        }

        Log::info('fetchProductData: Dati prodotti finali', ['products' => $products]);

        return $products;
    }
}
