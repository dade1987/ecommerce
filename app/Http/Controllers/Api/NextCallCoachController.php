<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Neuron\NextCallCoachAgent;
use App\Neuron\QuoterChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\UserMessage;
use function Safe\preg_match;

class NextCallCoachController extends Controller
{
    /**
     * Genera consigli per migliorare la prossima call,
     * basandosi su:
     * - obiettivo dichiarato (goal)
     * - trascrizione / appunti della call
     * - eventuale history del thread Quoter.
     *
     * Endpoint:
     * POST /api/chatbot/interview-next-call
     * body JSON: { goal: string, transcript_text?: string, locale?: string, lang_a?: string, lang_b?: string, thread_id?: string }
     */
    public function improve(Request $request)
    {
        $goal = (string) $request->input('goal', '');
        $transcript = (string) $request->input('transcript_text', '');
        $locale = (string) $request->input('locale', 'it');
        $langA = (string) $request->input('lang_a', 'it');
        $langB = (string) $request->input('lang_b', 'en');
        $threadId = (string) $request->input('thread_id', '');

        if (trim($goal) === '') {
            return response()->json([
                'error' => 'goal è obbligatorio.',
            ], 422);
        }

        // Genera thread_id se non fornito
        if (empty($threadId)) {
            $threadId = 'nextcall_'.uniqid('', true);
        }

        Log::info('NextCallCoachController.improve START', [
            'thread_id' => $threadId,
            'goal_preview' => mb_substr($goal, 0, 160),
            'transcript_length' => strlen($transcript),
            'locale' => $locale,
            'lang_a' => $langA,
            'lang_b' => $langB,
        ]);

        try {
            $chatHistory = new QuoterChatHistory($threadId);

            $agent = NextCallCoachAgent::make()
                ->withLocale($locale)
                ->withGoal($goal)
                ->withTranscript($transcript)
                ->withLanguages($langA, $langB)
                ->withChatHistory($chatHistory);

            $full = '';
            $userMessage = new UserMessage("OBIETTIVO_PROSSIMA_CALL:\n".$goal."\n\nTRASCRIZIONE_CALL:\n".$transcript);
            $stream = $agent->stream($userMessage);

            foreach ($stream as $chunk) {
                if (is_string($chunk)) {
                    $full .= $chunk;
                }
            }

            $full = trim($full);

            if ($full === '') {
                return response()->json([
                    'error' => 'Nessun suggerimento generato.',
                ], 500);
            }

            // Parsing simile a InterviewSuggestionController
            $langAUpper = strtoupper($langA);
            $langBUpper = strtoupper($langB);

            $langAText = null;
            $langBText = null;

            $langNames = [
                'it' => 'ITALIANO',
                'en' => 'INGLESE|ENGLISH',
                'es' => 'ESPAÑOL|SPANISH|SPAGNOLO',
                'fr' => 'FRANÇAIS|FRENCH|FRANCESE',
                'de' => 'DEUTSCH|GERMAN|TEDESCO',
                'pt' => 'PORTUGUÊS|PORTUGUESE|PORTOGHESE',
            ];

            $nameA = $langNames[$langA] ?? $langAUpper;
            $nameB = $langNames[$langB] ?? $langBUpper;

            $patternFull = "/(?:{$nameA}|{$langAUpper}):\s*\n?(.*?)\s*\n\s*(?:{$nameB}|{$langBUpper}):\s*\n?(.*?)$/is";
            if (preg_match($patternFull, $full, $m)) {
                $langAText = trim($m[1] ?? '');
                $langBText = trim($m[2] ?? '');
            }

            if (empty($langAText) || empty($langBText)) {
                if (preg_match("/(?:{$nameA}|{$langAUpper}):\s*\n?(.*?)(?=\n\s*(?:{$nameB}|{$langBUpper}|$))/is", $full, $mA)) {
                    $langAText = trim($mA[1] ?? '');
                }

                if (preg_match("/(?:{$nameB}|{$langBUpper}):\s*\n?(.*?)$/is", $full, $mB)) {
                    $langBText = trim($mB[1] ?? '');
                }
            }

            if (empty($langAText) || empty($langBText)) {
                $langAText = $full;
                $langBText = $full;
            }

            Log::info('NextCallCoachController.improve DONE', [
                'thread_id' => $threadId,
                'lang_a_length' => strlen($langAText),
                'lang_b_length' => strlen($langBText),
            ]);

            // Per il frontend di Interpreter interessa soprattutto la lingua selezionata dall'utente (lang_b).
            // Manteniamo comunque tips_lang_a nel payload per eventuali usi futuri,
            // ma il client può ignorarlo e mostrare solo tips_lang_b.
            return response()->json([
                'tips_lang_a' => $langAText,
                'tips_lang_b' => $langBText,
                'thread_id' => $threadId,
                'raw' => $full,
            ]);
        } catch (\Throwable $e) {
            Log::error('NextCallCoachController.improve ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Errore durante la generazione dei consigli: '.$e->getMessage(),
            ], 500);
        }
    }
}
