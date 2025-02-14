<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenAI;
use OpenAI\Client;
use function Safe\fopen;

class CalzaturieroController extends Controller
{
    public Client $client;

    public function __construct()
    {
        $apiKey = config('openapi.key');
        $this->client = OpenAI::client($apiKey);
    }

    public function extractProductInfo(Request $request)
    {
        if ($request->hasFile('file')) {
            // Se presente un cookie 'thread_id' lo riutilizzo, altrimenti ne creo uno nuovo
            if ($request->hasCookie('thread_id')) {
                $threadId = $request->cookie('thread_id');
            } else {
                $thread = $this->client->threads()->create([]);
                $threadId = $thread->id;
            }

            // Salva il file e caricalo tramite l'API di OpenAI
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $fullPath = storage_path('app/public/'.$path);
            $fileResource = fopen($fullPath, 'r');

            $uploadResponse = $this->client->files()->upload([
                'purpose' => 'assistants',
                'file'    => $fileResource,
            ]);

            // Costruisci il messaggio con il prompt e allega il file caricato
            $messageContent = "Estrai l'etichetta del prodotto e la quantità dal file PDF caricato. "
                .'Restituisci il risultato in formato JSON con il seguente formato: '
                .'{"prodotto": "nome_prodotto", "quantita": quantita}.';

            $this->client->threads()->messages()->create($threadId, [
                'role'        => 'user',
                'content'     => $messageContent,
                'attachments' => [
                    [
                        'file_id' => $uploadResponse->id,
                        'tools'   => [
                            ['type' => 'file_search'],
                        ],
                    ],
                ],
            ]);

            // Avvia l'esecuzione (run) della richiesta sul thread
            $run = $this->client->threads()->runs()->create(
                threadId: $threadId,
                parameters: [
                    'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0', // Sostituisci con l'ID corretto se necessario
                    'model'        => 'gpt-4o',
                ]
            );

            // Attendi che il run sia completato
            $this->retrieveRunResult($threadId, $run->id);

            // Recupera i messaggi dal thread; qui assumiamo che la risposta sia nel primo messaggio
            $messages = $this->client->threads()->messages()->list($threadId)->data;
            $content = $messages[0]->content[0]->text->value;

            return response()->json([
                'thread_id' => $threadId,
                'response'  => $content,
            ])->cookie('thread_id', $threadId, 60);
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    private function retrieveRunResult($threadId, $runId)
    {
        // Polling finché il run non risulta completato
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);

            if ($run->status === 'completed') {
                return $run;
            }

            sleep(1);
        }
    }
}
