<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenAI;
use OpenAI\Client;
use function Safe\fopen;
use function Safe\json_decode;

class CalzaturieroController extends Controller
{
    public Client $client;

    public function __construct()
    {
        $apiKey = config('openai.key');
        $this->client = OpenAI::client($apiKey);
    }

    public function extractProductInfo(Request $request)
    {
        // Verifica se è stato caricato un file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public'); // Salva il file e ottieni la path

            // Carica il file utilizzando la path
            $response = $this->client->files()->upload([
                'purpose' => 'assistants',
                'file' => fopen(storage_path('app/public/'.$path), 'r'), // Usa la path del file caricato
            ]);

            // Utilizza GPT per estrarre le informazioni dal file PDF
            $result = $this->client->completions()->create([
                'model' => 'gpt-4o',
                'prompt' => 'Estrai l\'etichetta del prodotto e la quantità dal file PDF caricato e restituisci in formato JSON con il seguente formato: {"prodotto": "nome_prodotto", "quantita": quantita}.',
                'max_tokens' => 150,
                'temperature' => 0.7,
                'attachments' => [['file_id' => $response->id, 'tools' => [['type' => 'file_search']]]],
            ]);

            $content = $result->choices[0]->text;

            // Decodifica il testo in JSON
            $jsonContent = json_decode($content, true);

            return response()->json($jsonContent);
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }
}
