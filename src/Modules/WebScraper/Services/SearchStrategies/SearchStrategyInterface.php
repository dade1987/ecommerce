<?php

namespace Modules\WebScraper\Services\SearchStrategies;

interface SearchStrategyInterface
{
    /**
     * Get maximum pages to scrape for this strategy
     */
    public function getMaxPages(): int;

    /**
     * Get maximum depth for recursive crawling
     */
    public function getMaxDepth(): int;

    /**
     * Check if this strategy applies to the given query
     */
    public function matches(string $query): bool;

    /**
     * Get strategy name for logging
     */
    public function getName(): string;

    /**
     * Get priority URLs to scrape first (optional)
     * Returns array of URLs or empty array
     */
    public function getPriorityUrls(string $baseUrl): array;

    /**
     * Should stop early if results found?
     */
    public function shouldStopEarly(): bool;

    /**
     * Minimum results before stopping early
     */
    public function getMinResultsForEarlyStop(): int;
}
