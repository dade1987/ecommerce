<?php

namespace Modules\WebScraper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array scrape(string $url, array $options = [])
 * @method static array|null getCached(string $url)
 * @method static bool clearCache(string $url)
 *
 * @see \Modules\WebScraper\Services\WebScraperService
 */
class WebScraper extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'webscraper';
    }
}