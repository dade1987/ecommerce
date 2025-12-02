<?php

namespace Modules\WebScraper\Services\SearchStrategies;

/**
 * Strategy for contact information queries
 * Optimized for finding contact data (phone, email, address, P.IVA)
 */
class ContactSearchStrategy implements SearchStrategyInterface
{
    public function getMaxPages(): int
    {
        // Contact info is usually on homepage or /contatti page
        // No need to scrape 50 pages!
        return 3;
    }

    public function getMaxDepth(): int
    {
        // Stay shallow - contacts are usually at top level
        return 1;
    }

    public function matches(string $query): bool
    {
        $contactKeywords = [
            'contatti', 'contatto', 'contact', 'contacts',
            'telefono', 'tel', 'phone',
            'email', 'mail', 'e-mail',
            'indirizzo', 'address', 'dove siamo', 'sede',
            'p.iva', 'piva', 'partita iva',
        ];

        $queryLower = strtolower($query);

        foreach ($contactKeywords as $keyword) {
            if (stripos($queryLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getName(): string
    {
        return 'ContactSearch';
    }

    public function getPriorityUrls(string $baseUrl): array
    {
        $domain = parse_url($baseUrl, PHP_URL_SCHEME) . '://' . parse_url($baseUrl, PHP_URL_HOST);

        return [
            $baseUrl,  // Homepage first
            $domain . '/contatti',
            $domain . '/contact',
            $domain . '/chi-siamo',
            $domain . '/about',
        ];
    }

    public function shouldStopEarly(): bool
    {
        // Stop as soon as we find contact info
        return true;
    }

    public function getMinResultsForEarlyStop(): int
    {
        // Stop after finding 1 page with contact data
        return 1;
    }
}