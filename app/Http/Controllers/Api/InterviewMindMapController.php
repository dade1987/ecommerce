<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use App\Neuron\InterviewMindMapAgent;
use App\Neuron\QuoterChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
use function Safe\json_decode;

class InterviewMindMapController extends Controller
{
    /**
     * Genera una mappa mentale multilingua basata sulla
     * storia delle conversazioni (thread_id) del colloquio.
     *
     * NOTA: il CV viene completamente ignorato; la mappa
     * è un riassunto della conversazione avuta.
     *
     * Endpoint:
     * POST /api/chatbot/interview-mindmap
     * body JSON: {
     *   locale?: string,
     *   lang_a?: string,
     *   lang_b?: string,
     *   thread_id?: string // thread della TRASCRIZIONE (translationThreadId)
     * }
     */
    public function generate(Request $request)
    {
        $locale = (string) $request->input('locale', 'it');
        $langA = (string) $request->input('lang_a', 'it');
        $langB = (string) $request->input('lang_b', 'en');
        $threadId = (string) $request->input('thread_id', '');

        try {
            $chatHistory = new QuoterChatHistory($threadId);

            // Carichiamo TUTTE le frasi della trascrizione dal thread di traduzione.
            // Il controller di streaming salva in Quoter con role "translation":
            // qui le trasformiamo in messaggi utente sequenziali per il memory provider.
            $records = Quoter::where('thread_id', $threadId)
                ->orderBy('created_at', 'asc')
                ->get(['content']);

            $messages = [];
            foreach ($records as $record) {
                $text = trim((string) $record->content);
                if ($text === '') {
                    continue;
                }
                $messages[] = new UserMessage($text);
            }

            if (! empty($messages)) {
                $chatHistory->setMessages($messages);
            }

            Log::info('InterviewMindMapController.generate START', [
                'thread_id' => $threadId,
                'locale' => $locale,
                'lang_a' => $langA,
                'lang_b' => $langB,
            ]);

            $agent = InterviewMindMapAgent::make()
                ->withLocale($locale)
                ->withLanguages($langA, $langB)
                ->withChatHistory($chatHistory);

            $full = '';
            // Il messaggio utente è solo un trigger, il contenuto reale viene dalla history
            $userMessage = new UserMessage('GENERA_MAPPA_MENTALE');
            $stream = $agent->stream($userMessage);

            foreach ($stream as $chunk) {
                if (is_string($chunk)) {
                    $full .= $chunk;
                }
            }

            $full = trim($full);

            if ($full === '') {
                return response()->json([
                    'error' => 'Nessuna mappa mentale generata.',
                ], 500);
            }

            // Il modello deve restituire SOLO JSON con nodi e archi
            $decoded = json_decode($full, true);
            if (! is_array($decoded)) {
                Log::warning('InterviewMindMapController.generate: JSON non valido', [
                    'thread_id' => $threadId,
                    'raw_preview' => mb_substr($full, 0, 200),
                ]);

                return response()->json([
                    'error' => 'Formato mappa mentale non valido.',
                ], 500);
            }

            $nodes = isset($decoded['nodes']) && is_array($decoded['nodes']) ? $decoded['nodes'] : [];
            $edges = isset($decoded['edges']) && is_array($decoded['edges']) ? $decoded['edges'] : [];

            Log::info('InterviewMindMapController.generate DONE', [
                'thread_id' => $threadId,
                'nodes_count' => count($nodes),
                'edges_count' => count($edges),
            ]);

            return response()->json([
                'nodes' => $nodes,
                'edges' => $edges,
                'thread_id' => $threadId,
                'raw' => $full,
            ]);
        } catch (\Throwable $e) {
            Log::error('InterviewMindMapController.generate ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Errore durante la generazione della mappa mentale: '.$e->getMessage(),
            ], 500);
        }
    }
}
