<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Safe\fopen;
use function Safe\json_decode;
use function Safe\preg_match;

class WhisperTranscriptionController extends Controller
{
    /**
     * Trascrive un chunk audio usando Groq Whisper Large V3 Turbo.
     *
     * POST /api/whisper/transcribe
     * Form-data:
     *  - audio: file (audio/webm, audio/ogg, ecc.)
     *  - lang: stringa BCP-47 opzionale (es. it-IT, en-US)
     *
     * Ritorna: { text: string }
     */
    public function transcribe(Request $request)
    {
        if (! $request->hasFile('audio') || ! $request->file('audio')->isValid()) {
            return response()->json(['error' => 'File audio mancante o non valido.'], 422);
        }

        $audio = $request->file('audio');
        $langHeader = $request->input('lang');

        // Se lang è vuoto o non presente, non passiamo il parametro language a Whisper
        // per permettere l'auto-rilevamento della lingua
        $language = null;
        if ($langHeader && trim($langHeader) !== '') {
            // Converte BCP-47 (es. it-IT) in codice lingua iso (es. it) per Whisper
            $langHeader = strtolower(trim((string) $langHeader));
            if (preg_match('/^[a-z]{2}/', $langHeader, $m)) {
                $language = $m[0];
            }
        }

        // Elenco di lingue consentite (codici ISO-639-1 in minuscolo, es. "it", "en"),
        // passato dal frontend come stringa separata da virgole (es. "it,en").
        $allowedLangsRaw = (string) ($request->input('allowed_langs') ?? '');
        $allowedLanguages = [];
        if (trim($allowedLangsRaw) !== '') {
            foreach (explode(',', $allowedLangsRaw) as $code) {
                $code = strtolower(trim((string) $code));
                if ($code !== '' && ! in_array($code, $allowedLanguages, true)) {
                    $allowedLanguages[] = $code;
                }
            }
        }

        // TEMP: per stabilizzare il comportamento (e far tornare l'auto-pausa),
        // inviamo UNA SOLA VOLTA a Whisper lasciando l'auto-rilevamento lingua.
        // Quindi: ignoriamo qualsiasi lingua forzata dal frontend e disattiviamo
        // la logica "forced languages" (multi-call / parallelo).
        $forceAutoDetectLanguage = true;
        if ($forceAutoDetectLanguage) {
            $language = null;
        }

        try {
            $apiKey = config('groq.key');
            if (! $apiKey) {
                return response()->json(['error' => 'Groq API key mancante in configurazione.'], 500);
            }

            $client = new Client([
                'base_uri' => 'https://api.groq.com/openai/v1/',
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                ],
                'http_errors' => false,
                'timeout' => 60,
            ]);

            $originalName = $audio->getClientOriginalName() ?: 'audio.webm';
            $detectedMime = $audio->getMimeType();

            // Forziamo un Content-Type compatibile con Whisper in base all'estensione
            $lowerName = strtolower($originalName);
            if (str_ends_with($lowerName, '.wav')) {
                $contentType = 'audio/wav';
            } elseif (str_ends_with($lowerName, '.ogg')) {
                $contentType = 'audio/ogg';
            } else {
                // Default per il nostro recorder è webm
                $contentType = 'audio/webm';
            }

            Log::info('WhisperTranscriptionController: riceuto audio', [
                'original_name' => $audio->getClientOriginalName(),
                'mime' => $detectedMime,
                'forced_content_type' => $contentType,
                'size' => $audio->getSize(),
                'language' => $language,
                'allowed_languages' => $allowedLanguages,
            ]);

            // Costruisce la base dei parametri multipart (senza il modello/response_format, che proveremo a scegliere)
            $multipartBase = [
                [
                    'name' => 'file',
                    'contents' => fopen($audio->getRealPath(), 'r'),
                    'filename' => $originalName,
                    'headers' => [
                        'Content-Type' => $contentType,
                    ],
                ],
                // Temperature a zero per ridurre al minimo le allucinazioni
                [
                    'name' => 'temperature',
                    'contents' => '0',
                ],
            ];

            // Aggiunge il parametro language solo se specificato.
            // In modalità auto-detect (TEMP) NON lo inviamo mai.
            if ($language !== null) {
                $multipartBase[] = [
                    'name' => 'language',
                    'contents' => $language,
                ];
            }

            // Usa Whisper Large V3 Turbo di Groq
            $modelsToTry = [
                'whisper-large-v3-turbo',
            ];

            // TEMP: disabilitiamo le lingue forzate (multi-call/parallelo).
            // Manteniamo $allowedLanguages solo per i filtri/guard (se attivi),
            // ma la trascrizione viene fatta con auto-detect in un'unica chiamata.

            $response = null;
            $body = '';
            $status = 0;
            $usedModel = null;
            $multipartForRetry = null;

            foreach ($modelsToTry as $candidateModel) {
                $multipart = $multipartBase;
                $multipart[] = [
                    'name' => 'model',
                    'contents' => $candidateModel,
                ];

                // Whisper 3 Turbo supporta verbose_json
                $responseFormat = 'verbose_json';
                $multipart[] = [
                    'name' => 'response_format',
                    'contents' => $responseFormat,
                ];

                $resp = $client->post('audio/transcriptions', [
                    'multipart' => $multipart,
                ]);

                $status = $resp->getStatusCode();
                $body = (string) $resp->getBody();

                if ($status >= 200 && $status < 300) {
                    $response = $resp;
                    $usedModel = $candidateModel;
                    $multipartForRetry = $multipart;
                    break;
                }

                $decodedError = json_decode($body, true);
                $errorCode = is_array($decodedError) && isset($decodedError['error']['code'])
                    ? (string) $decodedError['error']['code']
                    : null;
                $errorMessage = is_array($decodedError) && isset($decodedError['error']['message'])
                    ? (string) $decodedError['error']['message']
                    : '';

                $isModelMissingError = $status === 404
                    || $errorCode === 'model_not_found'
                    || str_contains(strtolower($errorMessage), 'does not exist')
                    || str_contains(strtolower($errorMessage), 'unknown model');

                // Se il modello non esiste / non è disponibile, logga e prova il successivo
                if ($isModelMissingError && $candidateModel !== 'whisper-large-v3-turbo') {
                    Log::warning('WhisperTranscriptionController: modello non disponibile, fallback al successivo', [
                        'model' => $candidateModel,
                        'status' => $status,
                        'error_code' => $errorCode,
                        'message' => $errorMessage,
                    ]);
                    continue;
                }

                // Per altri errori (rate limit, auth, ecc.) logghiamo ma non blocchiamo
                // il flusso dell'applicazione: restituiamo semplicemente testo vuoto,
                // così il frontend non vede un errore HTTP e il microfono non si blocca.
                Log::error('Groq Whisper error', [
                    'model' => $candidateModel,
                    'status' => $status,
                    'body' => $body,
                ]);

                return response()->json([
                    'text' => '',
                ]);
            }

            if (! $response || ! $usedModel) {
                return response()->json([
                    'text' => '',
                ]);
            }

            Log::info('WhisperTranscriptionController: modello di trascrizione utilizzato', [
                'model' => $usedModel,
            ]);

            $json = json_decode($body, true);

            // Applica i filtri di qualità sui segmenti Whisper
            $text = $this->extractHighQualityTextFromWhisperResponse($json);

            // Se dopo i filtri la trascrizione risulta vuota, proviamo UNA SOLA VOLTA
            // a reinviare l'audio a Whisper (stesso modello e stessi parametri).
            if ($text === '') {
                Log::info('WhisperTranscriptionController: testo vuoto dopo primo tentativo, retry senza guard', [
                    'model' => $usedModel,
                ]);

                $retryResponse = $client->post('audio/transcriptions', [
                    'multipart' => $multipartForRetry ?? $multipartBase,
                ]);

                $retryStatus = $retryResponse->getStatusCode();
                $retryBody = (string) $retryResponse->getBody();

                if ($retryStatus >= 200 && $retryStatus < 300) {
                    $retryJson = json_decode($retryBody, true);
                    $text = $this->extractHighQualityTextFromWhisperResponse($retryJson);
                } else {
                    Log::error('WhisperTranscriptionController: errore nel retry Whisper (text vuoto)', [
                        'status' => $retryStatus,
                        'body' => $retryBody,
                    ]);
                    $text = '';
                }
            }

            // Controllo grammaticale / allucinazioni / lingua con un modello leggero (gpt-4o-mini).
            // Il guard accetta la frase solo se:
            // - è coerente e "umana"
            // - sembra scritta in una delle lingue consentite (allowedLanguages)
            // - non appare palesemente fuori contesto (sottotitoli random, disclaimer, spam, ecc.)
            // In caso di dubbio, chiediamo UNA SOLA VOLTA un retry della trascrizione;
            // se fallisce di nuovo, restituiamo testo vuoto.
            if ($text !== '' && ! $this->isTranscriptionPlausible($text, $allowedLanguages)) {
                Log::info('WhisperTranscriptionController: trascrizione bocciata dal guard, tentativo di retry', [
                    'text_preview' => mb_substr($text, 0, 120),
                ]);

                // Secondo tentativo: nuova chiamata Whisper con gli stessi parametri
                // (stesso modello scelto, stessa configurazione)
                $retryResponse = $client->post('audio/transcriptions', [
                    'multipart' => $multipartForRetry ?? $multipartBase,
                ]);

                $retryStatus = $retryResponse->getStatusCode();
                $retryBody = (string) $retryResponse->getBody();

                if ($retryStatus >= 200 && $retryStatus < 300) {
                    $retryJson = json_decode($retryBody, true);
                    $retryText = $this->extractHighQualityTextFromWhisperResponse($retryJson);

                    if ($retryText !== '' && $this->isTranscriptionPlausible($retryText, $allowedLanguages)) {
                        $text = $retryText;
                    } else {
                        $text = '';
                    }
                } else {
                    Log::error('WhisperTranscriptionController: errore nel retry Whisper', [
                        'status' => $retryStatus,
                        'body' => $retryBody,
                    ]);
                    $text = '';
                }
            }

            return response()->json([
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhisperTranscriptionController.transcribe error', [
                'error' => $e->getMessage(),
            ]);

            // In caso di errore imprevisto, restituiamo comunque HTTP 200 con testo vuoto:
            // il frontend semplicemente ignorerà il chunk e il microfono non verrà bloccato.
            return response()->json([
                'text' => '',
            ]);
        }
    }

    /**
     * Trascrive l'audio forzando esplicitamente ciascuna lingua consentita
     * (tipicamente una coppia, es. it/en) con chiamate parallele a Whisper.
     *
     * Per ogni lingua costruisce una richiesta indipendente verso Whisper
     * (stesso file, stesso modello preferito) e attende che tutte le risposte
     * arrivino. Restituisce la prima trascrizione "plausibile" trovata in ordine
     * di priorità dato da $allowedLanguages, oppure stringa vuota se nessuna va bene.
     *
     * @param Client $client
     * @param mixed  $audio
     */
    private function transcribeWithForcedLanguages(Client $client, $audio, array $allowedLanguages, array $modelsToTry, string $originalName, string $contentType): string
    {
        if (empty($allowedLanguages)) {
            return '';
        }

        // Usiamo il primo modello della lista come "best effort" per il ramo parallelo;
        // se per qualche ragione fallisce, restituiremo stringa vuota e lasceremo
        // che il chiamante decida come comportarsi.
        $preferredModel = $modelsToTry[0] ?? 'whisper-1';
        $responseFormat = (strpos($preferredModel, 'gpt-4o') === 0) ? 'json' : 'verbose_json';

        $promises = [];
        $orderedForcedLangs = [];

        foreach ($allowedLanguages as $forcedLanguage) {
            $forcedLanguage = strtolower(trim((string) $forcedLanguage));
            if ($forcedLanguage === '') {
                continue;
            }

            $multipart = [
                [
                    'name' => 'file',
                    'contents' => fopen($audio->getRealPath(), 'r'),
                    'filename' => $originalName,
                    'headers' => [
                        'Content-Type' => $contentType,
                    ],
                ],
                [
                    'name' => 'temperature',
                    'contents' => '0',
                ],
                [
                    'name' => 'language',
                    'contents' => $forcedLanguage,
                ],
                [
                    'name' => 'model',
                    'contents' => $preferredModel,
                ],
                [
                    'name' => 'response_format',
                    'contents' => $responseFormat,
                ],
            ];

            $key = $forcedLanguage;
            $orderedForcedLangs[] = $forcedLanguage;

            $promises[$key] = $client->postAsync('audio/transcriptions', [
                'multipart' => $multipart,
            ]);
        }

        if (empty($promises)) {
            return '';
        }

        // Esegue tutte le chiamate in parallelo e attende che siano completate
        $results = Utils::settle($promises)->wait();

        // Seleziona la prima trascrizione plausibile seguendo l'ordine originale
        foreach ($orderedForcedLangs as $forcedLanguage) {
            $key = $forcedLanguage;

            if (! isset($results[$key])) {
                continue;
            }

            $result = $results[$key];
            if (($result['state'] ?? '') !== 'fulfilled') {
                Log::warning('WhisperTranscriptionController (parallel): richiesta fallita', [
                    'forced_language' => $forcedLanguage,
                    'reason' => isset($result['reason']) ? (string) $result['reason'] : null,
                ]);
                continue;
            }

            /** @var \Psr\Http\Message\ResponseInterface $resp */
            $resp = $result['value'];
            $status = $resp->getStatusCode();
            $body = (string) $resp->getBody();

            if ($status < 200 || $status >= 300) {
                $decodedError = json_decode($body, true);
                $errorCode = is_array($decodedError) && isset($decodedError['error']['code'])
                    ? (string) $decodedError['error']['code']
                    : null;
                $errorMessage = is_array($decodedError) && isset($decodedError['error']['message'])
                    ? (string) $decodedError['error']['message']
                    : '';

                Log::error('Groq Whisper error (parallel lingue forzate)', [
                    'forced_language' => $forcedLanguage,
                    'status' => $status,
                    'body' => $body,
                    'error_code' => $errorCode,
                    'message' => $errorMessage,
                ]);

                continue;
            }

            $json = json_decode($body, true);
            $text = $this->extractHighQualityTextFromWhisperResponse($json);

            if ($text === '') {
                continue;
            }

            if (! $this->isTranscriptionPlausible($text, [$forcedLanguage])) {
                Log::info('WhisperTranscriptionController (parallel): trascrizione bocciata dal guard', [
                    'forced_language' => $forcedLanguage,
                    'text_preview' => mb_substr($text, 0, 120),
                ]);
                continue;
            }

            Log::info('WhisperTranscriptionController: trascrizione riuscita con lingua forzata (parallel)', [
                'forced_language' => $forcedLanguage,
                'model' => $preferredModel,
            ]);

            return $text;
        }

        return '';
    }

    /**
     * Estrae il testo "buono" da una risposta Whisper in verbose_json,
     * scartando segmenti con bassa confidenza o alta probabilità di silenzio.
     *
     * Regole:
     * - scarta segmenti con avg_logprob < -1 (bassa confidenza)
     * - scarta segmenti con no_speech_prob > 0.5 (silenzio/rumore)
     */
    private function extractHighQualityTextFromWhisperResponse(mixed $json): string
    {
        $text = '';

        if (is_array($json) && isset($json['segments']) && is_array($json['segments'])) {
            $acceptedTexts = [];

            foreach ($json['segments'] as $segment) {
                $segmentText = isset($segment['text']) ? (string) $segment['text'] : '';
                $segmentText = trim($segmentText);
                if ($segmentText === '') {
                    continue;
                }

                $avgLogprob = $segment['avg_logprob'] ?? null;
                $noSpeechProb = $segment['no_speech_prob'] ?? null;

                // Filtra per avg_logprob
                if (is_numeric($avgLogprob) && (float) $avgLogprob < -1.0) {
                    Log::info('WhisperTranscriptionController: segmento scartato per avg_logprob', [
                        'avg_logprob' => $avgLogprob,
                        'text_preview' => mb_substr($segmentText, 0, 80),
                    ]);
                    continue;
                }

                // Filtra per no_speech_prob
                if (is_numeric($noSpeechProb) && (float) $noSpeechProb > 0.5) {
                    Log::info('WhisperTranscriptionController: segmento scartato per no_speech_prob', [
                        'no_speech_prob' => $noSpeechProb,
                        'text_preview' => mb_substr($segmentText, 0, 80),
                    ]);
                    continue;
                }

                $acceptedTexts[] = $segmentText;
            }

            $text = trim(implode(' ', $acceptedTexts));

            // Se dopo il filtro non resta nulla, come fallback usiamo comunque il campo "text"
            if ($text === '') {
                $text = (string) ($json['text'] ?? '');
            }
        } else {
            // Fallback: struttura inattesa, usiamo il campo "text" se presente
            $text = (string) ($json['text'] ?? '');
        }

        return $text;
    }

    /**
     * Usa un modello GPT leggero per verificare se la trascrizione sembra
     * coerente, umana e non "allucinata".
     *
     * Ritorna true se il testo è plausibile, false se è sospetto.
     */
    private function isTranscriptionPlausible(string $text, array $allowedLanguages = []): bool
    {
        $clean = trim($text);
        if ($clean === '') {
            return false;
        }

        try {
            $apiKey = config('groq.key');
            if (! $apiKey) {
                return true;
            }

            $client = new Client([
                'base_uri' => 'https://api.groq.com/openai/v1/',
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false,
                'timeout' => 30,
            ]);

            // Costruiamo una descrizione testuale delle lingue consentite (es. "it, en")
            $allowedDesc = '';
            if (! empty($allowedLanguages)) {
                $allowedDesc = implode(', ', $allowedLanguages);
            }

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'temperature' => 0,
                    'max_tokens' => 2,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a strict ASR quality checker for a bilingual interpreter. '
                                .'Given a short automatic transcription, you MUST answer ONLY "OK" or "RETRY". '
                                .'ALLOWED LANGUAGE CODES: ['.$allowedDesc.']. '
                                .'Answer "OK" ONLY IF ALL of the following are true: '
                                .'(1) the sentence is coherent and sounds like something a human would say in a conversation; '
                                .'(2) the language of the text appears to be one of the allowed language codes (if the language is clearly different, answer "RETRY"); '
                                .'(3) the content could plausibly belong to an ongoing call or dialog (reject obvious subtitles, credits, URLs, ads, boilerplate legal text, UI labels, etc.). '
                                .'Answer "RETRY" for noise, random characters, truncated or repeated fragments, text in a clearly different language, or content that is obviously unrelated to a live conversation. '
                                .'Do not explain, no extra words.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $clean,
                        ],
                    ],
                ],
            ]);

            $status = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($status < 200 || $status >= 300) {
                Log::warning('Groq quality check error', [
                    'status' => $status,
                    'body' => $body,
                ]);

                return true;
            }

            $json = json_decode($body, true);
            $choice = $json['choices'][0] ?? null;
            $content = '';
            if ($choice && isset($choice['message']['content'])) {
                $content = (string) $choice['message']['content'];
            }

            $normalized = strtolower(trim($content));

            if ($normalized === 'retry') {
                return false;
            }

            // Default: considera buono tutto ciò che non è un "retry" esplicito
            return true;
        } catch (\Throwable $e) {
            Log::warning('WhisperTranscriptionController.isTranscriptionPlausible error', [
                'error' => $e->getMessage(),
            ]);

            // In caso di errore nel guard, non blocchiamo la trascrizione
            return true;
        }
    }
}
