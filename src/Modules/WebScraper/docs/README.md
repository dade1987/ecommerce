# WebScraper Module - Documentazione

## 📋 Panoramica

Modulo Laravel per scraping intelligente di siti web con supporto per:
- **Multi-strategia crawling** (sitemap, robots.txt, link following)
- **Browser headless** (Playwright/Chromium) per contenuti JavaScript
- **AI analysis** (GPT-4o) per estrazione informazioni
- **Caching SQLite** per performance
- **Anti-bot detection** avanzato

## 📚 Documentazione

### Setup e Configurazione

- **[Playwright Setup](PLAYWRIGHT_SETUP.md)** - Installazione e configurazione browser headless
  - Requisiti sistema
  - Installazione Docker
  - Troubleshooting
  - Best practices

### Architettura

```
Modules/WebScraper/
├── Contracts/
│   ├── ScraperInterface.php      # Interface per scraper
│   └── ParserInterface.php       # Interface per HTML parser
├── Services/
│   ├── WebScraperService.php     # Orchestrazione principale
│   ├── BrowserScraperService.php # Playwright headless browser
│   ├── IntelligentCrawlerService.php # Crawling multi-strategia
│   ├── HtmlParserService.php     # Parsing HTML/DOM
│   └── AiAnalyzerService.php     # Analisi AI con GPT-4o
├── Models/
│   ├── ScrapedPage.php           # Cache pagine scrapate
│   └── SearchResultCache.php     # Cache risultati ricerca
├── Facades/
│   └── WebScraper.php            # Facade Laravel
├── Providers/
│   └── WebScraperServiceProvider.php
└── database/migrations/          # Migrazioni SQLite
```

## 🚀 Quick Start

### 1. Installazione Docker

Il Dockerfile è già configurato per installare automaticamente Playwright:

```bash
# Build immagine PHP con Playwright
cd docker-dev
docker-compose build php

# Avvia containers
docker-compose up -d
```

Durante il build, Docker:
1. ✅ Installa Node.js 22
2. ✅ Installa dipendenze sistema Chromium
3. ✅ Crea directory `/var/www/.cache/`
4. ✅ Installa npm packages (include Playwright)
5. ✅ Scarica browser Chromium come `www-data`

### 2. Verifica Installazione

```bash
# Verifica Chromium installato
docker exec php_fpm_avatar-3d-v1-dev \
    ls -la /var/www/.cache/ms-playwright/chromium-*/chrome-linux/chrome

# Test scraping
docker exec -u www-data php_fpm_avatar-3d-v1-dev \
    bash /var/www/html/scraper-headless-wrapper.sh "https://www.example.com"
```

### 3. Migrazioni Database

Il modulo usa SQLite per cache:

```bash
# Esegui migrazioni WebScraper
docker exec php_fpm_avatar-3d-v1-dev \
    php artisan migrate --path=src/Modules/WebScraper/database/migrations --database=webscraper
```

## 💻 Utilizzo

### Facade (Raccomandato)

```php
use Modules\WebScraper\Facades\WebScraper;

// Scraping singola pagina
$result = WebScraper::scrape('https://example.com');

// Scraping con opzioni
$result = WebScraper::scrape('https://example.com', [
    'query' => 'contatti telefono email' // Include footer se query contiene parole chiave contatti
]);

// Crawling multi-pagina
$results = WebScraper::crawlSite('https://example.com', maxPages: 10);

// Cache
$cached = WebScraper::getCached('https://example.com');
WebScraper::clearCache('https://example.com');
```

### Service Injection

```php
use Modules\WebScraper\Contracts\ScraperInterface;

class MyController extends Controller
{
    public function __construct(
        private ScraperInterface $scraper
    ) {}

    public function scrape(Request $request)
    {
        $result = $this->scraper->scrape($request->input('url'));
        return response()->json($result);
    }
}
```

### Direct Instantiation

```php
use Modules\WebScraper\Services\WebScraperService;
use Modules\WebScraper\Services\HtmlParserService;

$parser = new HtmlParserService();
$scraper = new WebScraperService($parser);

$result = $scraper->scrape('https://example.com');
```

## 🎯 Risultati Scraping

### Struttura Response

```php
[
    'url' => 'https://example.com',
    'scraped_at' => '2025-11-15T23:00:00Z',

    'metadata' => [
        'title' => 'Example Domain',
        'description' => 'Example site description',
        'keywords' => 'example, domain',
        'og_title' => 'Example Domain',
        'og_image' => 'https://example.com/image.jpg',
    ],

    'content' => [
        'main' => 'Clean main content...',       // Pulito per AI (no header/footer)
        'full' => 'Full page text...',           // Tutto il testo (include footer)
        'headings' => [                          // Struttura heading
            ['level' => 'h1', 'text' => 'Main Title'],
            ['level' => 'h2', 'text' => 'Section'],
        ],
        'structured' => [                        // Contenuti strutturati
            ['type' => 'article', 'content' => '...'],
        ],
    ],

    'links' => [
        ['url' => 'https://example.com/page', 'text' => 'Link Text', 'type' => 'internal'],
    ],

    'images' => [
        ['url' => 'https://example.com/img.jpg', 'alt' => 'Image Alt'],
    ],

    'raw_html' => '<!DOCTYPE html>...',
    'raw_html_length' => 12345,
    'content_truncated' => false,
]
```

### Query-Based Content Selection

Il parametro `query` determina quale contenuto usare:

