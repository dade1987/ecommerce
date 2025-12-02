<?php

namespace Modules\WebScraper\Contracts;

/**
 * SearchQueryEnhancer
 *
 * Contract for search query enhancement strategies.
 * Allows different enhancement methods to be applied to search queries.
 */
interface SearchQueryEnhancer
{
    /**
     * Enhance a search query with additional context
     *
     * @param string $query Original search query
     * @param array $context Context data for enhancement (e.g., domain, filters, etc.)
     * @return string Enhanced query
     */
    public function enhance(string $query, array $context = []): string;

    /**
     * Check if enhancement should be applied
     *
     * @param string $query Original search query
     * @param array $context Context data
     * @return bool True if enhancement should be applied
     */
    public function shouldEnhance(string $query, array $context = []): bool;
}