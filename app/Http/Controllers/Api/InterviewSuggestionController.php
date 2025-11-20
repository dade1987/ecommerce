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

            // Prova con nomi completi (supporta varianti)
            $patternFull = "/(?:{$nameA}|{$langAUpper}):\s*\n?(.*?)\s*\n?\s*(?:{$nameB}|{$langBUpper}):\s*\n?(.*?)$/is";
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
