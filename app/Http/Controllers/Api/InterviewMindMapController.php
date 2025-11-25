<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Neuron\InterviewMindMapAgent;
use App\Neuron\QuoterChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
use function Safe\json_decode;

class InterviewMindMapController extends Controller
{
    /**
     * Genera una mappa mentale multilingua basata su:
     * - CV dell'utente
     * - storia delle conversazioni (thread_id)
     *
     * Endpoint:
     * POST /api/chatbot/interview-mindmap
     * body JSON: { cv_text: string, locale?: string, lang_a?: string, lang_b?: string, thread_id?: string }
     */
    public function generate(Request $request)
    {
        $cvText = (string) $request->input('cv_text', '');
        $locale = (string) $request->input('locale', 'it');
        $langA = (string) $request->input('lang_a', 'it');
        $langB = (string) $request->input('lang_b', 'en');
        $threadId = (string) $request->input('thread_id', '');

        if (trim($cvText) === '') {
            return response()->json([
                'error' => 'cv_text è obbligatorio.',
            ], 422);
        }

        // Genera thread_id se non fornito
        if (empty($threadId)) {
            $threadId = 'interview_mm_'.uniqid('', true);
        }

        Log::info('InterviewMindMapController.generate START', [
            'thread_id' => $threadId,
            'cv_length' => strlen($cvText),
            'locale' => $locale,
            'lang_a' => $langA,
            'lang_b' => $langB,
        ]);

        try {
            $chatHistory = new QuoterChatHistory($threadId);

            $agent = InterviewMindMapAgent::make()
                ->withLocale($locale)
                ->withCv($cvText)
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
