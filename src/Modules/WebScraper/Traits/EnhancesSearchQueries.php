<?php

namespace Modules\WebScraper\Traits;

use Modules\WebScraper\Contracts\SearchQueryEnhancer;

/**
 * EnhancesSearchQueries
 *
 * Trait for services that need to enhance search queries using strategies.
 * Provides a unified way to apply query enhancement across different search services.
 */
trait EnhancesSearchQueries
{
    /**
     * Query enhancement strategy
     *
     * @var SearchQueryEnhancer|null
     */
    protected ?SearchQueryEnhancer $queryEnhancer = null;

    /**
     * Set the query enhancement strategy
     *
     * @param SearchQueryEnhancer $enhancer Enhancement strategy
     * @return self
     */
    public function setQueryEnhancer(SearchQueryEnhancer $enhancer): self
    {
        $this->queryEnhancer = $enhancer;
        return $this;
    }

    /**
     * Get the query enhancement strategy
     *
     * @return SearchQueryEnhancer|null
     */
    public function getQueryEnhancer(): ?SearchQueryEnhancer
    {
        return $this->queryEnhancer;
    }

    /**
     * Enhance a search query using the configured strategy
     *
     * @param string $query Original search query
     * @param array $context Context data for enhancement
     * @return string Enhanced query (or original if no enhancer set)
     */
    protected function enhanceQuery(string $query, array $context = []): string
    {
        if (!$this->queryEnhancer) {
            return $query;
        }

        return $this->queryEnhancer->enhance($query, $context);
    }

    /**
     * Check if query enhancement is enabled and should be applied
     *
     * @param string $query Original search query
     * @param array $context Context data
     * @return bool True if enhancement should be applied
     */
    protected function shouldEnhanceQuery(string $query, array $context = []): bool
    {
        if (!$this->queryEnhancer) {
            return false;
        }

        return $this->queryEnhancer->shouldEnhance($query, $context);
    }
}