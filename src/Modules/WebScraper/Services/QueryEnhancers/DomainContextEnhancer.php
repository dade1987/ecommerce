<?php

namespace Modules\WebScraper\Services\QueryEnhancers;

use Modules\WebScraper\Contracts\SearchQueryEnhancer;
use Illuminate\Support\Facades\Log;

/**
 * DomainContextEnhancer
 *
 * Enhances search queries by adding company name extracted from domain.
 * This improves semantic relevance and keyword matching for domain-filtered searches.
 *
 * Example:
 * - Query: "contatti"
 * - Domain: "isofin.it"
 * - Enhanced: "contatti isofin"
 */
class DomainContextEnhancer implements SearchQueryEnhancer
{
    /**
     * Enhance query by adding company name from domain
     *
     * @param string $query Original search query
     * @param array $context Must contain 'domain' key
     * @return string Enhanced query
     */
    public function enhance(string $query, array $context = []): string
    {
        if (!$this->shouldEnhance($query, $context)) {
            return $query;
        }

        $domain = $context['domain'];
        $companyName = $this->extractCompanyName($domain);

        $enhancedQuery = $query . ' ' . $companyName;

        Log::channel('webscraper')->debug('DomainContextEnhancer: Query enhanced', [
            'original_query' => $query,
            'enhanced_query' => $enhancedQuery,
            'domain' => $domain,
            'company_name' => $companyName,
        ]);

        return $enhancedQuery;
    }

    /**
     * Check if enhancement should be applied
     *
     * Enhancement is applied only if:
     * 1. Domain is provided in context
     * 2. Query does NOT already contain the company name
     *
     * @param string $query Original search query
     * @param array $context Context data with 'domain' key
     * @return bool True if enhancement should be applied
     */
    public function shouldEnhance(string $query, array $context = []): bool
    {
        // Domain must be provided
        if (empty($context['domain'])) {
            return false;
        }

        $domain = $context['domain'];
        $companyName = $this->extractCompanyName($domain);

        // Don't enhance if query already contains company name (case-insensitive)
        if (stripos($query, $companyName) !== false) {
            Log::channel('webscraper')->debug('DomainContextEnhancer: Query already contains company name, skipping', [
                'query' => $query,
                'company_name' => $companyName,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Extract company name from domain
     *
     * Removes:
     * - www. prefix
     * - TLD (.it, .com, .org, etc.)
     *
     * @param string $domain Domain (e.g., "www.isofin.it")
     * @return string Company name (e.g., "isofin")
     */
    protected function extractCompanyName(string $domain): string
    {
        // Remove www. prefix
        $companyName = preg_replace('/^www\./', '', $domain);

        // Remove TLD (.it, .com, .co.uk, etc.)
        $companyName = preg_replace('/\.\w+$/', '', $companyName);

        return $companyName;
    }
}
