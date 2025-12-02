<?php

namespace Modules\WebScraper\Services\SearchStrategies;

/**
 * Selects the appropriate search strategy based on query
 */
class SearchStrategySelector
{
    protected array $strategies = [];

    public function __construct()
    {
        // Register strategies in priority order
        $this->strategies = [
            new ContactSearchStrategy(),
            new WeatherSearchStrategy(),
            // Add more strategies here in the future:
            // new ProductSearchStrategy(),
            // new ServiceSearchStrategy(),
            // new PricingSearchStrategy(),
            new DefaultSearchStrategy(), // Fallback - always last
        ];
    }

    /**
     * Select the best strategy for the given query
     */
    public function selectStrategy(string $query): SearchStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->matches($query)) {
                return $strategy;
            }
        }

        // Should never reach here since DefaultSearchStrategy always matches
        return new DefaultSearchStrategy();
    }
}