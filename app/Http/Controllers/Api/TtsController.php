<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TtsController extends Controller
{
    /**
     * Genera audio tramite OpenAI TTS (unico servizio esterno già in uso, nessuna chiave extra).
     * POST /api/tts { text: string, locale?: string, voice?: string, format?: string }
     */
    public function synthesize(Request $request)
    {
        $text = (string) ($request->input('text') ?? '');
        // Normalizza locale su it-IT di default
        $locale = (string) ($request->input('locale') ?? 'it-IT');
        if ($locale === '' || !preg_match('/^[a-zA-Z]{2}([-_][a-zA-Z]{2})?$/', $locale)) {
            $locale = 'it-IT';
        }
        $voice = (string) ($request->input('voice') ?? $this->defaultVoiceForLocale($locale));
        // Usa mp3 come default (più leggero e veloce da decodificare lato client)
        $format = (string) ($request->input('format') ?? 'mp3'); // mp3|wav|opus|aac|flac

        if (trim($text) === '') {
            return response()->json(['error' => 'Testo mancante'], 422);
        }

        // Pulisce eventuale HTML residuo e limita la lunghezza
        $text = strip_tags($text);
        $text = mb_substr($text, 0, 4000);

        try {
            $apiKey = config('openapi.key');
            if (!$apiKey) {
                return response()->json(['error' => 'OpenAI API key mancante in configurazione.'], 500);
            }

            $client = new Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false,
                'timeout' => 60,
            ]);

            $payload = [
                'model' => 'gpt-4o-mini-tts',
                // Forziamo l'italiano: il modello rileva la lingua dal testo, ma la voce scelta è multi-lingua
                'input' => $text,
                'voice' => $voice,
                'format' => $format,
            ];

            $resp = $client->post('audio/speech', [ 'json' => $payload ]);
            $status = $resp->getStatusCode();
            if ($status < 200 || $status >= 300) {
                $body = (string) $resp->getBody();
                Log::error('OpenAI TTS error', ['status' => $status, 'body' => $body]);
                return response()->json(['error' => 'Errore TTS OpenAI'], 502);
            }

            $audio = $resp->getBody()->getContents();
            $contentType = $this->contentTypeForFormat($format);

            return response($audio, 200, [
                'Content-Type' => $contentType,
                'Content-Length' => strlen($audio),
                'Cache-Control' => 'no-store',
            ]);
        } catch (\Throwable $e) {
            Log::error('TtsController.synthesize error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Errore generazione audio'], 500);
        }
    }

    private function defaultVoiceForLocale(string $locale): string
    {
        $l = strtolower($locale);
        // Mappatura semplice verso voci generiche OpenAI
        return match (true) {
            str_starts_with($l, 'it') => 'alloy',
            str_starts_with($l, 'en') => 'alloy',
            str_starts_with($l, 'es') => 'alloy',
            str_starts_with($l, 'fr') => 'alloy',
            str_starts_with($l, 'de') => 'alloy',
            default => 'alloy',
        };
    }

    private function contentTypeForFormat(string $format): string
    {
        return match (strtolower($format)) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'opus' => 'audio/ogg',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
            default => 'audio/wav',
        };
    }
}


