<?php

namespace App\Services\Speech;

use App\Contracts\SpeechTranscriptionProviderInterface;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use function Safe\fopen;
use function Safe\json_decode;

class OpenAiWhisperTranscriptionProvider implements SpeechTranscriptionProviderInterface
{
    public function transcribe(UploadedFile $audio, string $language): string
    {
        $apiKey = (string) config('openapi.key');
        if (trim($apiKey) === '' || $apiKey === 'invalid key') {
            throw new \RuntimeException('OpenAI API key mancante in configurazione (config: openapi.key).');
        }

        $baseUrl = (string) config('speech.transcription.openai.base_url', 'https://api.openai.com/v1/');
        $model = (string) config('speech.transcription.openai.model', 'whisper-1');
        $timeout = (int) config('speech.transcription.openai.timeout', 60);

        $client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
            ],
            'http_errors' => false,
            'timeout' => $timeout,
        ]);

        $originalName = $audio->getClientOriginalName() ?: 'audio.webm';
        $contentType = $this->guessContentType($originalName);

        $lastError = null;
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
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
                            'contents' => $model,
                        ],
                        [
                            'name' => 'temperature',
                            'contents' => '0',
                        ],
                        [
                            // Necessario per ottenere segments + metriche (avg_logprob, no_speech_prob, compression_ratio, ...)
                            'name' => 'response_format',
                            'contents' => 'verbose_json',
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
                        'body' => mb_substr($body, 0, 2000),
                    ]);

                    $decoded = json_decode($body, true);
                    $message = '';
                    if (is_array($decoded) && isset($decoded['error']['message'])) {
                        $message = (string) $decoded['error']['message'];
                    } else {
                        $message = mb_substr($body, 0, 500);
                    }

                    throw new \RuntimeException('Errore Whisper OpenAI: '.$message);
                }

                $json = json_decode($body, true);
                $text = (string) (($json['text'] ?? '') ?: '');
                $text = trim($text);

                if ($text === '') {
                    return '';
                }

                if (self::shouldRejectTranscriptionQuality(is_array($json) ? $json : [])) {
                    Log::warning('OpenAI Whisper transcription rejected by quality guard', [
                        'attempt' => $attempt,
                        'provider' => 'openai_whisper',
                        'language' => $language,
                        'preview' => mb_substr($text, 0, 160),
                    ]);
                    // Retry una sola volta
                    if ($attempt < 2) {
                        continue;
                    }
                    throw new \RuntimeException('TRANSCRIPTION_REJECTED: qualità bassa (avg_logprob/no_speech_prob/compression_ratio)');
                }

                return $text;
            } catch (\Throwable $e) {
                $lastError = $e;
                // Se non è l'ultimo tentativo, riprova (una sola volta).
                if ($attempt < 2) {
                    continue;
                }
                throw $e;
            }
        }

        throw new \RuntimeException('Errore Whisper OpenAI: '.$lastError?->getMessage());
    }

    /**
     * Heuristics/guard su output "verbose_json" di Whisper.
     * Se non rispetta soglie minime di qualità, la trascrizione viene scartata.
     *
     * Esempio segment:
     * - avg_logprob: vicino a 0 = alta confidenza, più negativo = peggio
     * - no_speech_prob: vicino a 1 = probabile non-speech
     * - compression_ratio: valori molto alti possono indicare ripetizioni/spazzatura
     */
    public static function shouldRejectTranscriptionQuality(array $json): bool
    {
        try {
            $segments = $json['segments'] ?? null;
            if (! is_array($segments) || $segments === []) {
                // Se non abbiamo metadati, non possiamo validare: non blocchiamo.
                return false;
            }

            $avgLogprobs = [];
            $noSpeechProbs = [];
            $compressionRatios = [];

            foreach ($segments as $seg) {
                if (! is_array($seg)) {
                    continue;
                }
                $segText = trim((string) ($seg['text'] ?? ''));
                if ($segText === '') {
                    continue;
                }
                if (isset($seg['avg_logprob']) && is_numeric($seg['avg_logprob'])) {
                    $avgLogprobs[] = (float) $seg['avg_logprob'];
                }
                if (isset($seg['no_speech_prob']) && is_numeric($seg['no_speech_prob'])) {
                    $noSpeechProbs[] = (float) $seg['no_speech_prob'];
                }
                if (isset($seg['compression_ratio']) && is_numeric($seg['compression_ratio'])) {
                    $compressionRatios[] = (float) $seg['compression_ratio'];
                }
            }

            // Se non abbiamo metriche utili, non blocchiamo.
            if ($avgLogprobs === [] && $noSpeechProbs === [] && $compressionRatios === []) {
                return false;
            }

            // Soglie conservative (standard pratici)
            $minAvgLogprobAllowed = -0.5;
            $maxNoSpeechProbAllowed = 0.6;
            $maxCompressionRatioAllowed = 2.4;

            // Regola 1: confidenza troppo bassa in almeno un segmento
            if ($avgLogprobs !== [] && min($avgLogprobs) < $minAvgLogprobAllowed) {
                return true;
            }

            // Regola 2: probabile non-speech troppo alta (media o picchi)
            if ($noSpeechProbs !== []) {
                $avgNoSpeech = array_sum($noSpeechProbs) / max(count($noSpeechProbs), 1);
                $maxNoSpeech = max($noSpeechProbs);
                if ($avgNoSpeech >= $maxNoSpeechProbAllowed || $maxNoSpeech >= 0.85) {
                    return true;
                }
            }

            // Regola 3: compressione anomala (spesso ripetizioni/spazzatura)
            if ($compressionRatios !== [] && max($compressionRatios) > $maxCompressionRatioAllowed) {
                return true;
            }

            return false;
        } catch (\Throwable) {
            // Fail-open: se la guard crasha, non blocchiamo la trascrizione.
            return false;
        }
    }

    private function guessContentType(string $originalName): string
    {
        $lowerName = strtolower($originalName);
        if (str_ends_with($lowerName, '.wav')) {
            return 'audio/wav';
        }
        if (str_ends_with($lowerName, '.ogg')) {
            return 'audio/ogg';
        }

        return 'audio/webm';
    }
}
