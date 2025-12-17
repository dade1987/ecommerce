<?php

namespace App\Http\Controllers\Api;

use App\Contracts\SpeechTranscriptionProviderInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Safe\preg_match;

class WhisperTranscriptionController extends Controller
{
    public function __construct(
        private readonly SpeechTranscriptionProviderInterface $provider
    ) {
    }

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
            $detectedMime = $audio->getMimeType();
            $providerName = (string) config('speech.transcription.provider', 'openai_whisper');

            Log::info('WhisperTranscriptionController: riceuto audio', [
                'original_name' => $audio->getClientOriginalName(),
                'mime' => $detectedMime,
                'size' => $audio->getSize(),
                'language' => $language,
                'provider' => $providerName,
            ]);

            $text = $this->provider->transcribe($audio, $language);

            return response()->json([
                'text' => $text,
            ]);
        } catch (\RuntimeException $e) {
            // Quality-guard: trascrizione scartata -> 422 (non 500) per evitare che il client la usi.
            if (str_starts_with($e->getMessage(), 'TRANSCRIPTION_REJECTED:')) {
                Log::warning('WhisperTranscriptionController: transcription rejected', [
                    'message' => $e->getMessage(),
                    'language' => $language,
                    'provider' => (string) config('speech.transcription.provider', 'openai_whisper'),
                ]);

                return response()->json([
                    'error' => 'transcription_rejected',
                    'message' => $e->getMessage(),
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            Log::error('WhisperTranscriptionController.transcribe error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Errore trascrizione audio',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
