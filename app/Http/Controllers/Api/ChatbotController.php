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
        $userInput = $request->input('message');
        Log::info('handleChat: Messaggio utente ricevuto', ['message' => $userInput]);

        // Step 1: Ottieni la risposta di GPT
        $gptResponse = $this->getGptResponse($userInput);
        Log::info('handleChat: Risposta GPT ottenuta', ['gptResponse' => $gptResponse]);

        // Step 2: Gestione del function calling
        if (isset($gptResponse['function_call'])) {
            Log::info('handleChat: Function call rilevata', ['function_call' => $gptResponse['function_call']]);
            $functionCall = $gptResponse['function_call'];
            $functionName = $functionCall['name'];
            $arguments = json_decode($functionCall['arguments'], true);
            Log::info('handleChat: Function name e arguments', ['functionName' => $functionName, 'arguments' => $arguments]);

            if ($functionName === 'getProductInfo' && isset($arguments['product_names'])) {
                $productNames = $arguments['product_names'];
                Log::info('handleChat: Product names rilevati', ['productNames' => $productNames]);

                // Step 3: Chiama l'API dei prodotti
                $productData = $this->fetchProductData($productNames);
                Log::info('handleChat: Dati prodotti ottenuti', ['productData' => $productData]);

                // Step 4: Riformula la risposta utilizzando GPT
                $finalMessage = $this->getGptResponseWithProducts($userInput, $productData);
                Log::info('handleChat: Messaggio finale ottenuto', ['finalMessage' => $finalMessage]);

                // Step 5: Invia la risposta all'utente
                return response()->json(['message' => $finalMessage]);
            }
        }

        // Risposta standard se non ci sono function call
        Log::info('handleChat: Nessuna function call, risposta standard inviata');

        return response()->json(['message' => $gptResponse['message']]);
    }

    private function getGptResponse($message)
    {
        Log::info('getGptResponse: Inizio richiesta a GPT', ['message' => $message]);
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4-0613',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a chatbot that answers questions about menu products.'],
                    ['role' => 'user', 'content' => $message],
                ],
                'functions' => [
                    [
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
                'function_call' => 'auto',
            ],
        ]);

        $gptResponse = json_decode($response->getBody(), true)['choices'][0]['message'];
        Log::info('getGptResponse: Risposta GPT ricevuta', ['gptResponse' => $gptResponse]);

        return $gptResponse;
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

    private function getGptResponseWithProducts($originalMessage, $productData)
    {
        $client = new Client();

        // Formatta i dati dei prodotti come testo leggibile
        $formattedProductInfo = collect($productData)->map(function ($product) {
            return "- **{$product['name']}**: "
                .($product['description'] ? $product['description'] : 'No description available')
                .". Price: €{$product['price']}.";
        })->implode("\n");

        // Prompt aggiornato per rendere il dato più vincolante
        $promptMessage = "Based on the menu information provided below, respond to the user's query strictly using the product details without making assumptions or guesses.\n\nMenu Information:\n$formattedProductInfo";

        // Chiamata all'API GPT
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4-0613',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a chatbot that answers menu-related questions with precise information.'],
                    ['role' => 'user', 'content' => $originalMessage],
                    ['role' => 'assistant', 'content' => $promptMessage],
                ],
            ],
        ]);

        return json_decode($response->getBody(), true)['choices'][0]['message']['content'];
    }
}
