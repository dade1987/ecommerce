<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Safe\json_decode;

class ChatbotController extends Controller
{
    public function handleChat(Request $request)
    {
        Log::info('handleChat: Inizio elaborazione richiesta');

        // Recupera il messaggio inviato dall'utente e, se presente, il thread_id della sessione
        $userInput = $request->input('message');
        $threadId = $request->input('thread_id'); // il thread_id fornito dal client (se esiste)
        Log::info('handleChat: Messaggio utente ricevuto', [
            'message'   => $userInput,
            'thread_id' => $threadId,
        ]);

        // Se non esiste un thread_id, creane uno tramite l’endpoint /v1/threads
        if (! $threadId) {
            $threadId = $this->createThread();
            Log::info('handleChat: Nuovo thread creato', ['thread_id' => $threadId]);
        }

        // Step 1: Chiediamo a ChatGPT la risposta iniziale (senza passare "thread" nel payload)
        $gptResponse = $this->getGptResponse($userInput);
        Log::info('handleChat: Risposta GPT iniziale ottenuta', ['gptResponse' => $gptResponse]);

        // La risposta di ChatGPT DEVE essere in formato JSON (nella proprietà "content") con le chiavi "message" e "thread_id"
        $decodedResponse = json_decode($gptResponse['content'], true);
        $finalMessage = $decodedResponse['message'] ?? '';

        // Se ChatGPT restituisce un thread_id, lo usiamo (altrimenti, manteniamo quello corrente)
        $threadId = $decodedResponse['thread_id'] ?? $threadId;

        // Step 2: Se la risposta di ChatGPT include una "function_call", la gestiamo
        if (isset($gptResponse['function_call'])) {
            Log::info('handleChat: Function call rilevata', ['function_call' => $gptResponse['function_call']]);
            $functionCall = $gptResponse['function_call'];
            $functionName = $functionCall['name'];
            $arguments = json_decode($functionCall['arguments'], true);
            Log::info('handleChat: Nome funzione e argomenti', [
                'functionName' => $functionName,
                'arguments'    => $arguments,
            ]);

            if ($functionName === 'getProductInfo' && isset($arguments['product_names'])) {
                $productNames = $arguments['product_names'];
                Log::info('handleChat: Nomi dei prodotti rilevati', ['productNames' => $productNames]);

                // Step 3: Recupera i dati dei prodotti tramite l’API esterna
                $productData = $this->fetchProductData($productNames);
                Log::info('handleChat: Dati dei prodotti ottenuti', ['productData' => $productData]);

                // Step 4: Riformula la risposta includendo le informazioni sui prodotti
                $gptResponseConProdotti = $this->getGptResponseWithProducts($userInput, $productData);
                Log::info('handleChat: Risposta finale con prodotti ottenuta', ['gptResponseConProdotti' => $gptResponseConProdotti]);

                $decodedResponse = json_decode($gptResponseConProdotti['content'], true);
                $finalMessage = $decodedResponse['message'] ?? '';
                // Il thread_id dovrebbe rimanere invariato
                $threadId = $decodedResponse['thread_id'] ?? $threadId;
            }
        }

        Log::info('handleChat: Invio risposta finale', [
            'finalMessage' => $finalMessage,
            'thread_id'    => $threadId,
        ]);

        // Restituisce la risposta al client, includendo il thread_id per mantenere il contesto della chat
        return response()->json([
            'message'   => $finalMessage,
            'thread_id' => $threadId,
        ]);
    }

