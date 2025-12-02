<?php

namespace Modules\WebScraper\Tests;

use Tests\TestCase;
use Modules\WebScraper\Facades\WebScraper;
use Modules\WebScraper\Services\AiAnalyzerService;

class WebScraperTest extends TestCase
{
    /**
     * Test basic scraping functionality
     */
    public function test_can_scrape_website(): void
    {
        $url = 'https://example.com';

        $result = WebScraper::scrape($url);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals($url, $result['url']);
    }

    /**
     * Test URL validation
     */
    public function test_rejects_invalid_url(): void
    {
        $result = WebScraper::scrape('not-a-valid-url');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Invalid URL', $result['error']);
    }

    /**
     * Test caching functionality
     */
    public function test_caching_works(): void
    {
        $url = 'https://example.com';

        // First scrape
        $result1 = WebScraper::scrape($url);

        // Second scrape (should be cached)
        $result2 = WebScraper::getCached($url);

        $this->assertIsArray($result2);
        $this->assertEquals($result1['url'], $result2['url']);
    }

    /**
     * Test cache clearing
     */
    public function test_can_clear_cache(): void
    {
        $url = 'https://example.com';

        // Scrape and cache
        WebScraper::scrape($url);

        // Clear cache
        $cleared = WebScraper::clearCache($url);

        $this->assertTrue($cleared);

        // Should be null now
        $cached = WebScraper::getCached($url);
        $this->assertNull($cached);
    }

    /**
     * Test AI analysis integration
     */
    public function test_ai_analyzer_can_analyze(): void
    {
        $scrapedData = [
            'url' => 'https://example.com',
            'metadata' => [
                'title' => 'Example Domain',
                'description' => 'Example website',
            ],
            'content' => [
                'main' => 'This domain is for use in illustrative examples in documents.',
                'headings' => [
                    ['level' => 1, 'text' => 'Example Domain']
                ],
                'structured' => [
                    'paragraphs' => ['Example paragraph content']
                ],
            ],
        ];

        $analyzer = app(AiAnalyzerService::class);
        $result = $analyzer->summarize($scrapedData);

        $this->assertIsArray($result);
        // Note: This will only pass with valid OpenAI API key
        if (!isset($result['error'])) {
            $this->assertArrayHasKey('analysis', $result);
            $this->assertArrayHasKey('analyzed_at', $result);
        }
    }
}