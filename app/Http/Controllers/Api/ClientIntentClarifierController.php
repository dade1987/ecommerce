<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Neuron\ClientIntentClarifierAgent;
use App\Neuron\QuoterChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;

class ClientIntentClarifierController extends Controller
{
    /**
     * Chiarisce l'intenzione dell'interlocutore
     * a partire da:
     * - alcune frasi focali (focus_text)
     * - il ruolo dell'interlocutore (interlocutor_role)
     * - l'eventuale history del thread di trascrizione (thread_id).
     *
     * Endpoint:
     * POST /api/chatbot/interpreter-clarify-intent
     * body JSON: { focus_text: string, interlocutor_role?: string, locale?: string, lang_a?: string, lang_b?: string, thread_id?: string }
     */
    public function clarify(Request $request)
    {
        $focusText = (string) $request->input('focus_text', '');
        $interlocutorRole = (string) $request->input('interlocutor_role', '');
        $locale = (string) $request->input('locale', 'it');
        $langA = (string) $request->input('lang_a', 'it');
        $langB = (string) $request->input('lang_b', 'en');
        $threadId = (string) $request->input('thread_id', '');

        if (trim($focusText) === '') {
            return response()->json([
                'error' => 'focus_text è obbligatorio.',
            ], 422);
        }

        if ($threadId === '') {
            $threadId = 'intent_'.uniqid('', true);
        }

        Log::info('ClientIntentClarifierController.clarify START', [
            'thread_id' => $threadId,
            'focus_preview' => mb_substr($focusText, 0, 200),
            'interlocutor_role' => $interlocutorRole,
            'locale' => $locale,
            'lang_a' => $langA,
            'lang_b' => $langB,
        ]);

        try {
            $chatHistory = new QuoterChatHistory($threadId);

            $agent = ClientIntentClarifierAgent::make()
                ->withLocale($locale)
                ->withFocusText($focusText)
                ->withInterlocutorRole($interlocutorRole ?: null)
                ->withLanguages($langA, $langB)
                ->withChatHistory($chatHistory);

            $full = '';
            $userMessage = new UserMessage("CHIARISCI_INTENZIONE_INTERLOCUTORE:\n".$focusText);
            $stream = $agent->stream($userMessage);

            foreach ($stream as $chunk) {
                if (is_string($chunk)) {
                    $full .= $chunk;
                }
            }

            $full = trim($full);

            if ($full === '') {
                return response()->json([
                    'error' => 'Nessuna spiegazione generata.',
                ], 500);
            }

            Log::info('ClientIntentClarifierController.clarify DONE', [
                'thread_id' => $threadId,
                'length' => strlen($full),
            ]);

            return response()->json([
                'explanation' => $full,
                'thread_id' => $threadId,
            ]);
        } catch (\Throwable $e) {
            Log::error('ClientIntentClarifierController.clarify ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Errore durante la generazione della spiegazione: '.$e->getMessage(),
            ], 500);
        }
    }
}
