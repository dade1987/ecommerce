<?php

namespace App\Services;

use App\Services\NetworkScanningService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\parse_url;
use function Safe\preg_match;
use function Safe\preg_match_all;
use function Safe\preg_replace;
use Symfony\Component\Process\Process;

class DomainAnalysisService
{
    protected const TIMEOUT = 30; // Secondi per ogni processo (aumentato)

    protected const MAX_SUBDOMAINS_TO_SCAN = 8; // Limite per evitare timeout e costi eccessivi (ridotto)

    protected const MAX_EXECUTION_TIME = 300; // 5 minuti per scansione completa

    protected ?string $openaiApiKey;

    protected array $fullResults = [];

    protected string $originalDomain;

    protected string $locale = 'en';

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.key');
    }

    public function analyze(string $domain, string $locale = 'en'): array
    {
        $this->locale = $locale;
        $startTime = microtime(true);
        $this->originalDomain = $this->sanitizeDomain($domain);
        $this->fullResults['analysis'] = [];
        $this->fullResults['summary'] = [
            'subdomains_found' => 0,
            'subdomains_scanned' => 0,
            'scan_timeout' => false,
        ];

        $subdomains = $this->enumerateSubdomains($this->originalDomain);
        $this->fullResults['summary']['subdomains_found'] = count($subdomains);

        if (empty($subdomains)) {
            $domainsToScan = [$this->originalDomain];
        } else {
            $domainsToScan = $this->selectSubdomainsWithAI($subdomains);
        }

        $this->fullResults['summary']['subdomains_scanned'] = count($domainsToScan);
        $this->fullResults['summary']['scanned_targets'] = $domainsToScan;

        foreach ($domainsToScan as $index => $currentDomain) {
            // Controlla se stiamo per superare il timeout
            if ((microtime(true) - $startTime) > (self::MAX_EXECUTION_TIME - 60)) {
                Log::warning("Timeout raggiunto, interrompendo scansione dopo {$index} domini");
                $this->fullResults['summary']['scan_timeout'] = true;
                break;
            }

            try {
                $this->fullResults['analysis'][$currentDomain] = $this->analyzeSingleDomain($currentDomain);
            } catch (\Exception $e) {
                Log::error("Errore durante l'analisi di {$currentDomain}: ".$e->getMessage());
                $this->fullResults['analysis'][$currentDomain] = [
                    'error' => 'Analisi fallita per timeout o errore di rete',
                    'partial_data' => true,
                ];
            }
        }

        return $this->getRiskScore();
    }

    protected function selectSubdomainsWithAI(array $subdomains): array
    {
        if (! $this->openaiApiKey || empty($subdomains)) {
            return array_slice(array_unique(array_merge([$this->originalDomain], $subdomains)), 0, 5);
        }

        $client = OpenAI::client($this->openaiApiKey);
        $subdomainList = implode(', ', $subdomains);

        $prompt = trans('prompts.select_subdomains', [
            'domain' => $this->originalDomain,
            'subdomain_list' => $subdomainList,
        ], $this->locale);

        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $selected = json_decode($response->choices[0]->message->content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($selected)) {
                // Assicuriamoci che l'array abbia una struttura prevedibile (es. ['sub1', 'sub2'])
                $flatSelected = Arr::flatten($selected);

                return array_slice(array_unique(array_merge([$this->originalDomain], $flatSelected)), 0, 5);
            }
        } catch (\Exception $e) {
            Log::error('Errore durante la selezione dei sottodomini con AI: '.$e->getMessage());
        }

        // Fallback: se l'AI fallisce, prendi i primi 5
        return array_slice(array_unique(array_merge([$this->originalDomain], $subdomains)), 0, 5);
    }

    protected function sanitizeDomain(string $domain): string
    {
        $host = parse_url($domain, PHP_URL_HOST);
        if (empty($host)) {
            // Fallback for domains without scheme like 'example.com'
            $host = explode('/', $domain, 2)[0];
        }

        // Remove 'www.' prefix if present
        return preg_replace('/^www\./', '', $host);
    }

    protected function analyzeSingleDomain(string $domain): array
    {
        $results = [];

        // Eseguiamo una singola richiesta GET che useremo per più analisi
        $response = $this->makeHttpRequest('get', "https://{$domain}");

        // Se il target primario non risponde, eseguiamo solo controlli esterni
        if (! $response) {
            Log::warning("Il target primario {$domain} è irraggiungibile. Vengono eseguiti solo controlli esterni.");
            $results['error'] = 'Host Unreachable';
            $results['dns_records'] = $this->checkDnsRecords($domain);
            $results['port_scan'] = $this->performPortScan($domain);
            $results['cve_analysis'] = $this->analyzeCVE($results['port_scan'] ?? []);

            return $results;
        }

        // Il target è raggiungibile, procediamo con l'analisi completa
        $html = $response->body();
        $headers = $response->headers();

        $results['http_headers'] = $headers;
        $results['security_headers'] = $this->analyzeSecurityHeaders($headers);
        $results['robots_txt'] = $this->checkRobotsTxt($domain);
        $results['sitemap_xml'] = $this->checkSitemapXml($domain);

        if ($html) {
            $results['source_analysis'] = $this->analyzeSourceCode($html);
            $results['technology_analysis'] = $this->analyzeTechnology($html);
            $results['internal_links'] = $this->mapInternalLinks($html, $domain);
            $results['wordpress_analysis'] = $this->analyzeWordPress($domain, $html);
        } else {
            Log::warning("Impossibile recuperare il codice sorgente per {$domain} anche se l'host è raggiungibile.");
            $results['source_analysis'] = ['error' => 'Could not fetch source code'];
            $results['technology_analysis'] = ['error' => 'Could not fetch source code'];
            $results['internal_links'] = [];
            $results['wordpress_analysis'] = ['is_wordpress' => false, 'error' => 'Could not fetch source code'];
        }

        $results['dns_records'] = $this->checkDnsRecords($domain);
        $results['ssl_tls'] = $this->checkSslTls($domain);
        $results['cloudflare_detection'] = $this->detectCloudflare($domain, $headers);
        $results['port_scan'] = $this->performPortScan($domain);
        $results['cve_analysis'] = $this->analyzeCVE($results['port_scan'] ?? []);

        return $results;
    }

    protected function makeHttpRequest(string $method, string $url, array $options = []): ?\Illuminate\Http\Client\Response
    {
        // Default options
        $defaultOptions = [
            'connect_timeout' => 5,
            'timeout' => 10,
            'allow_redirects' => true,
            'verify' => false, // This handles potential SSL errors, like curl --insecure
        ];

        $finalOptions = array_merge($defaultOptions, $options);

        try {
            // Ensure URL has a scheme
            if (! preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://'.$url;
            }

            $client = Http::withOptions($finalOptions);
            $response = $client->{strtolower($method)}($url);

            if ($response->successful() || $response->redirect()) {
                return $response;
            }

            Log::warning("Richiesta HTTP Guzzle a {$url} fallita con status: ".$response->status());

            return null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Errore di connessione Guzzle durante la richiesta a {$url}: ".$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error("Errore generico Guzzle durante la richiesta HTTP a {$url}: ".$e->getMessage());

            return null;
        }
    }

    protected function runProcess(array $command): ?string
    {
        $process = new Process($command);
        $process->setTimeout(self::TIMEOUT);

        try {
            $process->mustRun();

            return $process->getOutput();
        } catch (\Exception $e) {
            // Per errori SSL, prova con --insecure
            if (strpos($e->getMessage(), 'SSL certificate problem') !== false &&
                in_array('curl', $command) &&
                ! in_array('--insecure', $command)) {

                Log::warning('Tentativo con --insecure per SSL error: '.$command[count($command) - 1]);
                $insecureCommand = $command;
                array_splice($insecureCommand, -1, 0, '--insecure');

                try {
                    $insecureProcess = new Process($insecureCommand);
                    $insecureProcess->setTimeout(self::TIMEOUT);
                    $insecureProcess->mustRun();

                    return $insecureProcess->getOutput();
                } catch (\Exception $insecureE) {
                    Log::error('Errore anche con --insecure: '.$insecureE->getMessage());
                }
            }

            Log::error("Errore durante l'esecuzione del processo: ".$e->getMessage());

            return null;
        }
    }

    protected function getHttpHeaders(string $domain): array
    {
        $response = $this->makeHttpRequest('head', $domain);

        return $response ? $response->headers() : [];
    }

    protected function checkRobotsTxt(string $domain): ?string
    {
        $response = $this->makeHttpRequest('get', "https://{$domain}/robots.txt");

        return $response ? $response->body() : null;
    }

    protected function checkSitemapXml(string $domain): ?string
    {
        $response = $this->makeHttpRequest('get', "https://{$domain}/sitemap.xml");

        return $response ? $response->body() : null;
    }

    protected function analyzeSourceCode(string $html): ?array
    {
        if (! $html) {
            return null;
        }

        // Cerchiamo commenti e librerie comuni
        preg_match_all('/<!--(.*?)-->/s', $html, $comments);
        preg_match_all('/(jquery|react|vue|angular|bootstrap)[\-\.]?([0-9\.]+)/i', $html, $libs);

        return [
            'comments' => $comments[1] ?? [],
            'libraries' => array_unique($libs[0]) ?? [],
        ];
    }

    protected function mapInternalLinks(string $html, string $domain): array
    {
        if (! $html) {
            return [];
        }

        preg_match_all('/<a\s+(?:[^>]*?\s+)?href="((?:https?:\/\/'.preg_quote($domain).'|(?!\/\/|\#))[^"]*)"/i', $html, $matches);

        return ! empty($matches[1]) ? array_unique($matches[1]) : [];
    }

    protected function enumerateSubdomains(string $domain): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)->get("https://crt.sh/?q={$domain}&output=json");

            if ($response->successful() && $response->json()) {
                $subdomains = collect($response->json())
                    ->pluck('name_value')
                    ->flatMap(fn ($name) => explode("\n", $name))
                    ->map(fn ($name) => trim($name, '*. '))
                    ->filter(fn ($name) => $name !== $domain && str_ends_with($name, '.'.$domain))
                    ->unique()
                    ->values()
                    ->all();

                return $subdomains;
            }

            return [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("Timeout durante la richiesta a crt.sh per il dominio {$domain}: ".$e->getMessage());

            return [];
        }
    }

    protected function checkDnsRecords(string $domain): array
    {
        $dnsTypes = ['A', 'MX', 'TXT', 'NS'];
        $dnsResults = [];
        foreach ($dnsTypes as $type) {
            $output = $this->runProcess(['dig', '+short', $domain, $type]);
            $dnsResults[$type] = $output ? explode("\n", trim($output)) : [];
        }

        return $dnsResults;
    }

    protected function checkSslTls(string $domain): ?string
    {
        // Questo è un controllo molto basilare, non sostituisce un'analisi completa.
        $command = "echo | openssl s_client -servername {$domain} -connect {$domain}:443 2>/dev/null | openssl x509 -noout -text";
        $output = $this->runProcess(['bash', '-c', $command]);

        return $output ?: null;
    }

    protected function analyzeSecurityHeaders(array $headers): array
    {
        $securityHeaders = [
            'content-security-policy' => false,
            'x-frame-options' => false,
            'x-content-type-options' => false,
            'x-xss-protection' => false,
            'strict-transport-security' => false,
            'referrer-policy' => false,
            'permissions-policy' => false,
        ];

        $missingHeaders = [];
        $presentHeaders = [];

        $headersLowercase = array_change_key_case($headers, CASE_LOWER);

        foreach ($securityHeaders as $header => $present) {
            if (array_key_exists(strtolower($header), $headersLowercase)) {
                $presentHeaders[] = $header;
            } else {
                $missingHeaders[] = $header;
            }
        }

        return [
            'present_headers' => $presentHeaders,
            'missing_headers' => $missingHeaders,
            'security_score' => count($securityHeaders) > 0 ? round((count($presentHeaders) / count($securityHeaders)) * 100, 2) : 0,
        ];
    }

    protected function analyzeTechnology(string $sourceCode): array
    {
        $technologies = [
            'jquery_old' => false,
            'bootstrap_old' => false,
            'php_version' => null,
            'cms_detected' => null,
            'framework_detected' => null,
            'outdated_indicators' => [],
        ];

        if (empty($sourceCode)) {
            return $technologies;
        }

        // Detect jQuery version
        if (preg_match('/jquery[.-]([0-9]+\.[0-9]+\.[0-9]+)/i', $sourceCode, $matches)) {
            $jqueryVersion = $matches[1];
            $technologies['jquery_version'] = $jqueryVersion;
            // jQuery < 3.0 è considerato vecchio
            if (version_compare($jqueryVersion, '3.0.0', '<')) {
                $technologies['jquery_old'] = true;
                $technologies['outdated_indicators'][] = "jQuery {$jqueryVersion} (obsoleto)";
            }
        }

        // Detect Bootstrap version
        if (preg_match('/bootstrap[.-]([0-9]+\.[0-9]+\.[0-9]+)/i', $sourceCode, $matches)) {
            $bootstrapVersion = $matches[1];
            $technologies['bootstrap_version'] = $bootstrapVersion;
            // Bootstrap < 4.0 è considerato vecchio
            if (version_compare($bootstrapVersion, '4.0.0', '<')) {
                $technologies['bootstrap_old'] = true;
                $technologies['outdated_indicators'][] = "Bootstrap {$bootstrapVersion} (obsoleto)";
            }
        }

        // Detect old HTML patterns
        if (strpos($sourceCode, 'DOCTYPE html PUBLIC') !== false) {
            $technologies['outdated_indicators'][] = 'DOCTYPE HTML 4.01 o XHTML (obsoleto)';
        }

        // Detect Flash references
        if (strpos($sourceCode, '.swf') !== false || strpos($sourceCode, 'application/x-shockwave-flash') !== false) {
            $technologies['outdated_indicators'][] = 'Adobe Flash rilevato (obsoleto e insicuro)';
        }

        // Detect old IE compatibility
        if (strpos($sourceCode, 'X-UA-Compatible') !== false) {
            $technologies['outdated_indicators'][] = 'Meta tag IE compatibility (indica supporto browser obsoleti)';
        }

        return $technologies;
    }

    protected function detectCloudflare(string $domain, array $headers): array
    {
        $results = [
            'is_cloudflare' => false,
            'indicators' => [],
            'ip_ranges' => [],
        ];

        // Controlla gli header HTTP
        if (! empty($headers)) {
            $headersLowercase = array_change_key_case($headers, CASE_LOWER);
            if (array_key_exists('cf-ray', $headersLowercase)) {
                $results['is_cloudflare'] = true;
                $results['indicators'][] = 'CF-RAY header trovato';
            }
            if (isset($headersLowercase['server']) && in_array('cloudflare', $headersLowercase['server'])) {
                $results['is_cloudflare'] = true;
                $results['indicators'][] = 'Server header cloudflare trovato';
            }
            if (array_key_exists('cf-cache-status', $headersLowercase)) {
                $results['is_cloudflare'] = true;
                $results['indicators'][] = 'CF-Cache-Status header trovato';
            }
        }

        // Controlla i range IP di Cloudflare
        $ip = gethostbyname($domain);
        if ($ip !== $domain) {
            $cloudflareRanges = [
                '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
                '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
                '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
                '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            ];

            foreach ($cloudflareRanges as $range) {
                if ($this->ipInRange($ip, $range)) {
                    $results['is_cloudflare'] = true;
                    $results['indicators'][] = "IP {$ip} in range Cloudflare {$range}";
                    $results['ip_ranges'][] = $range;
                }
            }
        }

        return $results;
    }

    protected function analyzeWordPress(string $domain, ?string $html): array
    {
        $results = [
            'is_wordpress' => false,
            'version' => null,
            'plugins' => [],
            'themes' => [],
            'indicators' => [],
            'api_namespaces' => [],
            'vulnerable_plugins' => [],
            'vulnerable_themes' => [],
            'exposed_files' => [],
            'security_issues' => [],
        ];

        // Il contenuto HTML è ora passato come parametro
        if (! $html) {
            return $results;
        }

        // Controlla indicatori WordPress
        $wpIndicators = [
            'wp-content/' => 'wp-content directory trovato',
            'wp-includes/' => 'wp-includes directory trovato',
            'wp-admin/' => 'wp-admin directory trovato',
            'wordpress' => 'WordPress menzionato nel codice',
            'wp-json/' => 'WordPress REST API trovata',
        ];

        foreach ($wpIndicators as $indicator => $message) {
            if (strpos($html, $indicator) !== false) {
                $results['is_wordpress'] = true;
                $results['indicators'][] = $message;
            }
        }

        if ($results['is_wordpress']) {
            // Analisi versione WordPress approfondita
            $results['version'] = $this->getWordPressVersion($domain, $html);

            // Analisi plugin dettagliata
            $results['plugins'] = $this->getWordPressPlugins($domain, $html);

            // Analisi temi dettagliata
            $results['themes'] = $this->getWordPressThemes($domain, $html);

            // Controlla API WordPress
            $results['api_namespaces'] = $this->getWordPressApiInfo($domain);

            // Controlla file esposti pericolosi
            $results['exposed_files'] = $this->checkExposedWordPressFiles($domain);

            // Analisi vulnerabilità con GPT
            if (! empty($results['plugins']) || ! empty($results['themes']) || $results['version']) {
                $vulnerabilityAnalysis = $this->analyzeWordPressVulnerabilities($results);
                $results['vulnerable_plugins'] = $vulnerabilityAnalysis['plugins'] ?? [];
                $results['vulnerable_themes'] = $vulnerabilityAnalysis['themes'] ?? [];
                $results['security_issues'] = $vulnerabilityAnalysis['core_issues'] ?? [];
            }
        }

        return $results;
    }

    protected function getWordPressVersion(string $domain, string $html): ?string
    {
        $version = null;

        // Metodo 1: Meta generator
        if (preg_match('/generator.*?wordpress\s*([0-9\.]+)/i', $html, $matches)) {
            $version = $matches[1];
        }

        // Metodo 2: wp-embed.min.js versione
        if (! $version && preg_match('/wp-includes\/js\/wp-embed\.min\.js\?ver=([0-9\.]+)/', $html, $matches)) {
            $version = $matches[1];
        }

        // Metodo 3: readme.html
        if (! $version) {
            $response = $this->makeHttpRequest('get', "https://{$domain}/readme.html");
            $readme = $response ? $response->body() : null;
            if ($readme && preg_match('/Version\s*([0-9\.]+)/i', $readme, $matches)) {
                $version = $matches[1];
            }
        }

        // Metodo 4: wp-includes/version.php (raramente esposto)
        if (! $version) {
            $response = $this->makeHttpRequest('get', "https://{$domain}/wp-includes/version.php");
            $versionFile = $response ? $response->body() : null;
            if ($versionFile && preg_match('/wp_version\s*=\s*[\'"]([0-9\.]+)[\'"]/', $versionFile, $matches)) {
                $version = $matches[1];
            }
        }

        return $version;
    }

    protected function getWordPressPlugins(string $domain, string $html): array
    {
        $plugins = [];

        // Metodo 1: Scansione HTML
        preg_match_all('/wp-content\/plugins\/([^\/\?\#]+)/', $html, $pluginMatches);
        if (! empty($pluginMatches[1])) {
            $plugins = array_unique($pluginMatches[1]);
        }

        // Metodo 2: Controlla directory plugins comuni
        $commonPlugins = [
            'akismet', 'jetpack', 'yoast-seo', 'contact-form-7', 'elementor',
            'woocommerce', 'wp-super-cache', 'wordfence', 'all-in-one-wp-migration',
            'classic-editor', 'duplicate-post', 'wp-optimize', 'updraftplus',
            'wp-rocket', 'really-simple-ssl', 'wpforms-lite', 'mailchimp-for-wp',
        ];

        foreach ($commonPlugins as $plugin) {
            $response = $this->makeHttpRequest('head', "https://{$domain}/wp-content/plugins/{$plugin}/", ['timeout' => 5, 'connect_timeout' => 3]);
            if ($response && $response->ok()) {
                if (! in_array($plugin, $plugins)) {
                    $plugins[] = $plugin;
                }
            }
        }

        // Metodo 3: Ottieni versioni plugin
        $pluginsWithVersions = [];
        foreach ($plugins as $plugin) {
            $version = $this->getPluginVersion($domain, $plugin);
            $pluginsWithVersions[$plugin] = $version;
        }

        return $pluginsWithVersions;
    }

    protected function getPluginVersion(string $domain, string $plugin): ?string
    {
        // Prova a leggere readme.txt del plugin
        $response = $this->makeHttpRequest('get', "https://{$domain}/wp-content/plugins/{$plugin}/readme.txt");
        $readme = $response ? $response->body() : null;
        if ($readme) {
            if (preg_match('/Stable tag:\s*([0-9\.]+)/i', $readme, $matches)) {
                return $matches[1];
            }
            if (preg_match('/Version:\s*([0-9\.]+)/i', $readme, $matches)) {
                return $matches[1];
            }
        }

        // Prova il file principale del plugin
        $response = $this->makeHttpRequest('get', "https://{$domain}/wp-content/plugins/{$plugin}/{$plugin}.php");
        $mainFile = $response ? $response->body() : null;
        if ($mainFile && preg_match('/Version:\s*([0-9\.]+)/i', $mainFile, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function getWordPressThemes(string $domain, string $html): array
    {
        $themes = [];

        // Trova temi dall'HTML
        preg_match_all('/wp-content\/themes\/([^\/\?\#]+)/', $html, $themeMatches);
        if (! empty($themeMatches[1])) {
            $themes = array_unique($themeMatches[1]);
        }

        // Ottieni versioni temi
        $themesWithVersions = [];
        foreach ($themes as $theme) {
            $version = $this->getThemeVersion($domain, $theme);
            $themesWithVersions[$theme] = $version;
        }

        return $themesWithVersions;
    }

    protected function getThemeVersion(string $domain, string $theme): ?string
    {
        // Prova style.css del tema
        $response = $this->makeHttpRequest('get', "https://{$domain}/wp-content/themes/{$theme}/style.css");
        $style = $response ? $response->body() : null;
        if ($style && preg_match('/Version:\s*([0-9\.]+)/i', $style, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function getWordPressApiInfo(string $domain): array
    {
        $apiInfo = [];

        // Controlla API principale
        $response = $this->makeHttpRequest('get', "https://{$domain}/wp-json/");
        $wpJson = $response ? $response->body() : null;
        if ($wpJson) {
            $jsonData = json_decode($wpJson, true);
            if (isset($jsonData['namespaces'])) {
                $apiInfo['namespaces'] = $jsonData['namespaces'];
            }
        }

        // Controlla endpoint specifici se disponibili
        $response = $this->makeHttpRequest('get', "https://{$domain}/wp-json/wp/v2/");
        $wpApiV2 = $response ? $response->body() : null;
        if ($wpApiV2) {
            $apiData = json_decode($wpApiV2, true);
            if (isset($apiData['routes'])) {
                $apiInfo['available_routes'] = array_keys($apiData['routes']);
            }
        }

        return $apiInfo;
    }

    protected function checkExposedWordPressFiles(string $domain): array
    {
        $exposedFiles = [];
        $criticalFiles = [
            'wp-config.php' => 'File di configurazione con credenziali database',
            'wp-config-sample.php' => 'File di configurazione di esempio',
            'wp-admin/install.php' => 'Script di installazione WordPress',
            'wp-admin/upgrade.php' => 'Script di aggiornamento WordPress',
            'readme.html' => 'File README con versione WordPress',
            'license.txt' => 'File licenza WordPress',
            'wp-content/debug.log' => 'Log di debug WordPress',
        ];

        foreach ($criticalFiles as $file => $description) {
            $response = $this->makeHttpRequest('head', "https://{$domain}/{$file}", ['timeout' => 5, 'connect_timeout' => 3]);
            if ($response && $response->ok()) {
                $exposedFiles[$file] = $description;
            }
        }

        return $exposedFiles;
    }

    protected function analyzeWordPressVulnerabilities(array $wpData): array
    {
        if (! $this->openaiApiKey) {
            return ['plugins' => [], 'themes' => [], 'core_issues' => []];
        }

        try {
            $client = OpenAI::client($this->openaiApiKey);

            $prompt = $this->buildWordPressVulnerabilityPrompt($wpData);

            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => trans('prompts.wordpress_vulnerability_system', [], $this->locale)],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            return $content ?: ['plugins' => [], 'themes' => [], 'core_issues' => []];

        } catch (\Exception $e) {
            Log::error("Errore durante l'analisi vulnerabilità WordPress: ".$e->getMessage());

            return ['plugins' => [], 'themes' => [], 'core_issues' => []];
        }
    }

    protected function buildWordPressVulnerabilityPrompt(array $wpData): string
    {
        $dataString = json_encode($wpData, JSON_PRETTY_PRINT);

        return trans('prompts.wordpress_vulnerability_user', ['data_string' => $dataString], $this->locale);
    }

    protected function performPortScan(string $domain): array
    {
        $networkScanner = new NetworkScanningService();

        try {
            $scanResults = $networkScanner->portScan($domain);

            // Converte il formato per compatibilità con il resto del sistema
            return [
                'scanned_ports' => $scanResults['scanned_ports'] ?? [],
                'open_ports' => $scanResults['open_ports'] ?? [],
                'closed_ports' => $scanResults['closed_ports'] ?? [],
                'services' => $scanResults['services'] ?? [],
                'scan_duration' => $scanResults['scan_duration'] ?? 0,
                'host' => $scanResults['host'] ?? $domain,
            ];
        } catch (\Exception $e) {
            Log::error("Errore durante il port scan per {$domain}: ".$e->getMessage());

            return [
                'scanned_ports' => [],
                'open_ports' => [],
                'closed_ports' => [],
                'services' => [],
                'scan_duration' => 0,
                'host' => $domain,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function analyzeCVE(array $portScanResults): array
    {
        if (! $this->openaiApiKey || empty($portScanResults['services'])) {
            return ['cve_analysis' => 'Nessun servizio trovato o API non disponibile'];
        }

        $networkScanner = new NetworkScanningService();
        $cveInfo = $networkScanner->extractCVEInfo($portScanResults);

        if (empty($cveInfo)) {
            return ['cve_analysis' => 'Nessun servizio identificato per analisi CVE'];
        }

        try {
            $client = OpenAI::client($this->openaiApiKey);
            $prompt = trans('prompts.cve_analysis_user', ['cve_info' => json_encode($cveInfo, JSON_PRETTY_PRINT)], $this->locale);

            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => trans('prompts.cve_analysis_system', [], $this->locale)],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            // Aggiungi informazioni aggiuntive dai risk indicators
            if (isset($content['vulnerabilities'])) {
                foreach ($content['vulnerabilities'] as &$vulnerability) {
                    $port = $vulnerability['port'];
                    if (isset($cveInfo[$port - 1]['risk_indicators'])) {
                        $vulnerability['risk_indicators'] = $cveInfo[$port - 1]['risk_indicators'];
                    }
                }
            }

            return $content ?: ['cve_analysis' => 'Errore nel parsing della risposta AI'];

        } catch (\Exception $e) {
            Log::error("Errore nell'analisi CVE: ".$e->getMessage());

            return ['cve_analysis' => 'Errore durante l\'analisi CVE: '.$e->getMessage()];
        }
    }

    protected function ipInRange(string $ip, string $range): bool
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) == $subnet;
    }

    protected function queryThreatIntelligence(string $domain)
    {
        // Integrazione con Shodan
        $shodanApiKey = config('services.shodan.key');
        if ($shodanApiKey) {
            try {
                $response = Http::timeout(self::TIMEOUT)->get("https://api.shodan.io/dns/domain/{$domain}?key={$shodanApiKey}");
                if ($response->successful()) {
                    $this->fullResults['threat_intelligence']['shodan'] = $response->json();
                }
            } catch (\Exception $e) {
                Log::warning("Chiamata a Shodan fallita per {$domain}: ".$e->getMessage());
            }
        }
        // Placeholder per Censys, AlienVault
        $this->fullResults['threat_intelligence'] = $this->fullResults['threat_intelligence'] ?? null;
    }

    protected function checkForLeaks(string $domain)
    {
        // Placeholder per HaveIBeenPwned, GitHub
        // Richiedono chiavi API
        $this->fullResults['leaks_breaches'] = null;
    }

    protected function getRiskScore(): array
    {
        if (! $this->openaiApiKey) {
            return ['error' => 'La chiave API di OpenAI non è configurata.'];
        }

        $client = OpenAI::client($this->openaiApiKey);
        $summarizedResults = $this->summarizeResultsForGpt($this->fullResults['analysis']);
        $prompt = $this->buildGptPrompt($summarizedResults, $this->fullResults['summary']);

        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => trans('prompts.risk_score_system', [], $this->locale)],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => trans('prompts.risk_score_json_error', [], $this->locale)];
            }

            return array_merge($content, [
                'summary' => $this->fullResults['summary'],
                'raw_data' => $this->fullResults['analysis'],
            ]);

        } catch (\Exception $e) {
            Log::error('Errore chiamata OpenAI per valutazione rischio: '.$e->getMessage());

            return ['error' => "Impossibile contattare l'Intelligenza Artificiale per la valutazione.", 'details' => $e->getMessage()];
        }
    }

    protected function buildGptPrompt(array $summarizedData, array $summary): string
    {
        $dataString = json_encode($summarizedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $summaryString = trans('prompts.risk_score_summary_intro', [
            'domain' => $this->originalDomain,
            'subdomains_found' => $summary['subdomains_found'],
            'subdomains_scanned' => $summary['subdomains_scanned'],
        ], $this->locale);

        if ($summary['scan_timeout']) {
            $summaryString .= ' '.trans('prompts.risk_score_summary_timeout', [], $this->locale);
        }

        return trans('prompts.risk_score_user', [
            'domain' => $this->originalDomain,
            'summary_string' => $summaryString,
            'data_string' => $dataString,
        ], $this->locale);
    }

    protected function summarizeResultsForGpt(array $results): array
    {
        $masterSummary = [];
        foreach ($results as $domain => $domainData) {
            $masterSummary[$domain] = $this->summarizeSingleDomainResults($domainData);
        }

        return $masterSummary;
    }

    protected function summarizeSingleDomainResults(array $data): array
    {
        $summary = $data;

        // Riassumi i link interni
        if (isset($summary['internal_links']) && is_array($summary['internal_links'])) {
            $count = count($summary['internal_links']);
            $summary['internal_links'] = [
                'count' => $count,
                'sample' => array_slice($summary['internal_links'], 0, 15),
            ];
        }

        // Riassumi i commenti nel sorgente
        if (isset($summary['source_analysis']['comments']) && is_array($summary['source_analysis']['comments'])) {
            $count = count($summary['source_analysis']['comments']);
            $sample = array_slice($summary['source_analysis']['comments'], 0, 5);
            $sample = array_map(fn ($c) => mb_strimwidth(trim($c), 0, 200, '...'), $sample);
            $summary['source_analysis']['comments'] = [
                'count' => $count,
                'sample' => $sample,
            ];
        }

        // Tronca i dati testuali lunghi
        foreach (['http_headers', 'robots_txt', 'sitemap_xml'] as $key) {
            if (isset($summary[$key]) && is_string($summary[$key])) {
                $summary[$key] = mb_strimwidth($summary[$key], 0, 2500, '...');
            }
        }

        // Estrai solo le informazioni chiave dal certificato SSL
        if (isset($summary['ssl_tls']) && is_string($summary['ssl_tls'])) {
            $certText = $summary['ssl_tls'];
            $sslSummary = [];

            preg_match('/Subject: (.*)/', $certText, $matches);
            $sslSummary['subject'] = trim($matches[1] ?? 'N/D');

            preg_match('/Issuer: (.*)/', $certText, $matches);
            $sslSummary['issuer'] = trim($matches[1] ?? 'N/D');

            preg_match('/Not Before: (.*)/', $certText, $matches);
            $sslSummary['valid_from'] = trim($matches[1] ?? 'N/D');

            preg_match('/Not After : (.*)/', $certText, $matches);
            $sslSummary['valid_until'] = trim($matches[1] ?? 'N/D');

            preg_match('/Subject Alternative Name:([\s\S]*?)X509v3/', $certText, $matches);
            if (! empty($matches[1])) {
                $san_list = str_replace('DNS:', '', $matches[1]);
                $sslSummary['subject_alternative_names'] = array_map('trim', explode(',', trim($san_list)));
            } else {
                $sslSummary['subject_alternative_names'] = [];
            }

            preg_match('/Signature Algorithm: (.*)/', $certText, $matches);
            $sslSummary['signature_algorithm'] = trim($matches[1] ?? 'N/D');

            $summary['ssl_tls'] = $sslSummary;
        }

        // Riassumi i banner dei servizi per evitare troppi dati
        if (isset($summary['port_scan']['services']) && is_array($summary['port_scan']['services'])) {
            foreach ($summary['port_scan']['services'] as $port => $service) {
                if (isset($service['banner']) && strlen($service['banner']) > 300) {
                    $summary['port_scan']['services'][$port]['banner'] = mb_strimwidth($service['banner'], 0, 300, '...');
                }
            }
        }

        // Assicurati che i nuovi dati siano inclusi
        if (isset($summary['security_headers'])) {
            // Security headers è già in formato compatto, mantieni così
        }

        if (isset($summary['technology_analysis'])) {
            // Technology analysis è già in formato compatto, mantieni così
        }

        return $summary;
    }
}
