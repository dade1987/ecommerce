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
            // Se esiste già un thread (tramite cookie), lo riutilizziamo, altrimenti ne creiamo uno nuovo
            if ($request->hasCookie('thread_id')) {
                $threadId = $request->cookie('thread_id');
            } else {
                $thread = $this->client->threads()->create([]);
                $threadId = $thread->id;
            }

            // Salviamo il file e lo carichiamo tramite l'API OpenAI
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $fullPath = storage_path('app/public/'.$path);
            $fileResource = fopen($fullPath, 'r');

            $uploadResponse = $this->client->files()->upload([
                'purpose' => 'assistants',
                'file'    => $fileResource,
            ]);

            // Creiamo il messaggio con il prompt:
            // Istruzione chiara: rispondi soltanto con un array JSON conforme al formato indicato.
            $messageContent = "Estrai l'etichetta del prodotto e la quantità dal file PDF caricato. "
                .'Rispondi esclusivamente con un array JSON, senza alcun testo aggiuntivo. '
                .'Il JSON deve rispettare esattamente il seguente formato: [{"prodotto": "nome_prodotto", "quantita": quantita}]. '
                .'L\'array può contenere uno o più oggetti a seconda del contenuto del PDF.';

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

            // Attende il completamento del run
            $this->retrieveRunResult($threadId, $run->id);

            // Recupera i messaggi dal thread
            $messages = $this->client->threads()->messages()->list($threadId)->data;

            // Assumiamo che la risposta dell'assistente contenga esclusivamente il JSON desiderato.
            // Se l'assistente ha inviato più messaggi, prendiamo il primo messaggio con role 'assistant'.
            $jsonResponse = null;
            foreach ($messages as $message) {
                if ($message->role === 'assistant') {
                    $jsonResponse = $message->content[0]->text->value;
                    break;
                }
            }

            // Se non troviamo un messaggio assistant, usiamo l'ultimo messaggio ricevuto
            if (! $jsonResponse && count($messages) > 0) {
                $lastMessage = end($messages);
                $jsonResponse = $lastMessage->content[0]->text->value;
            }

            // Restituisce la risposta con header 'application/json'
            return response($jsonResponse, 200)
                ->header('Content-Type', 'application/json')
                ->cookie('thread_id', $threadId, 60);
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    private function retrieveRunResult($threadId, $runId)
    {
        // Esegue il polling finché il run non risulta completato
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);

            if ($run->status === 'completed') {
                return $run;
            }

            sleep(1);
        }
    }
}
