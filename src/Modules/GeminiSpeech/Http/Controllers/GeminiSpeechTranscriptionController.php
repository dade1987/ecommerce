<?php

namespace Modules\GeminiSpeech\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeminiSpeechTranscriptionController extends Controller
{
    /**
     * Trascrive un chunk audio usando Gemini via HTTP (GEMINI_API_KEY).
     *
     * POST /api/gemini-speech/transcribe
     * Form-data:
     *  - audio: file (audio/webm, audio/ogg, ecc.)
     *  - lang: stringa BCP-47 opzionale (es. it-IT, en-US) – usata solo come hint nel prompt
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

        $languageHint = trim($langHeader) !== '' ? $langHeader : 'auto';

        $apiKey = config('services.gemini.key');
        if (! $apiKey) {
            return response()->json(['error' => 'Gemini API key mancante in configurazione.'], 500);
        }

        try {
            $content = (string) file_get_contents($audio->getRealPath());
            $base64Audio = base64_encode($content);

            $prompt = sprintf(
                "Trascrivi in modo fedele l'audio fornito.\n".
                "Restituisci SOLO il testo trascritto, senza spiegazioni, senza virgolette, senza note.\n".
                "Hint lingua (può essere BCP-47 o \"auto\"): %s",
                $languageHint
            );

            $body = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    // Forziamo audio/webm anche se il browser riporta video/webm
                                    // perché Gemini si aspetta un mime audio valido.
                                    'mime_type' => 'audio/webm',
                                    'data' => $base64Audio,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $client = new Client([
                'base_uri' => 'https://generativelanguage.googleapis.com/',
                'timeout' => 60,
                'http_errors' => false,
            ]);

            // Endpoint ufficiale Generative Language API v1.
            // I modelli 1.5 sono stati deprecati; usiamo un modello 2.x supportato.
            $response = $client->post(
                'v1/models/gemini-2.0-flash:generateContent?key='.$apiKey,
                [
                    'json' => $body,
                ]
            );

            $status = $response->getStatusCode();
            $raw = (string) $response->getBody();

            if ($status < 200 || $status >= 300) {
                Log::error('GeminiSpeechTranscriptionController: HTTP error', [
                    'status' => $status,
                    'body' => $raw,
                ]);

                return response()->json([
                    'error' => 'Errore HTTP Gemini',
                    'status' => $status,
                ], 502);
            }

            $json = json_decode($raw, true);

            $text = '';
            if (isset($json['candidates'][0]['content']['parts'])) {
                $parts = $json['candidates'][0]['content']['parts'];
                $texts = [];
                foreach ($parts as $part) {
                    if (isset($part['text'])) {
                        $texts[] = (string) $part['text'];
                    }
                }
                $text = trim(implode(' ', $texts));
            }

            return response()->json([
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::error('GeminiSpeechTranscriptionController.transcribe error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Errore trascrizione audio con Gemini'], 500);
        }
    }
}


