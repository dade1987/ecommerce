<?php

declare(strict_types=1);

namespace App\Services\OpenAi;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use function Safe\json_decode;
use function Safe\preg_match;

class OpenAiLanguageDetector
{
    /**
     * @return array{language_code: string, language_name: string, confidence: float}
     */
    public function detect(string $text): array
    {
        $apiKey = config('services.openai.key');
        $apiKey = is_string($apiKey) ? trim($apiKey) : '';

        if ($apiKey === '') {
            // Fallback al key usato nel generatore SEO (config/openapi.php).
            $fallbackKey = config('openapi.key');
            $fallbackKey = is_string($fallbackKey) ? trim($fallbackKey) : '';
            if ($fallbackKey !== '') {
                $apiKey = $fallbackKey;
            }
        }

        if ($apiKey === '') {
            throw new \RuntimeException('OpenAI API key mancante (services.openai.key o openapi.key).');
        }

        $text = trim($text);
        if ($text === '') {
            return [
                'language_code' => 'it',
                'language_name' => 'Italian',
                'confidence' => 0.0,
            ];
        }

        $client = new Client();

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a language detector.\nReturn ONLY valid JSON with keys: language_code, language_name, confidence.\n- language_code: ISO 639-1 in lowercase (e.g. \"en\", \"it\", \"fr\").\n- language_name: English name (e.g. \"English\").\n- confidence: number between 0 and 1.\nIf the input is mixed, choose the primary language.\nNever add extra keys.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $text,
                    ],
                ],
            ],
            'http_errors' => false,
            'timeout' => 30,
        ]);

        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            Log::warning('OpenAiLanguageDetector: HTTP non-2xx', [
                'status' => $status,
                'body' => mb_substr($raw, 0, 2000),
            ]);
            throw new \RuntimeException('OpenAI language detection failed (HTTP '.$status.').');
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('OpenAI language detection: invalid response JSON.');
        }

        $choices = $decoded['choices'] ?? null;
        if (! is_array($choices) || ! isset($choices[0]) || ! is_array($choices[0])) {
            throw new \RuntimeException('OpenAI language detection: missing choices.');
        }

        $message = $choices[0]['message'] ?? null;
        if (! is_array($message)) {
            throw new \RuntimeException('OpenAI language detection: missing message.');
        }

        $content = $message['content'] ?? null;
        $content = is_string($content) ? trim($content) : '';
        if ($content === '') {
            throw new \RuntimeException('OpenAI language detection: empty content.');
        }

        $data = json_decode($content, true);
        if (! is_array($data)) {
            throw new \RuntimeException('OpenAI language detection: invalid JSON object.');
        }

        $code = isset($data['language_code']) ? (string) $data['language_code'] : '';
        $name = isset($data['language_name']) ? (string) $data['language_name'] : '';
        $confidence = $data['confidence'] ?? 0;

        $code = strtolower(trim($code));
        $name = trim($name);
        $confidence = is_numeric($confidence) ? (float) $confidence : 0.0;

        if ($code === '' || ! preg_match('/^[a-z]{2}$/', $code)) {
            $code = 'it';
        }
        if ($name === '') {
            $name = $code === 'it' ? 'Italian' : strtoupper($code);
        }
        if ($confidence < 0.0) {
            $confidence = 0.0;
        }
        if ($confidence > 1.0) {
            $confidence = 1.0;
        }

        return [
            'language_code' => $code,
            'language_name' => $name,
            'confidence' => $confidence,
        ];
    }
}