```php
// Query generica → usa 'main' content (pulito, no footer)
$result = WebScraper::scrape('https://example.com', ['query' => 'servizi prodotti']);
// $result['content']['main'] contiene solo contenuto principale

// Query con parole chiave contatti → usa 'full' content (include footer)
$result = WebScraper::scrape('https://example.com', ['query' => 'contatti telefono']);
// $result['content']['main'] contiene TUTTO il testo (footer incluso)
```

**Parole chiave che attivano footer**:
- contatt*, telefon*, email, indirizzo, address, phone
- contact, dove, where, ubicazione, location, sede
- mail, chiamare, call, scrivere, write
- orari, hours, apertura, opening, chiusura, closing

## 🔧 Configurazione

### File: `config/webscraper.php`

```php
return [
    'scraping' => [
        'timeout' => 60,                    // HTTP timeout (secondi)
        'max_redirects' => 5,
        'delay' => 1000,                    // Delay tra richieste (ms)
        'max_pages' => 10,                  // Max pagine per crawl
        'user_agents' => [                  // Rotazione user agents
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36...',
            // ... altri user agents
        ],
    ],

    'browser_scraping' => [
        'timeout' => 150,                   // Browser timeout (secondi)
        'enabled' => true,                  // Abilita browser scraping
    ],

    'parsing' => [
        'max_content_length' => 50000,      // Max lunghezza contenuto
        'remove_boilerplate' => true,       // Rimuovi boilerplate (nav, footer, etc.)
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 86400,                     // 24 ore
    ],

    'database' => [
        'connection' => 'webscraper',
        'cache_ttl' => 86400,
    ],

    'ai_analysis' => [
        'enabled' => true,
        'model' => 'gpt-4o',
        'max_tokens' => 4096,
    ],

    'blocked_domains' => [],
    'allowed_domains' => [],
];
```

### File: `.env.playwright`

```bash
PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright
```

## 🤖 AI Analysis

Il modulo include analisi AI per estrarre informazioni specifiche:

```php
use Modules\WebScraper\Services\AiAnalyzerService;

$analyzer = new AiAnalyzerService();

// Analisi generica
$analysis = $analyzer->analyze($scrapedContent, 'Quali servizi offre questa azienda?');

// Estrazione contatti
$contacts = $analyzer->extractContactInfo($scrapedContent);
// Ritorna: telefono, email, indirizzo, orari

// Estrazione menu
$menu = $analyzer->extractMenuStructure($rawHtml);
// Ritorna: categorie, link, gerarchia

// Ricerca keyword
$results = $analyzer->searchContent($scrapedContent, 'servizi fotovoltaico');
// Ritorna: snippet rilevanti, ranking
```

## 📊 Caching e Performance

### SQLite Cache

Due tabelle:
- `scraped_pages` - Cache pagine complete (TTL 24h)
- `search_result_caches` - Cache risultati ricerca (TTL 1h)

### Performance Tips

1. **Cache management**:
```php
// Clear cache specifico
WebScraper::clearCache('https://example.com');

// Clear tutto
ScrapedPage::truncate();
SearchResultCache::truncate();
```

2. **Batch operations**:
```php
// Crawl limitato
$results = WebScraper::crawlSite('https://example.com', maxPages: 5);
```

3. **Delay tra richieste**:
```php
// config/webscraper.php
'delay' => 1000, // 1 secondo tra richieste
```

## 🔐 Sicurezza

### Domain Filtering

```php
// config/webscraper.php
'blocked_domains' => [
    'malicious-site.com',
],

'allowed_domains' => [
    // Se popolato, solo questi domini sono permessi
    'trusted-site.com',
],
```

### Rate Limiting

Il crawler rispetta automaticamente:
- `robots.txt` (se presente)
- Delay configurato tra richieste
- Max pagine per sessione

### Anti-Detection

Browser scraping include:
- User-Agent realistici
- Viewport randomizzato
- Cookies e storage abilitati
- Canvas/WebGL fingerprinting simulation

## 🐛 Debugging

### Log Channel

I log vanno in `storage/logs/webscraper-{date}.log`:

```php
// Enable in config/logging.php
'channels' => [
    'webscraper' => [
        'driver' => 'daily',
        'path' => storage_path('logs/webscraper.log'),
        'level' => 'debug',
        'days' => 14,
    ],
],
```

### Livelli Log

- **DEBUG**: Dettagli richieste HTTP, parser output
- **INFO**: Scraping completato, cache hit/miss
- **WARNING**: Fallback a HTTP, retry
- **ERROR**: Errori HTTP, parser fail, AI errors

### Tail Logs

```bash
# Real-time logs
docker exec php_fpm_avatar-3d-v1-dev tail -f storage/logs/webscraper-$(date +%Y-%m-%d).log

# Cerca errori
docker exec php_fpm_avatar-3d-v1-dev grep ERROR storage/logs/webscraper-*.log
```

## 📦 Testing

```bash
# Unit tests
php artisan test --filter=WebScraperTest

# Test specifico
php artisan test --filter=testScrapeReturnsValidData
```

## 🔄 Aggiornamenti

### Update Playwright

```bash
# 1. Update package.json
npm update playwright

# 2. Rebuild Docker
docker-compose build php

# 3. Restart container
docker-compose restart php
```

### Update AI Model

```php
// config/webscraper.php
'ai_analysis' => [
    'model' => 'gpt-4o-mini', // Cambia modello
],
```

## 📖 Riferimenti

- **[Playwright Setup](PLAYWRIGHT_SETUP.md)** - Configurazione browser headless completa
- **Laravel Modules**: https://github.com/nWidart/laravel-modules
- **Playwright**: https://playwright.dev/
- **OpenAI GPT-4o**: https://platform.openai.com/docs

---

**Versione**: 1.0.0
**Ultimo aggiornamento**: 2025-11-15