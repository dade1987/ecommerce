<?php

namespace App\Http\Controllers;

use App\Models\Quoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;
use function Safe\fopen;

class QuoterController extends Controller
{
    public Client $client;

    public function __construct()
    {
        $apiKey = config('openapi.key');
        $this->client = OpenAI::client($apiKey);
    }

    public function createThread()
    {
        $minutes = 60; // Durata del cookie in minuti
        $thread = $this->client->threads()->create([]);

        Log::info('thread id '.$thread->id);

        return response('Thread_id cookie impostato')->cookie(
            'thread_id',
            $thread->id,
            $minutes
        );
    }

    public function uploadFile(Request $request)
    {
        $threadId = $request->cookie('thread_id');
        $userMessage = $request->input('message');
        $locale = $request->input('locale', 'it');
        App::setLocale($locale);

        Log::info('thread id '.$threadId);

        // Verifica se è stato caricato un file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public'); // Salva il file e ottieni la path

            // Carica il file utilizzando la path
            $response = $this->client->files()->upload([
                'purpose' => 'assistants',
                'file' => fopen(storage_path('app/public/'.$path), 'r'), // Usa la path del file caricato
            ]);

            Quoter::create(['thread_id' => $threadId, 'role' => 'user', 'content' => 'Caricamento bolletta']);

            $this->client->threads()->messages()->create($threadId, [
                'role' => 'user',
                'content' => __('quoter.upload_prompt'),
                'attachments' => [['file_id' => $response->id, 'tools' => [['type' => 'file_search']]]],
            ]);

            $run = $this->client->threads()->runs()->create(
                threadId: $threadId,
                parameters: [
                    'assistant_id' => config('openapi.assistant_id'),
                ]
            );

            $this->retrieveRunResult($threadId, $run->id);

            $messages = $this->client->threads()->messages()->list($threadId)->data;

            $content = $messages[0]->content[0]->text->value;

            Quoter::create(['thread_id' => $threadId, 'role' => 'chatbot', 'content' => $content]);

            return response()->json([
                'response' => $content,
            ]);
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    public function sendMessage(Request $request)
    {
        $threadId = $request->cookie('thread_id');
        $userMessage = $request->input('message');
        $locale = $request->input('locale', 'it');
        App::setLocale($locale);

        Log::info('thread id '.$threadId);

        $content = $this->generateContentBasedOnMessage($userMessage);

        Quoter::create(['thread_id' => $threadId, 'role' => 'user', 'content' => $userMessage]);

        $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $content,
        ]);

        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => config('openapi.assistant_id'),
                'instructions' => __('quoter.assistant_instructions'),
                'tools' => [
                    [
                        'type' => 'file_search',
                    ],
                ],
                'model' => 'gpt-4o',
            ]
        );

        $this->retrieveRunResult($threadId, $run->id);

        $messages = $this->client->threads()->messages()->list($threadId)->data;

        $content = $messages[0]->content[0]->text->value;

        Quoter::create(['thread_id' => $threadId, 'role' => 'chatbot', 'content' => $content]);

        return response()->json([
            'response' => $content,
        ]);
    }

    private function generateContentBasedOnMessage($userMessage)
    {
        if ($userMessage === 'Intro') {
            return __('quoter.intro_prompt');
        } elseif ($userMessage === 'Genera Preventivo') {
            return __('quoter.generate_quote_prompt');
        } else {
            return $userMessage;
        }
    }

    private function retrieveRunResult($threadId, $runId)
    {
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);

            Log::info(var_export($run, true));

            if ($run->status === 'completed') {
                return $run;
            }

            sleep(1); // Attende un secondo prima di fare un'altra richiesta
        }
    }
}
