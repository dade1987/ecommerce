<?php

namespace Modules\WebScraper\Services\SearchStrategies;

/**
 * Default strategy for general queries (services, products, etc.)
 * More comprehensive search across the site
 */
class DefaultSearchStrategy implements SearchStrategyInterface
{
    public function getMaxPages(): int
    {
        // Use configured max_pages from config
        return config('webscraper.scraping.max_pages', 10);
    }

    public function getMaxDepth(): int
    {
        // Deep crawling for complex queries
        return 3;
    }

    public function matches(string $query): bool
    {
        // This is the fallback strategy - always matches
        return true;
    }

    public function getName(): string
    {
        return 'DefaultSearch';
    }

    public function getPriorityUrls(string $baseUrl): array
    {
        // No specific priority URLs, let strategies discover them
        return [];
    }

    public function shouldStopEarly(): bool
    {
        // Don't stop early - search thoroughly
        return false;
    }

    public function getMinResultsForEarlyStop(): int
    {
        return 0; // Not used since shouldStopEarly = false
    }
}