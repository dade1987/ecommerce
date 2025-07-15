<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;
use Symfony\Component\Process\Process;
use App\Services\NetworkScanningService;

class DomainAnalysisService
{
    protected const TIMEOUT = 30; // Secondi per ogni processo (aumentato)
    protected const MAX_SUBDOMAINS_TO_SCAN = 8; // Limite per evitare timeout e costi eccessivi (ridotto)
    protected const MAX_EXECUTION_TIME = 300; // 5 minuti per scansione completa

    protected ?string $openaiApiKey;
    protected array $fullResults = [];
    protected string $originalDomain;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.key');
    }

    public function analyze(string $domain): array
    {
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
                Log::error("Errore durante l'analisi di {$currentDomain}: " . $e->getMessage());
                $this->fullResults['analysis'][$currentDomain] = [
                    'error' => 'Analisi fallita per timeout o errore di rete',
                    'partial_data' => true
                ];
            }
        }
        
        return $this->getRiskScore();
    }
    
    protected function selectSubdomainsWithAI(array $subdomains): array
    {
        if (!$this->openaiApiKey || empty($subdomains)) {
            return array_slice(array_unique(array_merge([$this->originalDomain], $subdomains)), 0, 5);
        }

        $client = OpenAI::client($this->openaiApiKey);
        $subdomainList = implode(', ', $subdomains);
        $prompt = "Dalla seguente lista di sottodomini per '{$this->originalDomain}', seleziona i 5 che ritieni più interessanti per un'analisi di sicurezza (es. api, vpn, dev, admin, test, etc.). Restituisci SOLO un array JSON con i 5 domini. Lista: {$subdomainList}";

        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
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
            Log::error("Errore durante la selezione dei sottodomini con AI: " . $e->getMessage());
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

        $html = $this->runProcess(['curl', '--location', '--connect-timeout', '10', "https://{$domain}"]);

        $results['http_headers'] = $this->getHttpHeaders($domain);
        $results['security_headers'] = $this->analyzeSecurityHeaders($results['http_headers'] ?? '');
        $results['robots_txt'] = $this->checkRobotsTxt($domain);
        $results['sitemap_xml'] = $this->checkSitemapXml($domain);
        
        if ($html) {
            $results['source_analysis'] = $this->analyzeSourceCode($html);
            $results['technology_analysis'] = $this->analyzeTechnology($html);
            $results['internal_links'] = $this->mapInternalLinks($html, $domain);
            $results['wordpress_analysis'] = $this->analyzeWordPress($domain, $html);
        } else {
            Log::warning("Impossibile recuperare il codice sorgente per {$domain}. Salto di diverse analisi.");
            $results['source_analysis'] = ['error' => 'Could not fetch source code'];
            $results['technology_analysis'] = ['error' => 'Could not fetch source code'];
            $results['internal_links'] = [];
            $results['wordpress_analysis'] = ['is_wordpress' => false, 'error' => 'Could not fetch source code'];
        }

        $results['dns_records'] = $this->checkDnsRecords($domain);
        $results['ssl_tls'] = $this->checkSslTls($domain);
        $results['cloudflare_detection'] = $this->detectCloudflare($domain);
        $results['port_scan'] = $this->performPortScan($domain);
        $results['cve_analysis'] = $this->analyzeCVE($results['port_scan'] ?? []);
        return $results;
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
                !in_array('--insecure', $command)) {
                
                Log::warning("Tentativo con --insecure per SSL error: " . $command[count($command)-1]);
                $insecureCommand = $command;
                array_splice($insecureCommand, -1, 0, '--insecure');
                
                try {
                    $insecureProcess = new Process($insecureCommand);
                    $insecureProcess->setTimeout(self::TIMEOUT);
                    $insecureProcess->mustRun();
                    return $insecureProcess->getOutput();
                } catch (\Exception $insecureE) {
                    Log::error("Errore anche con --insecure: " . $insecureE->getMessage());
                }
            }
            
            Log::error("Errore durante l'esecuzione del processo: " . $e->getMessage());
            return null;
        }
    }

    protected function getHttpHeaders(string $domain): ?string
    {
        $output = $this->runProcess(['curl', '-I', '--location', '--connect-timeout', '5', $domain]);
        return $output ?: null;
    }

    protected function checkRobotsTxt(string $domain): ?string
    {
        $output = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/robots.txt"]);
        return $output ?: null;
    }

    protected function checkSitemapXml(string $domain): ?string
    {
        $output = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/sitemap.xml"]);
        return $output ?: null;
    }

    protected function analyzeSourceCode(string $html): ?array
    {
        if (!$html) {
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
        if (!$html) {
            return [];
        }

        preg_match_all('/<a\s+(?:[^>]*?\s+)?href="((?:https?:\/\/' . preg_quote($domain) . '|(?!\/\/|\#))[^"]*)"/i', $html, $matches);
        return !empty($matches[1]) ? array_unique($matches[1]) : [];
    }

    protected function enumerateSubdomains(string $domain): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)->get("https://crt.sh/?q={$domain}&output=json");

            if ($response->successful() && $response->json()) {
                $subdomains = collect($response->json())
                    ->pluck('name_value')
                    ->flatMap(fn($name) => explode("\n", $name))
                    ->map(fn($name) => trim($name, '*. '))
                    ->filter(fn($name) => $name !== $domain && str_ends_with($name, '.' . $domain))
                    ->unique()
                    ->values()
                    ->all();
                return $subdomains;
            }
            return [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("Timeout durante la richiesta a crt.sh per il dominio {$domain}: " . $e->getMessage());
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

    protected function analyzeSecurityHeaders(string $headers): array
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
        
        foreach ($securityHeaders as $header => $present) {
            if (stripos($headers, $header) !== false) {
                $presentHeaders[] = $header;
            } else {
                $missingHeaders[] = $header;
            }
        }

        return [
            'present_headers' => $presentHeaders,
            'missing_headers' => $missingHeaders,
            'security_score' => round((count($presentHeaders) / count($securityHeaders)) * 100, 2),
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

    protected function detectCloudflare(string $domain): array
    {
        $results = [
            'is_cloudflare' => false,
            'indicators' => [],
            'ip_ranges' => []
        ];

        // Controlla gli header HTTP
        $headers = $this->getHttpHeaders($domain);
        if ($headers) {
            if (strpos($headers, 'CF-RAY') !== false) {
                $results['is_cloudflare'] = true;
                $results['indicators'][] = 'CF-RAY header trovato';
            }
            if (strpos($headers, 'server: cloudflare') !== false || strpos($headers, 'Server: cloudflare') !== false) {
                $results['is_cloudflare'] = true;
                $results['indicators'][] = 'Server header cloudflare trovato';
            }
            if (strpos($headers, 'cf-cache-status') !== false) {
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
                '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22'
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
            'security_issues' => []
        ];

        // Il contenuto HTML è ora passato come parametro
        if (!$html) {
            return $results;
        }

        // Controlla indicatori WordPress
        $wpIndicators = [
            'wp-content/' => 'wp-content directory trovato',
            'wp-includes/' => 'wp-includes directory trovato',
            'wp-admin/' => 'wp-admin directory trovato',
            'wordpress' => 'WordPress menzionato nel codice',
            'wp-json/' => 'WordPress REST API trovata'
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
            if (!empty($results['plugins']) || !empty($results['themes']) || $results['version']) {
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
        if (!$version && preg_match('/wp-includes\/js\/wp-embed\.min\.js\?ver=([0-9\.]+)/', $html, $matches)) {
            $version = $matches[1];
        }
        
        // Metodo 3: readme.html
        if (!$version) {
            $readme = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/readme.html"]);
            if ($readme && preg_match('/Version\s*([0-9\.]+)/i', $readme, $matches)) {
                $version = $matches[1];
            }
        }
        
        // Metodo 4: wp-includes/version.php (raramente esposto)
        if (!$version) {
            $versionFile = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/wp-includes/version.php"]);
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
        if (!empty($pluginMatches[1])) {
            $plugins = array_unique($pluginMatches[1]);
        }
        
        // Metodo 2: Controlla directory plugins comuni
        $commonPlugins = [
            'akismet', 'jetpack', 'yoast-seo', 'contact-form-7', 'elementor',
            'woocommerce', 'wp-super-cache', 'wordfence', 'all-in-one-wp-migration',
            'classic-editor', 'duplicate-post', 'wp-optimize', 'updraftplus',
            'wp-rocket', 'really-simple-ssl', 'wpforms-lite', 'mailchimp-for-wp'
        ];
        
        foreach ($commonPlugins as $plugin) {
            $pluginPath = $this->runProcess(['curl', '--location', '--connect-timeout', '3', '-I', "https://{$domain}/wp-content/plugins/{$plugin}/"]);
            if ($pluginPath && strpos($pluginPath, '200 OK') !== false) {
                if (!in_array($plugin, $plugins)) {
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
        $readme = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/wp-content/plugins/{$plugin}/readme.txt"]);
        if ($readme) {
            if (preg_match('/Stable tag:\s*([0-9\.]+)/i', $readme, $matches)) {
                return $matches[1];
            }
            if (preg_match('/Version:\s*([0-9\.]+)/i', $readme, $matches)) {
                return $matches[1];
            }
        }
        
        // Prova il file principale del plugin
        $mainFile = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/wp-content/plugins/{$plugin}/{$plugin}.php"]);
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
        if (!empty($themeMatches[1])) {
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
        $style = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/wp-content/themes/{$theme}/style.css"]);
        if ($style && preg_match('/Version:\s*([0-9\.]+)/i', $style, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    protected function getWordPressApiInfo(string $domain): array
    {
        $apiInfo = [];
        
        // Controlla API principale
        $wpJson = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/wp-json/"]);
        if ($wpJson) {
            $jsonData = json_decode($wpJson, true);
            if (isset($jsonData['namespaces'])) {
                $apiInfo['namespaces'] = $jsonData['namespaces'];
            }
        }
        
        // Controlla endpoint specifici se disponibili
        $wpApiV2 = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/wp-json/wp/v2/"]);
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
            'wp-content/debug.log' => 'Log di debug WordPress'
        ];
        
        foreach ($criticalFiles as $file => $description) {
            $response = $this->runProcess(['curl', '--location', '--connect-timeout', '3', '-I', "https://{$domain}/{$file}"]);
            if ($response && strpos($response, '200 OK') !== false) {
                $exposedFiles[$file] = $description;
            }
        }
        
        return $exposedFiles;
    }

    protected function analyzeWordPressVulnerabilities(array $wpData): array
    {
        if (!$this->openaiApiKey) {
            return ['plugins' => [], 'themes' => [], 'core_issues' => []];
        }
        
        try {
            $client = OpenAI::client($this->openaiApiKey);
            
            $prompt = $this->buildWordPressVulnerabilityPrompt($wpData);
            
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sei un esperto di sicurezza WordPress. Analizza le versioni specifiche di WordPress, plugin e temi per identificare vulnerabilità note con CVE specifici. Rispondi SOLO con dati concreti e verificabili.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);
            
            $content = json_decode($response->choices[0]->message->content, true);
            
            return $content ?: ['plugins' => [], 'themes' => [], 'core_issues' => []];
            
        } catch (\Exception $e) {
            Log::error("Errore durante l'analisi vulnerabilità WordPress: " . $e->getMessage());
            return ['plugins' => [], 'themes' => [], 'core_issues' => []];
        }
    }

    protected function buildWordPressVulnerabilityPrompt(array $wpData): string
    {
        $dataString = json_encode($wpData, JSON_PRETTY_PRINT);
        
        return <<<PROMPT
Analizza questa installazione WordPress per identificare vulnerabilità specifiche e concrete.

CRITERI RIGOROSI PER WORDPRESS:
1. VERIFICA che il CVE esista nel database NVD/WPVulnDB
2. CONFERMA che la versione specifica sia vulnerabile
3. Per plugin/temi senza versione identificata, NON assumere vulnerabilità
4. Fornisci SOLO CVSS score reali e verificati
5. Se non sei sicuro del CVE, ometti dalla risposta
6. NON creare CVE fittizi o inventati
7. Per file esposti, considera solo se rappresentano rischi concreti

ESEMPI CVE WORDPRESS CORRETTI:
- WordPress 4.9.1: CVE-2018-6389 (CVSS 5.3 - DoS)
- Contact Form 7 4.6.1: CVE-2017-9804 (CVSS 6.1 - XSS)
- WP Super Cache 1.4.4: CVE-2017-1000600 (CVSS 8.8 - RCE)
- Yoast SEO 7.0.2: CVE-2018-6511 (CVSS 6.1 - XSS)

INSTALLAZIONE WORDPRESS:
{$dataString}

Fornisci risposta in formato JSON:
{
  "plugins": [
    {
      "name": "contact-form-7",
      "version": "4.6.1",
      "cves": ["CVE-2017-9804"],
      "risk_level": 8,
      "cvss_score": 6.1,
      "description": "XSS vulnerability in Contact Form 7 before 4.8",
      "recommendation": "Aggiorna a versione 4.8 o superiore"
    }
  ],
  "themes": [
    {
      "name": "twentyfifteen",
      "version": "1.8",
      "cves": ["CVE-2019-8943"],
      "risk_level": 6,
      "description": "Authenticated theme editor vulnerability",
      "recommendation": "Aggiorna tema o disabilita editor"
    }
  ],
  "core_issues": [
    {
      "component": "WordPress Core",
      "version": "4.9.1",
      "cves": ["CVE-2018-6389"],
      "risk_level": 7,
      "cvss_score": 5.3,
      "description": "DoS vulnerability in load-scripts.php",
      "recommendation": "Aggiorna a WordPress 4.9.2 o superiore"
    }
  ]
}
PROMPT;
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
                'host' => $scanResults['host'] ?? $domain
            ];
        } catch (\Exception $e) {
            Log::error("Errore durante il port scan per {$domain}: " . $e->getMessage());
            return [
                'scanned_ports' => [],
                'open_ports' => [],
                'closed_ports' => [],
                'services' => [],
                'scan_duration' => 0,
                'host' => $domain,
                'error' => $e->getMessage()
            ];
        }
    }



    protected function analyzeCVE(array $portScanResults): array
    {
        if (!$this->openaiApiKey || empty($portScanResults['services'])) {
            return ['cve_analysis' => 'Nessun servizio trovato o API non disponibile'];
        }

        $networkScanner = new NetworkScanningService();
        $cveInfo = $networkScanner->extractCVEInfo($portScanResults);

        if (empty($cveInfo)) {
            return ['cve_analysis' => 'Nessun servizio identificato per analisi CVE'];
        }

        try {
            $client = OpenAI::client($this->openaiApiKey);
            $prompt = "Analizza i seguenti servizi identificati tramite port scan e banner grabbing. IMPORTANTE: Identifica SOLO CVE specifici per versioni software chiaramente identificate.\n\nCRITERI RIGOROSI PER CVE:\n1. VERIFICA che il CVE esista nel database NVD/MITRE\n2. CONFERMA che la versione software identificata sia effettivamente vulnerabile\n3. FORNISCI solo CVSS score reali e verificati\n4. Se la versione non è identificata, NON assumere vulnerabilità\n5. Se non sei sicuro del CVE, ometti dalla risposta\n6. NON creare CVE fittizi o inventati\n\nEsempi di CVE CORRETTI:\n- Apache 2.2.15: CVE-2011-3192 (CVSS 7.8 - DoS)\n- vsftpd 2.3.4: CVE-2011-2523 (CVSS 10.0 - RCE Backdoor)\n- WordPress 4.9.1: CVE-2018-6389 (CVSS 5.3 - DoS)\n- OpenSSH 7.4: CVE-2016-10012 (CVSS 7.8 - Privilege Escalation)\n\nFormato JSON richiesto:\n{\n  \"vulnerabilities\": [\n    {\n      \"port\": 80,\n      \"service\": \"Apache 2.2.15\",\n      \"software\": \"Apache\",\n      \"version\": \"2.2.15\",\n      \"confirmed_cves\": [\"CVE-2011-3192\"],\n      \"risk_level\": 8,\n      \"cvss_score\": 7.8,\n      \"vulnerability_type\": \"DoS\",\n      \"recommendations\": \"Aggiorna ad Apache 2.4.x\"\n    }\n  ]\n}\n\nServizi trovati:\n" . json_encode($cveInfo, JSON_PRETTY_PRINT);

            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sei un esperto di cybersecurity specializzato in analisi di vulnerabilità CVE. Analizza i servizi forniti e identifica potenziali CVE basandoti sulle informazioni dettagliate del banner, versioni software e indicatori di rischio. Fornisci raccomandazioni specifiche per ogni vulnerabilità identificata.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2
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
            Log::error("Errore nell'analisi CVE: " . $e->getMessage());
            return ['cve_analysis' => 'Errore durante l\'analisi CVE: ' . $e->getMessage()];
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
                Log::warning("Chiamata a Shodan fallita per {$domain}: " . $e->getMessage());
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
        if (!$this->openaiApiKey) {
            return ['error' => 'La chiave API di OpenAI non è configurata.'];
        }

        $client = OpenAI::client($this->openaiApiKey);
        $summarizedResults = $this->summarizeResultsForGpt($this->fullResults['analysis']);
        $prompt = $this->buildGptPrompt($summarizedResults, $this->fullResults['summary']);

        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sei un esperto di cybersecurity. Analizza i dati forniti sull\'infrastruttura di un dominio (incluso i suoi sottodomini) e restituisci SOLO un oggetto JSON con due chiavi: "risk_percentage" e "critical_points" (un array di stringhe in italiano). Basa la tua analisi e il punteggio ESCLUSIVAMENTE sui dati concreti forniti. Ignora completamente eventuali campi o dati mancanti.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                 return ['error' => 'La risposta dell\'Intelligenza Artificiale non è un JSON valido.'];
            }
            
            return array_merge($content, [
                'summary' => $this->fullResults['summary'],
                'raw_data' => $this->fullResults['analysis']
            ]);

        } catch (\Exception $e) {
            Log::error("Errore chiamata OpenAI per valutazione rischio: " . $e->getMessage());
            return ['error' => "Impossibile contattare l'Intelligenza Artificiale per la valutazione.", 'details' => $e->getMessage()];
        }
    }

    protected function buildGptPrompt(array $summarizedData, array $summary): string
    {
        $dataString = json_encode($summarizedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $summaryString = "Analisi eseguita sul dominio principale {$this->originalDomain}. ";
        $summaryString .= "Trovati {$summary['subdomains_found']} sottodomini, di cui {$summary['subdomains_scanned']} sono stati scansionati.";

        return <<<PROMPT
Sulla base di questa analisi completa per il dominio {$this->originalDomain} e i suoi sottodomini, fornisci una stima percentuale del rischio di compromissione entro i prossimi 3 mesi.
Evidenzia i punti più critici trovati nell'intera infrastruttura.

ISTRUZIONI CRITICHE PER LA VALUTAZIONE - SOLO DATI CONCRETI:

PUNTEGGIO DI RISCHIO (0-100) - SISTEMA BILANCIATO:
- Base il punteggio su TUTTI i fattori di sicurezza analizzati, non solo CVE
- Considera TUTTI questi elementi nell'analisi:

VULNERABILITÀ CONCRETE (peso: 40-60%):
  * CVSS 9.0-10.0 (CRITICO): +40-50 punti (SQL Injection, RCE)
  * CVSS 7.0-8.9 (ALTO): +25-35 punti (XSS, Directory Traversal)
  * CVSS 4.0-6.9 (MEDIO): +10-20 punti (Info Disclosure)
  * CVSS 0.1-3.9 (BASSO): +5-10 punti (Minor issues)

SECURITY HEADERS MANCANTI (peso: 15-20%):
  * Mancanza di CSP, X-Frame-Options, HSTS: +10-15 punti
  * Security score < 30%: +5-10 punti aggiuntivi

TECNOLOGIE OBSOLETE (peso: 10-15%):
  * jQuery < 3.0: +5-8 punti
  * Bootstrap < 4.0: +3-5 punti
  * DOCTYPE HTML 4.01/XHTML: +3-5 punti
  * Adobe Flash rilevato: +8-12 punti
  * Meta tag IE compatibility: +2-3 punti

PORTE APERTE (peso: 5-10%):
  * Porte non standard o inusuali: +5-10 punti per porta
  * Servizi esposti senza versioni identificate: +3-5 punti per servizio

CONFIGURAZIONE GENERALE (peso: 5-10%):
  * Mancanza di robots.txt: +2-3 punti
  * Errori SSL/TLS: +5-8 punti
  * Servizi non responsivi: +3-5 punti

FATTORI PROTETTIVI:
  * Cloudflare protection: -15-20 punti
  * HTTPS correttamente configurato: -5-10 punti
  * Security headers presenti: -5-15 punti

CRITICAL POINTS - PROBLEMI DI SICUREZZA IDENTIFICATI:
- Includi TUTTI i problemi di sicurezza identificati nell'analisi
- Formato: "[DOMINIO] Descrizione specifica del problema"
- ESEMPI ACCETTABILI:

VULNERABILITÀ CONCRETE:
  * "[admin.example.com] Apache 2.2.15 - CVE-2011-3192 (DoS vulnerability)"
  * "[blog.example.com] WordPress 4.9.1 - CVE-2018-6389 (DoS vulnerability)"
  * "[ftp.example.com] vsftpd 2.3.4 - backdoor smiley face (CRITICO)"

SECURITY HEADERS MANCANTI:
  * "[example.com] Mancanza di Content Security Policy (CSP)"
  * "[api.example.com] Assenza di X-Frame-Options (rischio clickjacking)"
  * "[shop.example.com] HSTS non configurato (rischio downgrade SSL)"

TECNOLOGIE OBSOLETE:
  * "[example.com] jQuery 1.8.3 utilizzato (versione obsoleta del 2012)"
  * "[blog.example.com] Bootstrap 2.3.2 (versione EOL dal 2013)"
  * "[old.example.com] Adobe Flash rilevato (tecnologia obsoleta e insicura)"

PORTE/SERVIZI ESPOSTI:
  * "[example.com] Porta 21 (FTP) aperta senza crittografia"
  * "[db.example.com] Porta 3306 (MySQL) esposta pubblicamente"
  * "[admin.example.com] Porta 8080 (HTTP-Alt) accessibile esternamente"

CONFIGURAZIONI INSICURE:
  * "[example.com] Certificato SSL scaduto o non valido"
  * "[api.example.com] Servizio non raggiungibile (timeout frequenti)"
  * "[www.example.com] Robots.txt mancante"

REGOLA: Ogni punto critico deve essere verificabile dal cliente. Include dettagli specifici quando disponibili.

{$summaryString}

Ecco i dati raccolti (in forma riassunta per ogni target):
{$dataString}

Fornisci la risposta esclusivamente in formato JSON, con le chiavi "risk_percentage" (numero intero da 0 a 100) e "critical_points" (array di stringhe in italiano che descrivono i punti critici specifici trovati, ciascuno con il formato "[DOMINIO] Descrizione").
PROMPT;
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
                'sample' => array_slice($summary['internal_links'], 0, 15)
            ];
        }
        
        // Riassumi i commenti nel sorgente
        if (isset($summary['source_analysis']['comments']) && is_array($summary['source_analysis']['comments'])) {
            $count = count($summary['source_analysis']['comments']);
            $sample = array_slice($summary['source_analysis']['comments'], 0, 5);
            $sample = array_map(fn($c) => mb_strimwidth(trim($c), 0, 200, "..."), $sample);
            $summary['source_analysis']['comments'] = [
                'count' => $count,
                'sample' => $sample
            ];
        }

        // Tronca i dati testuali lunghi
        foreach (['http_headers', 'robots_txt', 'sitemap_xml'] as $key) {
            if (isset($summary[$key]) && is_string($summary[$key])) {
                $summary[$key] = mb_strimwidth($summary[$key], 0, 2500, "...");
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
            if(!empty($matches[1])){
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
                    $summary['port_scan']['services'][$port]['banner'] = mb_strimwidth($service['banner'], 0, 300, "...");
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