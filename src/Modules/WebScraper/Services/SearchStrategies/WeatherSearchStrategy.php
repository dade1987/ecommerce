<?php

namespace Modules\WebScraper\Services\SearchStrategies;

/**
 * Strategy for weather/meteo queries
 * Optimized for finding weather forecasts for specific cities
 */
class WeatherSearchStrategy implements SearchStrategyInterface
{
    public function getMaxPages(): int
    {
        // Weather info is usually on specific city pages
        // Try a few variations of the city name
        return 5;
    }

    public function getMaxDepth(): int
    {
        // Stay shallow - weather pages are usually at top level
        return 1;
    }

    public function matches(string $query): bool
    {
        $weatherKeywords = [
            'meteo', 'weather', 'previsioni', 'forecast', 'previsione',
            'temperatura', 'temperature', 'clima', 'climate',
            'pioggia', 'rain', 'sole', 'sun', 'neve', 'snow',
            'tempo', 'condizioni meteo', 'weather conditions',
        ];

        $queryLower = strtolower($query);

        foreach ($weatherKeywords as $keyword) {
            if (stripos($queryLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getName(): string
    {
        return 'WeatherSearch';
    }

    public function getPriorityUrls(string $baseUrl): array
    {
        // Extract city name from query if possible
        // This method will be called with the base URL, but we need the query
        // We'll construct URLs based on common weather site patterns

        $domain = parse_url($baseUrl, PHP_URL_SCHEME) . '://' . parse_url($baseUrl, PHP_URL_HOST);
        $host = parse_url($baseUrl, PHP_URL_HOST);

        $urls = [$baseUrl]; // Homepage first

        // Try to extract city from URL or use common patterns
        // For meteo.it specifically, cities are at /meteo/{city-name}
        if (stripos($host, 'meteo.it') !== false) {
            // Extract city from the context (we'll need to pass it somehow)
            // For now, return common Italian cities that might be requested
            $commonCities = [
                'bologna', 'roma', 'milano', 'torino', 'firenze',
                'napoli', 'venezia', 'genova', 'verona', 'palermo'
            ];

            // We can't know the exact city from here, but we return the pattern
            // The IntelligentCrawler will need to pass city info
            foreach ($commonCities as $city) {
                $urls[] = $domain . '/meteo/' . $city;
                $urls[] = $domain . '/previsioni/' . $city;
            }
        }

        // Generic weather site patterns
        $urls[] = $domain . '/meteo';
        $urls[] = $domain . '/previsioni';
        $urls[] = $domain . '/weather';
        $urls[] = $domain . '/forecast';

        return array_unique($urls);
    }

    public function shouldStopEarly(): bool
    {
        // Stop as soon as we find weather info
        return true;
    }

    public function getMinResultsForEarlyStop(): int
    {
        // Stop after finding 1 page with weather data
        return 1;
    }

    /**
     * Extract city name from query
     * Examples: "meteo Bologna" -> "bologna", "weather in Rome" -> "rome"
     */
    public function extractCityFromQuery(string $query): ?string
    {
        $queryLower = strtolower(trim($query));

        // Remove common weather keywords using word boundaries to avoid partial matches
        $weatherWords = ['meteo', 'weather', 'previsioni', 'forecast', 'previsione', 'di', 'in', 'per', 'of', 'at', 'for', 'il', 'la', 'del', 'della'];

        foreach ($weatherWords as $word) {
            // Use word boundary regex to match only complete words
            $queryLower = preg_replace('/\b' . preg_quote($word, '/') . '\b/', '', $queryLower);
        }

        // Clean up extra spaces
        $queryLower = preg_replace('/\s+/', ' ', $queryLower);
        $queryLower = trim($queryLower);

        // If we have a single word left, it's probably the city
        $parts = explode(' ', $queryLower);
        if (count($parts) === 1 && !empty($parts[0])) {
            return $parts[0];
        }

        // Return the last word as city name (most common pattern)
        return !empty($parts) ? end($parts) : null;
    }

    /**
     * Generate city-specific URLs for a given base URL
     */
    public function getCityUrls(string $baseUrl, string $city): array
    {
        $domain = parse_url($baseUrl, PHP_URL_SCHEME) . '://' . parse_url($baseUrl, PHP_URL_HOST);
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $cityLower = strtolower(trim($city));

        $urls = [];

        // meteo.it specific patterns
        if (stripos($host, 'meteo.it') !== false) {
            $urls[] = $domain . '/meteo/' . $cityLower;
            $urls[] = $domain . '/previsioni/' . $cityLower;
            $urls[] = $domain . '/meteo/italia/' . $cityLower;
            $urls[] = $domain . '/previsioni-meteo/' . $cityLower;
        }

        // ilmeteo.it patterns
        if (stripos($host, 'ilmeteo.it') !== false) {
            $urls[] = $domain . '/meteo/' . ucfirst($cityLower);
            $urls[] = $domain . '/previsioni/' . ucfirst($cityLower);
        }

        // Generic patterns for other weather sites
        $urls[] = $domain . '/' . $cityLower;
        $urls[] = $domain . '/city/' . $cityLower;
        $urls[] = $domain . '/location/' . $cityLower;

        return array_unique($urls);
    }
}