    /**
     * Crea un nuovo thread chiamando l’endpoint /v1/threads e restituisce il thread_id.
     */
    private function createThread()
    {
        $client = new Client();

        $response = $client->post('https://api.openai.com/v1/threads', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
                'OpenAI-Beta'   => 'assistants=v2',
            ],
            'json' => [],
        ]);

        $data = json_decode($response->getBody(), true);
        Log::info('createThread: Thread creato', ['data' => $data]);

        return $data['id'];
    }

    /**
     * Invia una richiesta a ChatGPT per ottenere la risposta iniziale.
     * NOTA: Non passiamo il parametro "thread" nel payload, in quanto non è riconosciuto dall’endpoint.
     */
    private function getGptResponse($message)
    {
        Log::info('getGptResponse: Inizio richiesta a ChatGPT', ['message' => $message]);
        $client = new Client();

        $systemMessage = 'Sei un chatbot che risponde a domande sui prodotti del menù. '.
                         'Quando rispondi, utilizza il seguente formato JSON: '.
                         '{"message": "il testo della risposta", "thread_id": "identificativo della sessione"}. '.
                         'Se viene fornito un thread_id, usalo senza modificarlo; altrimenti, generane uno costante per la sessione.';

        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $message],
        ];

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
                'OpenAI-Beta'   => 'assistants=v2',
            ],
            'json' => [
                'model'         => 'gpt-4-0613',
                'messages'      => $messages,
                'functions'     => [
                    [
                        'name'        => 'getProductInfo',
                        'description' => 'Recupera informazioni sui prodotti del menù a partire dai loro nomi.',
                        'parameters'  => [
                            'type'       => 'object',
                            'properties' => [
                                'product_names' => [
                                    'type'        => 'array',
                                    'items'       => ['type' => 'string'],
                                    'description' => 'Nomi dei prodotti da recuperare.',
                                ],
                            ],
                            'required'   => ['product_names'],
                        ],
                    ],
                ],
                'function_call' => 'auto',
            ],
        ]);

        $gptResponse = json_decode($response->getBody(), true)['choices'][0]['message'];
        Log::info('getGptResponse: Risposta ricevuta da ChatGPT', ['gptResponse' => $gptResponse]);

        return $gptResponse;
    }

    /**
     * Recupera i dati dei prodotti tramite chiamata a un’API esterna.
     */
    private function fetchProductData(array $productNames)
    {
        Log::info('fetchProductData: Inizio recupero dati per i prodotti', ['productNames' => $productNames]);
        $client = new Client();
        $products = [];

        foreach ($productNames as $name) {
            Log::info('fetchProductData: Richiesta dati per il prodotto', ['name' => $name]);
            $response = $client->get('https://cavalliniservice.com/api/products', [
                'query' => ['name' => $name],
            ]);
            $productData = json_decode($response->getBody(), true);
            Log::info('fetchProductData: Dati ricevuti per il prodotto', ['productData' => $productData]);
            $products = array_merge($products, $productData);
        }

        Log::info('fetchProductData: Dati finali dei prodotti', ['products' => $products]);

        return $products;
    }

    /**
     * Invia una richiesta a ChatGPT includendo i dettagli dei prodotti ottenuti,
     * in modo da riformulare la risposta dell'utente.
     * NOTA: Anche qui NON passiamo il parametro "thread" nel payload.
     */
    private function getGptResponseWithProducts($originalMessage, $productData)
    {
        $client = new Client();

        // Formattiamo i dati dei prodotti in un testo leggibile
        $formattedProductInfo = collect($productData)->map(function ($product) {
            return "- **{$product['name']}**: ".
                   ($product['description'] ? $product['description'] : 'Nessuna descrizione disponibile').
                   ". Prezzo: €{$product['price']}.";
        })->implode("\n");

        $promptMessage = "In base alle seguenti informazioni del menù, rispondi alla domanda dell'utente utilizzando esclusivamente i dettagli forniti. ".
                         "Inoltre, offri una descrizione raffinata del piatto come se fossi uno chef.\n\n".
                         "Informazioni del menù:\n$formattedProductInfo\n\n".
                         'Rispondi in formato JSON: {"message": "il testo della risposta", "thread_id": "identificativo della sessione"}.';

        $systemMessage = 'Sei un chatbot che risponde a domande sui prodotti del menù e fornisce descrizioni raffinate. '.
                         'Quando rispondi, utilizza il seguente formato JSON: {"message": "il testo della risposta", "thread_id": "identificativo della sessione"}. '.
                         'Se viene fornito un thread_id, usalo senza modificarlo; altrimenti, generane uno costante per la sessione.';

        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $originalMessage],
            ['role' => 'assistant', 'content' => $promptMessage],
        ];

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
                'OpenAI-Beta'   => 'assistants=v2',
            ],
            'json' => [
                'model'    => 'gpt-4-0613',
                'messages' => $messages,
            ],
        ]);

        $gptResponse = json_decode($response->getBody(), true)['choices'][0]['message'];
        Log::info('getGptResponseWithProducts: Risposta ricevuta da ChatGPT', ['gptResponse' => $gptResponse]);

        return $gptResponse;
    }
}
