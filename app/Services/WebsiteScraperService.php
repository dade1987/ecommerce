<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Client as OpenAIClient;

class WebsiteScraperService
{
    private const CACHE_TTL = 604800; // 7 giorni in secondi
    private const CACHE_PREFIX = 'website_content:';
    private const MAX_CONTENT_LENGTH = 10000; // Limita il contenuto scrapato

    public function __construct(private OpenAIClient $client)
    {
    }

    /**
     * Scrapa il contenuto del sito web del Team e lo cachea
     */
    public function scrapeTeamWebsite(string $website, string $teamId): ?string
    {
        if (!$website || trim($website) === '') {
            return null;
        }

        $cacheKey = self::CACHE_PREFIX . md5($teamId . ':' . $website);

        // Prova cache
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::info('WebsiteScraperService: Contenuto da cache', ['teamId' => $teamId]);
            return $cached;
        }

        try {
            $content = $this->fetchAndCleanContent($website);

            if ($content && strlen($content) > 0) {
                // Cachea per 7 giorni
                Cache::put($cacheKey, $content, self::CACHE_TTL);
                Log::info('WebsiteScraperService: Scraping completato', [
                    'teamId' => $teamId,
                    'contentLength' => strlen($content),
                ]);

                return $content;
            }
        } catch (\Throwable $e) {
            Log::warning('WebsiteScraperService: Errore nel scraping', [
                'error' => $e->getMessage(),
                'website' => $website,
            ]);
        }

        return null;
    }

    /**
     * Scrapa il contenuto di una lista di siti web del Team e lo cachea
     */
    public function scrapeTeamWebsites(array $websites, string $teamId): ?string
    {
        if (empty($websites)) {
            return null;
        }

        $cacheKey = self::CACHE_PREFIX . md5($teamId . ':' . implode('|', $websites));

        // Prova cache
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::info('WebsiteScraperService: Contenuto lista da cache', ['teamId' => $teamId, 'count' => count($websites)]);
            return $cached;
        }

        $allContent = [];
        foreach ($websites as $website) {
            if (!$website || trim($website) === '') {
                continue;
            }

            try {
                $content = $this->fetchAndCleanContent($website);
                if ($content && strlen($content) > 0) {
                    $allContent[] = "=== Sito: {$website} ===\n{$content}";
                }
            } catch (\Throwable $e) {
                Log::warning('WebsiteScraperService: Errore nel scraping di un URL', [
                    'error' => $e->getMessage(),
                    'website' => $website,
                ]);
            }
        }

        if (empty($allContent)) {
            return null;
        }

        $combinedContent = implode("\n\n", $allContent);

        // Cachea per 7 giorni
        Cache::put($cacheKey, $combinedContent, self::CACHE_TTL);
        Log::info('WebsiteScraperService: Scraping lista completato', [
            'teamId' => $teamId,
            'urlsCount' => count($websites),
            'contentLength' => strlen($combinedContent),
        ]);

        return $combinedContent;
    }

    /**
     * Fetcha il contenuto HTML e lo pulisce
     */
    private function fetchAndCleanContent(string $website): ?string
    {
        try {
            $client = new Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => false,
            ]);

            $response = $client->get($website);
            $html = $response->getBody()->getContents();

            // Estrai testo dal HTML
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);

            // Rimuovi script e style
            foreach ($dom->getElementsByTagName('script') as $node) {
                $node->parentNode->removeChild($node);
            }
            foreach ($dom->getElementsByTagName('style') as $node) {
                $node->parentNode->removeChild($node);
            }

            $body = $dom->getElementsByTagName('body')->item(0);
            $plainText = $body ? strip_tags($dom->saveHTML($body)) : strip_tags($html);

            // Pulisci spazi multipli
            $plainText = preg_replace('/\s+/', ' ', $plainText);
            $plainText = trim($plainText);

            // Limita la lunghezza
            if (strlen($plainText) > self::MAX_CONTENT_LENGTH) {
                $plainText = mb_substr($plainText, 0, self::MAX_CONTENT_LENGTH) . '...';
            }

            return $plainText;
        } catch (\Throwable $e) {
            Log::error('WebsiteScraperService.fetchAndCleanContent', [
                'error' => $e->getMessage(),
                'website' => $website,
            ]);

            return null;
        }
    }

    /**
     * Analizza il contenuto del sito per estrarre punti chiave (usando GPT)
     */
    public function analyzeWebsiteContent(string $content, string $question, string $locale = 'it'): ?string
    {
        try {
            if (!$content || trim($content) === '') {
                return null;
            }

            $response = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Sei un AI Assistant che analizza i contenuti dei siti web. Rispondi in modo conciso e rilevante basandoti SOLO sul contenuto fornito.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Contenuto del sito web:\n{$content}\n\nDomanda: {$question}\n\nRispondi basandoti SOLO su questo contenuto. Se la risposta non è presente, rispondi che l'informazione non è disponibile nel sito.",
                    ],
                ],
                'temperature' => 0.5,
                'max_tokens' => 500,
            ]);

            return $response->choices[0]->message->content ?? null;
        } catch (\Throwable $e) {
            Log::warning('WebsiteScraperService.analyzeWebsiteContent', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Pulisce cache del sito web
     */
    public static function clearCache(string $teamId = null): void
    {
        if ($teamId) {
            Cache::forget(self::CACHE_PREFIX . $teamId);
        } else {
            Cache::flush(); // Pulisce tutto
        }
    }
}
