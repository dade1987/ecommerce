<?php

namespace Modules\WebScraper\Contracts;

interface ScraperInterface
{
    /**
     * Scrape a website and return structured data
     *
     * @param string $url The URL to scrape
     * @param array $options Scraping options
     * @return array Scraped data
     */
    public function scrape(string $url, array $options = []): array;

    /**
     * Get cached scraping result
     *
     * @param string $url
     * @return array|null
     */
    public function getCached(string $url): ?array;

    /**
     * Clear cache for a specific URL
     *
     * @param string $url
     * @return bool
     */
    public function clearCache(string $url): bool;
}