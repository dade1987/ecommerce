<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use App\Neuron\InterviewAssistantAgent;
use App\Neuron\QuoterChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\UserMessage;
use function Safe\preg_match;

class InterviewSuggestionController extends Controller
{
    /**
     * Genera un suggerimento di risposta per un colloquio,
     * basandosi sul CV, sulla frase/testo corrente e sulla storia delle conversazioni precedenti.
     *
     * Endpoint:
     * POST /api/chatbot/interview-suggestion
     * body JSON: { cv_text: string, utterance: string, locale?: string, lang_a?: string, lang_b?: string, thread_id?: string }
     */
    public function suggest(Request $request)
    {
        $cvText = (string) $request->input('cv_text', '');
        $utterance = (string) $request->input('utterance', '');
        $locale = (string) $request->input('locale', 'it');
        $langA = (string) $request->input('lang_a', 'it');
        $langB = (string) $request->input('lang_b', 'en');
        $threadId = (string) $request->input('thread_id', '');

        if (trim($cvText) === '' || trim($utterance) === '') {
            return response()->json([
                'error' => 'cv_text e utterance sono obbligatori.',
            ], 422);
        }

        // Genera thread_id se non fornito
        if (empty($threadId)) {
            $threadId = 'interview_'.uniqid('', true);
        }

        Log::info('InterviewSuggestionController.suggest START', [
            'thread_id' => $threadId,
            'cv_length' => strlen($cvText),
            'utterance_preview' => mb_substr($utterance, 0, 160),
            'locale' => $locale,
            'lang_a' => $langA,
            'lang_b' => $langB,
        ]);

        try {
            // Crea la chat history per mantenere il contesto
            $chatHistory = new QuoterChatHistory($threadId);

            // Controlla se l'argomento è cambiato rispetto alla conversazione precedente
            $previousMessages = $chatHistory->getMessages();
            $topicChanged = true; // Default: considera cambiato se non ci sono messaggi precedenti

            if (! empty($previousMessages)) {
                // Prendi l'ultimo messaggio dell'utente per confrontare l'argomento
                $lastUserMessage = null;
                for ($i = count($previousMessages) - 1; $i >= 0; $i--) {
                    if ($previousMessages[$i] instanceof UserMessage) {
                        $lastUserMessage = $previousMessages[$i];
                        break;
                    }
                }

                if ($lastUserMessage) {
                    $lastUtterance = (string) $lastUserMessage->getContent();
                    // Considera l'argomento cambiato se l'utterance è significativamente diversa
                    // Usa una semplice comparazione: se sono molto simili, l'argomento non è cambiato
                    $similarity = $this->calculateSimilarity($lastUtterance, $utterance);
                    $topicChanged = $similarity < 0.7; // Soglia di similarità: se < 70%, argomento cambiato
                }
            }

            // Se l'argomento non è cambiato, restituisci un suggerimento vuoto
            if (! $topicChanged) {
                Log::info('InterviewSuggestionController: argomento non cambiato, restituisco suggerimento vuoto', [
                    'thread_id' => $threadId,
                    'utterance' => mb_substr($utterance, 0, 100),
                ]);

                return response()->json([
                    'suggestion_lang_a' => '',
                    'suggestion_lang_b' => '',
                    'thread_id' => $threadId,
                    'raw' => '',
                    'topic_changed' => false,
                ]);
            }

            $agent = InterviewAssistantAgent::make()
                ->withLocale($locale)
                ->withCv($cvText)
                ->withLanguages($langA, $langB)
                ->withChatHistory($chatHistory);

            $full = '';
            $userMessage = new UserMessage($utterance);
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

            // Proviamo a separare blocco LINGUA A / LINGUA B con nomi dinamici
            $langAUpper = strtoupper($langA);
            $langBUpper = strtoupper($langB);

            $langAText = null;
            $langBText = null;

            // Mappa i codici lingua ai nomi completi (in MAIUSCOLO, con varianti separate da "|")
            $langNames = [
                'it' => 'ITALIANO',
                'en' => 'INGLESE|ENGLISH',
                'es' => 'ESPAÑOL|SPANISH|SPAGNOLO',
                'fr' => 'FRANÇAIS|FRENCH|FRANCESE',
                'de' => 'DEUTSCH|GERMAN|TEDESCO',
                'pt' => 'PORTUGUÊS|PORTUGUESE|PORTOGHESE',
                'nl' => 'NEDERLANDS|DUTCH|OLANDESE',
                'sv' => 'SVENSKA|SWEDISH|SVEDESE',
                'no' => 'NORSK|NORWEGIAN|NORVEGESE',
                'da' => 'DANSK|DANISH|DANESE',
                'fi' => 'SUOMI|FINNISH|FINLANDESE',
                'pl' => 'POLSKI|POLISH|POLACCO',
                'cs' => 'ČEŠTINA|CZECH|CECO',
                'sk' => 'SLOVENČINA|SLOVAK|SLOVACCO',
                'hu' => 'MAGYAR|HUNGARIAN|UNGARESE',
                'ro' => 'ROMÂNĂ|ROMANIAN|RUMENO',
                'bg' => 'БЪЛГАРСКИ|BULGARIAN|BULGARO',
                'el' => 'ΕΛΛΗΝΙΚΑ|GREEK|GRECO',
                'uk' => 'УКРАЇНСЬКА|UKRAINIAN|UCRAINO',
                'ru' => 'РУССКИЙ|RUSSIAN|RUSSO',
                'tr' => 'TÜRKÇE|TURKISH|TURCO',
                'ar' => 'العَرَبِيَّة|ARABIC|ARABO',
                'he' => 'עברית|HEBREW|EBRAICO',
                'hi' => 'हिन्दी|HINDI',
                'zh' => '中文|CHINESE|CINESE',
                'ja' => '日本語|JAPANESE|GIAPPONESE',
                'ko' => '한국어|KOREAN|COREANO',
                'id' => 'BAHASA INDONESIA|INDONESIAN',
                'ms' => 'BAHASA MELAYU|MALAY',
                'th' => 'ไทย|THAI',
                'vi' => 'TIẾNG VIỆT|VIETNAMESE|VIETNAMITA',
            ];

            $nameA = $langNames[$langA] ?? $langAUpper;
            $nameB = $langNames[$langB] ?? $langBUpper;

            // Prova con nomi completi (supporta varianti) - gestisce anche liste numerate
            $patternFull = "/(?:{$nameA}|{$langAUpper}):\s*\n?(.*?)\s*\n\s*(?:{$nameB}|{$langBUpper}):\s*\n?(.*?)$/is";
            if (preg_match($patternFull, $full, $m)) {
                $langAText = trim($m[1] ?? '');
                $langBText = trim($m[2] ?? '');
            }

            // Se non funziona, prova a cercare le sezioni separate
            if (empty($langAText) || empty($langBText)) {
                // Cerca solo la sezione LINGUA A
                if (preg_match("/(?:{$nameA}|{$langAUpper}):\s*\n?(.*?)(?=\n\s*(?:{$nameB}|{$langBUpper}|$))/is", $full, $mA)) {
                    $langAText = trim($mA[1] ?? '');
                }

                // Cerca solo la sezione LINGUA B
                if (preg_match("/(?:{$nameB}|{$langBUpper}):\s*\n?(.*?)$/is", $full, $mB)) {
                    $langBText = trim($mB[1] ?? '');
                }
            }

            if (empty($langAText) || empty($langBText)) {
                // Fallback finale: restituiamo il testo completo in entrambi i campi
                $langAText = $full;
                $langBText = $full;
            }

            Log::info('InterviewSuggestionController.suggest DONE', [
                'thread_id' => $threadId,
                'lang_a_length' => strlen($langAText),
                'lang_b_length' => strlen($langBText),
            ]);

            return response()->json([
                'suggestion_lang_a' => $langAText,
                'suggestion_lang_b' => $langBText,
                'thread_id' => $threadId,
                'raw' => $full,
                'topic_changed' => true,
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

    /**
     * Calcola la similarità tra due testi (0-1, dove 1 = identici)
     */
    private function calculateSimilarity(string $text1, string $text2): float
    {
        $text1 = mb_strtolower(trim($text1));
        $text2 = mb_strtolower(trim($text2));

        if ($text1 === $text2) {
            return 1.0;
        }

        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        // Usa similar_text nativa di PHP per calcolare la similarità
        $percent = 0.0;
        \similar_text($text1, $text2, $percent);

        return $percent / 100.0;
    }
}
