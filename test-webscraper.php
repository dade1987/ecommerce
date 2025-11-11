<?php

/**
 * Quick test script for WebScraper module
 * Run with: php test-webscraper.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\WebScraper\Facades\WebScraper;
use Modules\WebScraper\Services\AiAnalyzerService;

echo "=== WebScraper Module Test ===\n\n";

// Test 1: Basic scraping
echo "Test 1: Scraping example.com\n";
echo str_repeat("-", 50) . "\n";

try {
    $result = WebScraper::scrape('https://example.com');

    if (isset($result['error'])) {
        echo "❌ Error: {$result['error']}\n";
    } else {
        echo "✅ Scraping successful!\n";
        echo "URL: {$result['url']}\n";
        echo "Title: " . ($result['metadata']['title'] ?? 'N/A') . "\n";
        echo "Content length: " . strlen($result['content']['main']) . " chars\n";
        echo "Headings found: " . count($result['content']['headings']) . "\n";
        echo "Links found: " . count($result['links']) . "\n";
        echo "Images found: " . count($result['images']) . "\n";
        echo "\nFirst 200 chars of content:\n";
        echo substr($result['content']['main'], 0, 200) . "...\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

echo "\n\n";

// Test 2: Cache test
echo "Test 2: Testing cache\n";
echo str_repeat("-", 50) . "\n";

try {
    $cached = WebScraper::getCached('https://example.com');
    if ($cached) {
        echo "✅ Cache hit! Data from: {$cached['scraped_at']}\n";
    } else {
        echo "ℹ️  No cached data found\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
}

echo "\n\n";

// Test 3: AI Analysis (only if scraping succeeded)
echo "Test 3: AI Analysis\n";
echo str_repeat("-", 50) . "\n";

try {
    $scrapedData = WebScraper::scrape('https://example.com');

    if (!isset($scrapedData['error'])) {
        echo "ℹ️  Analyzing with GPT-4o (this may take a few seconds)...\n";

        $analyzer = app(AiAnalyzerService::class);
        $analysis = $analyzer->summarize($scrapedData);

        if (isset($analysis['error'])) {
            echo "❌ Analysis error: {$analysis['error']}\n";
        } else {
            echo "✅ Analysis successful!\n";
            echo "Tokens used: {$analysis['usage']['total_tokens']}\n";
            echo "\nAnalysis:\n";
            echo $analysis['analysis'] . "\n";
        }
    } else {
        echo "⏭️  Skipping (scraping failed)\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

echo "\n\n=== Test Complete ===\n";