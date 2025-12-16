<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiveAvatarSessionController extends Controller
{
    /**
     * Avvia una sessione LiveAvatar (token + start) usando API key SOLO server-side.
     *
     * POST /api/liveavatar/start
     * Body: { avatar_id: string, context_id: string, voice_id?: string|null, language?: string }
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'avatar_id' => ['required', 'string'],
            'context_id' => ['required', 'string'],
            'voice_id' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
        ]);

        $apiKey = (string) config('services.liveavatar.api_key');
        $serverUrl = (string) (config('services.liveavatar.server_url') ?: 'https://api.liveavatar.com');

        if ($apiKey === '') {
            return response()->json([
                'code' => 5000,
                'data' => null,
                'message' => 'LIVEAVATAR_API_KEY mancante in configurazione server.',
            ], 500);
        }

        $client = new Client([
            'base_uri' => rtrim($serverUrl, '/').'/',
            'headers' => [
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'http_errors' => false,
            'timeout' => 30,
        ]);

        $avatarPersona = [
            'context_id' => (string) $data['context_id'],
            'language' => (string) ($data['language'] ?: 'it'),
        ];
        if (! empty($data['voice_id'])) {
            $avatarPersona['voice_id'] = (string) $data['voice_id'];
        }

        $tokenPayload = [
            'mode' => 'FULL',
            'avatar_id' => (string) $data['avatar_id'],
            'avatar_persona' => $avatarPersona,
        ];

        try {
            $tokResp = $client->post('v1/sessions/token', ['json' => $tokenPayload]);
            $tokStatus = $tokResp->getStatusCode();
            $tokBody = (string) $tokResp->getBody();
            $tokJson = json_decode($tokBody, true);

            if ($tokStatus < 200 || $tokStatus >= 300) {
                Log::warning('LiveAvatar token failed', ['status' => $tokStatus, 'body' => $tokBody]);
                return response()->json([
                    'code' => 5001,
                    'data' => $tokJson ?: null,
                    'message' => 'LiveAvatar token failed',
                ], 502);
            }

            $sessionToken = (string) (($tokJson['session_token'] ?? $tokJson['data']['session_token'] ?? '') ?: '');
            $sessionId = (string) (($tokJson['session_id'] ?? $tokJson['data']['session_id'] ?? '') ?: '');

            if ($sessionToken === '') {
                return response()->json([
                    'code' => 5002,
                    'data' => $tokJson ?: null,
                    'message' => 'LiveAvatar token response missing session_token',
                ], 502);
            }

            // Start session (Bearer session_token)
            $startClient = new Client([
                'base_uri' => rtrim($serverUrl, '/').'/',
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$sessionToken,
                ],
                'http_errors' => false,
                'timeout' => 30,
            ]);

            $startResp = $startClient->post('v1/sessions/start');
            $startStatus = $startResp->getStatusCode();
            $startBody = (string) $startResp->getBody();
            $startJson = json_decode($startBody, true) ?: [];

            if ($startStatus < 200 || $startStatus >= 300) {
                Log::warning('LiveAvatar start failed', ['status' => $startStatus, 'body' => $startBody]);
                return response()->json([
                    'code' => 5003,
                    'data' => $startJson ?: null,
                    'message' => 'LiveAvatar start failed',
                ], 502);
            }

            // Normalizza: includiamo anche session_token per lo stop server-side
            $out = $startJson;
            if (! isset($out['data']) || ! is_array($out['data'])) {
                $out = ['code' => 1000, 'data' => (array) $startJson, 'message' => 'Session created successfully'];
            }
            $out['data']['session_token'] = $sessionToken;
            if ($sessionId !== '' && empty($out['data']['session_id'])) {
                $out['data']['session_id'] = $sessionId;
            }

            return response()->json($out);
        } catch (\Throwable $e) {
            Log::error('LiveAvatarSessionController.start error', ['error' => $e->getMessage()]);

            return response()->json([
                'code' => 5004,
                'data' => null,
                'message' => 'Errore server LiveAvatar',
            ], 500);
        }
    }

    /**
     * Stop session (server-side) usando Bearer session_token (NON API key).
     *
     * POST /api/liveavatar/stop { session_token: string }
     */
    public function stop(Request $request)
    {
        $data = $request->validate([
            'session_token' => ['required', 'string'],
        ]);

        $serverUrl = (string) (config('services.liveavatar.server_url') ?: 'https://api.liveavatar.com');
        $sessionToken = (string) $data['session_token'];

        try {
            $client = new Client([
                'base_uri' => rtrim($serverUrl, '/').'/',
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$sessionToken,
                ],
                'http_errors' => false,
                'timeout' => 30,
            ]);

            $resp = $client->post('v1/sessions/stop');
            $status = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            $json = json_decode($body, true);

            if ($status < 200 || $status >= 300) {
                Log::warning('LiveAvatar stop failed', ['status' => $status, 'body' => $body]);
                return response()->json([
                    'code' => 5006,
                    'data' => $json ?: null,
                    'message' => 'LiveAvatar stop failed',
                ], 502);
            }

            return response()->json($json ?: ['code' => 1000, 'data' => null, 'message' => 'Stopped']);
        } catch (\Throwable $e) {
            Log::error('LiveAvatarSessionController.stop error', ['error' => $e->getMessage()]);

            return response()->json([
                'code' => 5007,
                'data' => null,
                'message' => 'Errore server LiveAvatar stop',
            ], 500);
        }
    }
}

