<?php

namespace App\Services\Speech;

use App\Contracts\SpeechTranscriptionProviderInterface;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use function Safe\fopen;
use function Safe\json_decode;

class GroqWhisperTranscriptionProvider implements SpeechTranscriptionProviderInterface
{
    public function transcribe(UploadedFile $audio, string $language): string
    {
        $apiKey = (string) config('services.groq.key');
        if (trim($apiKey) === '') {
            throw new \RuntimeException('GROQ_API_KEY mancante in configurazione (config: services.groq.key).');
        }

        $baseUrl = (string) (config('speech.transcription.groq.base_url')
            ?: config('services.groq.base_uri', 'https://api.groq.com/openai/v1'));
        $baseUrl = rtrim($baseUrl, '/').'/';

        $model = (string) config('speech.transcription.groq.model', 'whisper-large-v3-turbo');
        $timeout = (int) config('speech.transcription.groq.timeout', 60);

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
            Log::error('Groq Whisper error', [
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

            throw new \RuntimeException('Errore Whisper Groq: '.$message);
        }

        $json = json_decode($body, true);

        return (string) ($json['text'] ?? '');
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
