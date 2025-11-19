<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Neuron\InterviewAssistantAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
use function Safe\preg_match;

class InterviewSuggestionController extends Controller
{
    /**
     * Genera un suggerimento di risposta per un colloquio,
     * basandosi esclusivamente sul CV e sulla frase/testo corrente.
     *
     * Endpoint:
     * POST /api/chatbot/interview-suggestion
     * body JSON: { cv_text: string, utterance: string, locale?: string, lang_a?: string, lang_b?: string }
     */
    public function suggest(Request $request)
    {
        $cvText = (string) $request->input('cv_text', '');
        $utterance = (string) $request->input('utterance', '');
        $locale = (string) $request->input('locale', 'it');
        $langA = (string) $request->input('lang_a', 'it');
        $langB = (string) $request->input('lang_b', 'en');

        if (trim($cvText) === '' || trim($utterance) === '') {
            return response()->json([
                'error' => 'cv_text e utterance sono obbligatori.',
            ], 422);
        }

        Log::info('InterviewSuggestionController.suggest START', [
            'cv_length' => strlen($cvText),
            'utterance_preview' => mb_substr($utterance, 0, 160),
            'locale' => $locale,
            'lang_a' => $langA,
            'lang_b' => $langB,
        ]);

        try {
            $agent = InterviewAssistantAgent::make()
                ->withLocale($locale)
                ->withCv($cvText)
                ->withLanguages($langA, $langB);

            $full = '';
            $stream = $agent->stream(new UserMessage($utterance));

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

            // Proviamo a separare blocco LINGUA A / LINGUA B con nomi dinamici
            $langAUpper = strtoupper($langA);
            $langBUpper = strtoupper($langB);

            $langAText = null;
            $langBText = null;

            // Prova con codici lingua esatti (IT, EN, ES, etc.)
            $pattern = "/{$langAUpper}:\s*(.*?)\s*{$langBUpper}:\s*(.*)/is";
            if (preg_match($pattern, $full, $m)) {
                $langAText = trim($m[1] ?? '');
                $langBText = trim($m[2] ?? '');
            }

            // Fallback: cerca con nomi completi comuni
            if ($langAText === null || $langBText === null) {
                $langNames = [
                    'it' => 'ITALIANO',
                    'en' => 'INGLESE|ENGLISH',
                    'es' => 'ESPAÑOL|SPANISH',
                    'fr' => 'FRANÇAIS|FRENCH',
                    'de' => 'DEUTSCH|GERMAN',
                    'pt' => 'PORTUGUÊS|PORTUGUESE',
                ];

                $nameA = $langNames[$langA] ?? $langAUpper;
                $nameB = $langNames[$langB] ?? $langBUpper;

                $patternAlt = "/({$nameA}):\s*(.*?)\s*({$nameB}):\s*(.*)/is";
                if (preg_match($patternAlt, $full, $m)) {
                    $langAText = trim($m[2] ?? '');
                    $langBText = trim($m[4] ?? '');
                }
            }

            if ($langAText === null || $langBText === null) {
                // Fallback finale: restituiamo il testo completo in entrambi i campi
                $langAText = $full;
                $langBText = $full;
            }

            Log::info('InterviewSuggestionController.suggest DONE', [
                'lang_a_length' => strlen($langAText),
                'lang_b_length' => strlen($langBText),
            ]);

            return response()->json([
                'suggestion_lang_a' => $langAText,
                'suggestion_lang_b' => $langBText,
                'raw' => $full,
            ]);
        } catch (\Throwable $e) {
            Log::error('InterviewSuggestionController.suggest ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Errore durante la generazione del suggerimento: '.$e->getMessage(),
            ], 500);
        }
    }
}
