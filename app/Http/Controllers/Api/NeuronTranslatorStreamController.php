<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use App\Neuron\LiveTranslatorAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
use function Safe\json_encode;
use function Safe\ob_flush;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * NeuronTranslatorStreamController
 *
 * Controller SSE che usa l'Agent LiveTranslatorAgent per tradurre frasi brevi
 * riconosciute dal microfono, con streaming token-by-token.
 *
 * Endpoint:
 * GET /api/chatbot/translator-stream?text=...&source_lang=it-IT&locale=it
 */
class NeuronTranslatorStreamController extends Controller
{
    public function stream(Request $request)
    {
        $text = (string) $request->query('text', '');
        $sourceLang = (string) $request->query('source_lang', '');
        $locale = (string) $request->query('locale', 'it');
        $targetLang = (string) $request->query('target_lang', '');
        $threadId = (string) $request->query('thread_id', '');

        // Genera thread_id se non fornito
        if (empty($threadId)) {
            $threadId = 'translation_'.uniqid('', true);
        }

        $response = new StreamedResponse(function () use ($text, $sourceLang, $locale, $targetLang, $threadId) {
            $flush = function (array $payload, string $event = 'message') {
                echo "event: {$event}\n";
                echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
                @ob_flush();
                @flush();
            };

            $cleanText = trim($text);
            if ($cleanText === '') {
                $flush(['token' => ''], 'done');

                return;
            }

            Log::info('NeuronTranslatorStreamController.stream START', [
                'text_preview' => mb_substr($cleanText, 0, 120),
                'source_lang' => $sourceLang,
                'locale' => $locale,
                'target_lang' => $targetLang,
            ]);

            try {
                // Wrap the user text between guillemets so the model clearly
                // understands what must be translated and does not confuse it
                // with instructions. We keep the raw text for logging/saving.
                $wrappedText = '«'.$cleanText.'»';

                $agent = LiveTranslatorAgent::make()
                    ->withLocale($locale)
                    ->withTargetLang($targetLang ?: null);

                $fullContent = '';

                $stream = $agent->stream(
                    new UserMessage($wrappedText)
                );

                foreach ($stream as $chunk) {
                    if (is_string($chunk)) {
                        $fullContent .= $chunk;
                        $flush(['token' => $chunk]);
                    }
                }

                if ($fullContent === '') {
                    $fullContent = 'Traduzione non disponibile.';
                }

                // Salva la traduzione nella tabella quoters
                try {
                    Quoter::create([
                        'thread_id' => $threadId,
                        'role' => 'translation',
                        'content' => $cleanText,
                    ]);
                } catch (\Throwable $saveError) {
                    Log::warning('NeuronTranslatorStreamController: errore salvataggio traduzione', [
                        'error' => $saveError->getMessage(),
                    ]);
                }

                Log::info('NeuronTranslatorStreamController.stream DONE', [
                    'content_length' => strlen($fullContent),
                    'content_preview' => mb_substr($fullContent, 0, 160),
                    'thread_id' => $threadId,
                ]);

                $flush(['token' => ''], 'done');
            } catch (\Throwable $e) {
                Log::error('NeuronTranslatorStreamController error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $flush(['error' => 'Errore durante la traduzione: '.$e->getMessage()], 'error');
                $flush(['token' => ''], 'done');
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
