<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use function Safe\json_decode;

class ChatbotController extends Controller
{
    public function handleChat(Request $request)
    {
        $userInput = $request->input('message');

        // Step 1: Ottieni la risposta di GPT
        $gptResponse = $this->getGptResponse($userInput);

        // Step 2: Gestione del function calling
        if (isset($gptResponse['function_call'])) {
            $functionCall = $gptResponse['function_call'];
            $functionName = $functionCall['name'];
            $arguments = json_decode($functionCall['arguments'], true);

            if ($functionName === 'getProductInfo' && isset($arguments['product_names'])) {
                $productNames = $arguments['product_names'];

                // Step 3: Chiama l'API dei prodotti
                $productData = $this->fetchProductData($productNames);

                // Step 4: Riformula la risposta utilizzando GPT
                $finalMessage = $this->getGptResponseWithProducts($userInput, $productData);

                // Step 5: Invia la risposta all'utente
                return response()->json(['message' => $finalMessage]);
            }
        }

        // Risposta standard se non ci sono function call
        return response()->json(['message' => $gptResponse['message']]);
    }

    private function getGptResponse($message)
    {
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

        return json_decode($response->getBody(), true)['choices'][0]['message'];
    }

    private function fetchProductData(array $productNames)
    {
        $client = new Client();
        $products = [];

        foreach ($productNames as $name) {
            $response = $client->get('https://cavalliniservice.com/api/products', [
                'query' => ['name' => $name],
            ]);

            $productData = json_decode($response->getBody(), true);
            $products = array_merge($products, $productData);
        }

        return $products;
    }

    private function getGptResponseWithProducts($originalMessage, $productData)
    {
        $client = new Client();

        // Formatta i dati dei prodotti per GPT
        $formattedProductInfo = collect($productData)->map(function ($product) {
            return "- **{$product['name']}**: ".($product['description'] ?? 'No description available').". Price: €{$product['price']}.";
        })->implode("\n");

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4-0613',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a chatbot that provides elegant answers about menu products.'],
                    ['role' => 'user', 'content' => $originalMessage],
                    ['role' => 'assistant', 'content' => "Here is the product information I found:\n$formattedProductInfo"],
                ],
            ],
        ]);

        return json_decode($response->getBody(), true)['choices'][0]['message']['content'];
    }
}
