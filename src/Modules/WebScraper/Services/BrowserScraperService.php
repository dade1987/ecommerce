<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Browser-based scraper using Puppeteer (Node.js headless browser)
 * Used for sites with anti-bot protection like Amazon, eBay, etc.
 */
class BrowserScraperService
{
    protected string $scriptPath;
    protected int $timeout;

    public function __construct()
    {
        $this->scriptPath = base_path('scraper-headless-wrapper.sh');
        $this->timeout = config('webscraper.browser_scraping.timeout', 150); // 2.5 minutes default (for POW captcha)
    }

    /**
     * Scrape a URL using headless browser (Puppeteer)
     * Returns raw HTML content that can be parsed by HtmlParserService
     */
    public function scrape(string $url): array
    {
        Log::channel('webscraper')->info('BrowserScraper: Starting headless browser scrape', [
            'url' => $url,
        ]);

        if (!file_exists($this->scriptPath)) {
            Log::channel('webscraper')->error('BrowserScraper: Script not found', [
                'path' => $this->scriptPath,
            ]);
            return ['error' => 'Headless browser script not found'];
        }

        try {
            // Use wrapper bash script that sets environment variables
            // Explicitly use /bin/bash instead of /bin/sh (which is dash)
            $process = new Process([
                '/bin/bash',
                $this->scriptPath,
                $url
            ], base_path());

            $process->setTimeout($this->timeout);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();

            // Parse JSON output from Node.js script
            $result = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::channel('webscraper')->error('BrowserScraper: Invalid JSON from script', [
                    'url' => $url,
                    'output' => substr($output, 0, 500),
                    'error_output' => $errorOutput,
                ]);
                return ['error' => 'Invalid response from headless browser'];
            }

            if (!isset($result['success']) || !$result['success']) {
                Log::channel('webscraper')->error('BrowserScraper: Script returned error', [
                    'url' => $url,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
                return ['error' => $result['error'] ?? 'Browser scraping failed'];
            }

            Log::channel('webscraper')->info('BrowserScraper: Successfully scraped with headless browser', [
                'url' => $url,
                'final_url' => $result['final_url'] ?? $url,
                'html_length' => $result['html_length'] ?? 0,
                'title' => $result['title'] ?? 'N/A',
            ]);

            return [
                'success' => true,
                'url' => $result['url'] ?? $url,
                'final_url' => $result['final_url'] ?? $url,
                'title' => $result['title'] ?? '',
                'html' => $result['html'] ?? '',
                'timestamp' => $result['timestamp'] ?? now()->toIso8601String(),
            ];

        } catch (ProcessFailedException $e) {
            Log::channel('webscraper')->error('BrowserScraper: Process failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'output' => $e->getProcess()->getOutput(),
                'error_output' => $e->getProcess()->getErrorOutput(),
            ]);

            return ['error' => 'Failed to run headless browser: ' . $e->getMessage()];

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('BrowserScraper: Unexpected error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['error' => 'Browser scraping error: ' . $e->getMessage()];
        }
    }

    /**
     * Check if the URL should use browser-based scraping
     * Returns true for sites known to have anti-bot protection
     */
    public static function shouldUseBrowserScraping(string $url): bool
    {
        $browserScrapingDomains = [
            'amazon.',
            'ebay.',
            'walmart.',
            'target.',
            'bestbuy.',
            'isofin.it',
            // Add more domains as needed
        ];

        foreach ($browserScrapingDomains as $domain) {
            if (stripos($url, $domain) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if headless browser is available
     */
    public function isAvailable(): bool
    {
        if (!file_exists($this->scriptPath)) {
            return false;
        }

        // Check if node is available
        try {
            $process = new Process(['node', '--version']);
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            return false;
        }
    }
}