<?php

/**
 * EXAMPLE: How to integrate the new WebScraper module into ChatbotController
 *
 * This file shows how to replace the old scrapeSite() method with the new WebScraper module.
 * The new implementation provides:
 * - Intelligent HTML parsing with boilerplate removal
 * - Structured data extraction (metadata, headings, links, images)
 * - Automatic caching with configurable TTL
 * - AI analysis with multiple analysis types
 * - Better error handling and logging
 */

namespace Modules\WebScraper\Examples;

use Modules\WebScraper\Facades\WebScraper;
use Modules\WebScraper\Services\AiAnalyzerService;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class ChatbotIntegrationExample
{
    /**
     * OLD METHOD (from ChatbotController.php:704-775)
     * Problems:
     * - Only scrapes homepage
     * - Uses simple strip_tags() losing structure
     * - Limited to 4000 characters
     * - No cache
     * - Doesn't remove boilerplate (header, footer, nav)
     * - Makes separate GPT call for analysis
     */
    private function scrapeSite_OLD(?string $userUuid)
    {
        if (!$userUuid) {
            return ['error' => "Nessun UUID fornito per l'utente/attività."];
        }

        $customer = Customer::where('uuid', $userUuid)->first();
        if (!$customer || !$customer->website) {
            return ['error' => 'Nessun sito web specificato per questo utente.'];
        }

        // Old way: manual Guzzle + strip_tags
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($customer->website);
            $html = $response->getBody()->getContents();

            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $body = $dom->getElementsByTagName('body')->item(0);
            $plainText = strip_tags($dom->saveHTML($body));

            // Manual GPT call for analysis
            // ... (lines 743-768)

            return [
                'site_content' => mb_substr($plainText, 0, 4000).'...',
                'ai_analysis'  => 'Analysis here...',
            ];
        } catch (\Exception $e) {
            return ['error' => 'Impossibile recuperare il contenuto del sito.'];
        }
    }

    /**
     * NEW METHOD - Using WebScraper Module
     * Benefits:
     * - Intelligent parsing with boilerplate removal
     * - Structured data extraction
     * - Automatic caching (24 hours default)
     * - Domain blocking/whitelisting
     * - Better content extraction (finds main content containers)
     * - Integrated AI analysis with configurable prompts
     * - Comprehensive error handling
     */
    private function scrapeSite_NEW(?string $userUuid)
    {
        Log::info('scrapeSite: Inizio recupero Customer da uuid', ['userUuid' => $userUuid]);

        if (!$userUuid) {
            return ['error' => "Nessun UUID fornito per l'utente/attività."];
        }

        $customer = Customer::where('uuid', $userUuid)->first();
        if (!$customer) {
            Log::warning('scrapeSite: Nessun customer trovato', ['userUuid' => $userUuid]);
            return ['error' => 'Nessun cliente trovato per l\'UUID fornito.'];
        }

        if (!$customer->website) {
            Log::warning('scrapeSite: Nessun sito web associato a questo customer', ['userUuid' => $userUuid]);
            return ['error' => 'Nessun sito web specificato per questo utente.'];
        }

        try {
            // Step 1: Scrape the website with intelligent parsing
            $scrapedData = WebScraper::scrape($customer->website);

            if (isset($scrapedData['error'])) {
                Log::error('scrapeSite: Errore nello scraping', ['error' => $scrapedData['error']]);
                return ['error' => 'Impossibile recuperare il contenuto del sito.'];
            }

            // Step 2: Perform AI analysis on the scraped content
            $analyzer = app(AiAnalyzerService::class);
            $analysis = $analyzer->analyzeBusinessInfo($scrapedData);

            if (isset($analysis['error'])) {
                Log::error('scrapeSite: Errore durante l\'analisi AI', ['error' => $analysis['error']]);
                return ['error' => 'Impossibile generare un riepilogo. Errore AI.'];
            }

            Log::info('scrapeSite: Scraping e analisi completati', [
                'url' => $customer->website,
                'content_length' => strlen($scrapedData['content']['main']),
                'tokens_used' => $analysis['usage']['total_tokens'] ?? 0,
            ]);

            // Return structured data
            return [
                'site_content' => $scrapedData['content']['main'],
                'ai_analysis' => $analysis['analysis'],
                'metadata' => $scrapedData['metadata'],
                'scraped_at' => $scrapedData['scraped_at'],
                'analyzed_at' => $analysis['analyzed_at'],
            ];

        } catch (\Exception $e) {
            Log::error('scrapeSite: Errore imprevisto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Si è verificato un errore durante l\'elaborazione.'];
        }
    }

    /**
     * USAGE EXAMPLES
     */
    public function examples()
    {
        // Example 1: Basic scraping with facade
        $result = WebScraper::scrape('https://example.com');

        // Example 2: Check cache first
        $cached = WebScraper::getCached('https://example.com');
        if (!$cached) {
            $result = WebScraper::scrape('https://example.com');
        }

        // Example 3: Clear cache to force fresh scrape
        WebScraper::clearCache('https://example.com');
        $result = WebScraper::scrape('https://example.com');

        // Example 4: Different analysis types
        $analyzer = app(AiAnalyzerService::class);

        // Business analysis (sectors, products, target market, contacts)
        $businessAnalysis = $analyzer->analyzeBusinessInfo($result);

        // Extract specific info (contacts, prices, business info)
        $keyInfo = $analyzer->extractKeyInfo($result);

        // Quick summary
        $summary = $analyzer->summarize($result);

        // Example 5: Direct dependency injection in controller
        public function __construct(
            protected AiAnalyzerService $aiAnalyzer
        ) {
            // Now available as $this->aiAnalyzer
        }
    }

    /**
     * MIGRATION STEPS
     *
     * 1. In ChatbotController.php, add at the top:
     *    use Modules\WebScraper\Facades\WebScraper;
     *    use Modules\WebScraper\Services\AiAnalyzerService;
     *
     * 2. Replace the entire scrapeSite() method (lines 704-775) with scrapeSite_NEW()
     *
     * 3. Update the function calling tool definition if needed to reflect new return structure
     *
     * 4. Test with a real customer UUID:
     *    - Verify caching works (check logs for "Using cached data")
     *    - Verify AI analysis returns meaningful insights
     *    - Check that metadata is properly extracted
     *
     * 5. Optional: Configure in config/webscraper.php:
     *    - Cache TTL (default: 86400 = 24 hours)
     *    - Max content length (default: 50000 chars)
     *    - Blocked/allowed domains
     *    - HTTP timeouts
     *    - AI analysis model and tokens
     */
}