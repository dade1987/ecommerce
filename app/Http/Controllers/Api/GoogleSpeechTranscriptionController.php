<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\SpeechClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Safe\file_get_contents;
use function Safe\preg_match;

class GoogleSpeechTranscriptionController extends Controller
{
    /**
     * Trascrive un chunk audio usando Google Cloud Speech-to-Text.
     *
     * POST /api/google-speech/transcribe
     * Form-data:
     *  - audio: file (audio/webm, audio/ogg, ecc.)
     *  - lang: stringa BCP-47 opzionale (es. it-IT, en-US)
     *
     * Ritorna: { text: string }
     */
    public function transcribe(Request $request): \Illuminate\Http\JsonResponse
    {
        if (! $request->hasFile('audio') || ! $request->file('audio')->isValid()) {
            return response()->json(['error' => 'File audio mancante o non valido.'], 422);
        }

        $audio = $request->file('audio');
        /** @var string|null $langInput */
        $langInput = $request->input('lang');
        $langHeader = $langInput !== null ? (string) $langInput : 'it-IT';

        // Google Speech accetta BCP-47 (es. it-IT); se arriva solo "it", lo usiamo così.
        $languageCode = 'it-IT';
        $langHeader = trim($langHeader);
        if ($langHeader !== '') {
            $languageCode = $langHeader;
        }

        try {
            // Il client Google Speech usa GOOGLE_APPLICATION_CREDENTIALS
            // per trovare le credenziali JSON. Qui assumiamo che sia già configurato
            // nell'ambiente del server.
            /** @phpstan-ignore-next-line */
            $speechClient = new SpeechClient();

            $content = (string) file_get_contents($audio->getRealPath());

            /** @phpstan-ignore-next-line */
            $audioMessage = (new RecognitionAudio())
                ->setContent($content);

            // Lasciamo l'encoding "UNSPECIFIED" così che Google possa auto-rilevare
            // (utile per i webm/opus prodotti da MediaRecorder).
            /** @phpstan-ignore-next-line */
            $config = new RecognitionConfig([
                /** @phpstan-ignore-next-line */
                'encoding' => RecognitionConfig\AudioEncoding::ENCODING_UNSPECIFIED,
                'language_code' => $languageCode,
                'enable_automatic_punctuation' => true,
            ]);

            Log::info('GoogleSpeechTranscriptionController: ricevuto audio', [
                'original_name' => $audio->getClientOriginalName(),
                'mime' => $audio->getMimeType(),
                'size' => $audio->getSize(),
                'language_code' => $languageCode,
            ]);

            /** @phpstan-ignore-next-line */
            $response = $speechClient->recognize($config, $audioMessage);

            $textParts = [];
            /** @phpstan-ignore-next-line */
            foreach ($response->getResults() as $result) {
                /** @phpstan-ignore-next-line */
                $alternatives = $result->getAlternatives();
                if (isset($alternatives[0])) {
                    /** @phpstan-ignore-next-line */
                    $textParts[] = (string) $alternatives[0]->getTranscript();
                }
            }

            /** @phpstan-ignore-next-line */
            $speechClient->close();

            $text = trim(implode(' ', $textParts));

            return response()->json([
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::error('GoogleSpeechTranscriptionController.transcribe error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Errore trascrizione audio con Google Speech'], 500);
        }
    }
}
