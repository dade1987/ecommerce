<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Safe\fopen;
use function Safe\json_decode;
use function Safe\preg_match;

class WhisperTranscriptionController extends Controller
{
    /**
     * Trascrive un chunk audio usando OpenAI Whisper.
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
        $langHeader = (string) ($request->input('lang') ?? 'it-IT');

        // Converte BCP-47 (es. it-IT) in codice lingua iso (es. it) per Whisper
        $language = 'it';
        $langHeader = strtolower(trim($langHeader));
        if (preg_match('/^[a-z]{2}/', $langHeader, $m)) {
            $language = $m[0];
        }

        try {
            $apiKey = config('openapi.key');
            if (! $apiKey) {
                return response()->json(['error' => 'OpenAI API key mancante in configurazione.'], 500);
            }

            $client = new Client([
                'base_uri' => 'https://api.openai.com/v1/',
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
            ]);

            $response = $client->post('audio/transcriptions', [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audio->getRealPath(), 'r'),
                        'filename' => $originalName,
                        'headers' => [
                            'Content-Type' => $contentType,
                        ],
                    ],
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1',
                    ],
                    [
                        'name' => 'language',
                        'contents' => $language,
                    ],
                ],
            ]);

            $status = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($status < 200 || $status >= 300) {
                Log::error('OpenAI Whisper error', [
                    'status' => $status,
                    'body' => $body,
                ]);

                $decoded = json_decode($body, true);
                $message = '';
                if (is_array($decoded) && isset($decoded['error']['message'])) {
                    $message = (string) $decoded['error']['message'];
                } else {
                    $message = mb_substr($body, 0, 500);
                }

                return response()->json([
                    'error' => 'Errore Whisper OpenAI',
                    'status' => $status,
                    'message' => $message,
                ], 502);
            }

            $json = json_decode($body, true);
            $text = (string) ($json['text'] ?? '');

            return response()->json([
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhisperTranscriptionController.transcribe error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Errore trascrizione audio'], 500);
        }
    }
}
