<?php

namespace App\Actions;

use OpenAI;
use Spatie\QueueableAction\QueueableAction;

class RequestTimeSlotsAction
{
    use QueueableAction;

    public string $api_key;

    public string $thread_id;

    public string $path;

    public function getClient()
    {
        return OpenAI::client($this->api_key);
    }

    public function createThread()
    {
        $thread = $this->getClient()->threads()->create([]);

        $this->thread_id = $thread->id;
    }

    public function uploadFile()
    {
        $threadId = $this->thread_id;

        // Verifica se è stato caricato un file

        dd($this->path);

        // Carica il file utilizzando la path
        $response = $this->getClient()->files()->upload([
            'purpose' => 'assistants',
            'file' => fopen(storage_path('app/public/'.$this->path), 'r'), // Usa la path del file caricato
        ]);

        $this->getClient()->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => 'Estrai i consumi della fascia F1, della fascia F2 e della fascia F3 dal file caricato. Estrai nome e cognome e indirizzo del cantiere e se l\'utente è privato o azienda. Poi continua con le domande',
            'attachments' => [['file_id' => $response->id, 'tools' => [['type' => 'file_search']]]],
        ]);

        $run = $this->getClient()->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => config('openapi.assistant_id'),
            ]
        );

        $this->retrieveRunResult($threadId, $run->id);

        $messages = $this->getClient()->threads()->messages()->list($threadId)->data;

        $content = $messages[0]->content[0]->text->value;

        dd($content);
    }

    private function retrieveRunResult($threadId, $runId)
    {
        while (true) {
            $run = $this->getClient()->threads()->runs()->retrieve($threadId, $runId);

            if ($run->status === 'completed') {
                return $run;
            }

            sleep(1); // Attende un secondo prima di fare un'altra richiesta
        }
    }

    /**
     * Execute the action.
     *
     * @return mixed
     */
    public function execute(string $path)
    {
        $this->api_key = config('openapi.key');
        $this->createThread();

        dd($path);
    }
}
