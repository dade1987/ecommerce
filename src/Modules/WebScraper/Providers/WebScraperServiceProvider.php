<?php

namespace Modules\WebScraper\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\WebScraper\Contracts\ScraperInterface;
use Modules\WebScraper\Contracts\ParserInterface;
use Modules\WebScraper\Services\WebScraperService;
use Modules\WebScraper\Services\HtmlParserService;
use Modules\WebScraper\Services\AiAnalyzerService;
use Modules\WebScraper\Services\IntelligentCrawlerService;
use Modules\WebScraper\Services\SitemapService;
use Modules\WebScraper\Services\SearchFormService;
use Modules\WebScraper\Services\HybridSearchService;
use Modules\WebScraper\Console\Commands\ClearExpiredCache;
use Modules\WebScraper\Console\Commands\IndexSiteCommand;
use Modules\WebScraper\Console\Commands\RagSearchCommand;
use Modules\WebScraper\Console\Commands\RagStatsCommand;
use Modules\WebScraper\Console\Commands\UpdateChunksDomain;
use Modules\WebScraper\Console\Commands\HybridSearchCommand;
use Modules\WebScraper\Console\Commands\PurgeDomainCommand;

class WebScraperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge module configurations
        $this->mergeConfigFrom(
            __DIR__.'/../config/webscraper.php',
            'webscraper'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../config/database.php',
            'webscraper.database'
        );

        // Register SQLite connection for WebScraper
        $this->registerDatabaseConnection();

        // Register contracts
        $this->app->singleton(ScraperInterface::class, WebScraperService::class);
        $this->app->singleton(ParserInterface::class, HtmlParserService::class);
        $this->app->singleton(AiAnalyzerService::class);
        $this->app->singleton(SitemapService::class);
        $this->app->singleton(SearchFormService::class);
        $this->app->singleton(IntelligentCrawlerService::class);
        $this->app->singleton(HybridSearchService::class);

        // Register facade accessor
        $this->app->singleton('webscraper', function ($app) {
            return $app->make(ScraperInterface::class);
        });
    }

    /**
     * Register the WebScraper SQLite database connection
     */
    protected function registerDatabaseConnection(): void
    {
        $connections = config('webscraper.database.connections', []);

        foreach ($connections as $name => $config) {
            config(["database.connections.{$name}" => $config]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearExpiredCache::class,
                IndexSiteCommand::class,
                RagSearchCommand::class,
                RagStatsCommand::class,
                UpdateChunksDomain::class,
                HybridSearchCommand::class,
                PurgeDomainCommand::class,
            ]);

            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/webscraper.php' => config_path('webscraper.php'),
            ], 'webscraper-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'webscraper-migrations');
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes if they exist
        if (file_exists(__DIR__.'/../routes/webscraper_routes.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/webscraper_routes.php');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ScraperInterface::class,
            ParserInterface::class,
            'webscraper',
        ];
    }
}