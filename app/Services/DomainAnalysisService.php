<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;
use Symfony\Component\Process\Process;

class DomainAnalysisService
{
    protected const TIMEOUT = 20; // Secondi per ogni processo
    protected const MAX_SUBDOMAINS_TO_SCAN = 10; // Limite per evitare timeout e costi eccessivi

    protected ?string $openaiApiKey;
    protected array $fullResults = [];
    protected string $originalDomain;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.key');
    }

    public function analyze(string $domain): array
    {
        $this->originalDomain = $this->sanitizeDomain($domain);
        $this->fullResults['analysis'] = [];
        $this->fullResults['summary'] = [
            'subdomains_found' => 0,
            'subdomains_scanned' => 0,
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

        foreach ($domainsToScan as $currentDomain) {
            $this->fullResults['analysis'][$currentDomain] = $this->analyzeSingleDomain($currentDomain);
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
        $results['http_headers'] = $this->getHttpHeaders($domain);
        $results['robots_txt'] = $this->checkRobotsTxt($domain);
        $results['sitemap_xml'] = $this->checkSitemapXml($domain);
        $results['source_analysis'] = $this->analyzeSourceCode($domain);
        $results['internal_links'] = $this->mapInternalLinks($domain);
        $results['dns_records'] = $this->checkDnsRecords($domain);
        $results['ssl_tls'] = $this->checkSslTls($domain);
        $results['cloudflare_detection'] = $this->detectCloudflare($domain);
        $results['wordpress_analysis'] = $this->analyzeWordPress($domain);
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

    protected function analyzeSourceCode(string $domain): ?array
    {
        $html = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}"]);
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

    protected function mapInternalLinks(string $domain): array
    {
        $html = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}"]);
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

    protected function analyzeWordPress(string $domain): array
    {
        $results = [
            'is_wordpress' => false,
            'version' => null,
            'plugins' => [],
            'themes' => [],
            'indicators' => []
        ];

        // Scarica il contenuto della homepage
        $html = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}"]);
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
            // Estrai la versione di WordPress
            preg_match('/wp-includes\/js\/wp-embed\.min\.js\?ver=([0-9\.]+)/', $html, $matches);
            if (isset($matches[1])) {
                $results['version'] = $matches[1];
            }

            // Trova plugin comuni
            preg_match_all('/wp-content\/plugins\/([^\/]+)/', $html, $pluginMatches);
            if (!empty($pluginMatches[1])) {
                $results['plugins'] = array_unique($pluginMatches[1]);
            }

            // Trova theme
            preg_match_all('/wp-content\/themes\/([^\/]+)/', $html, $themeMatches);
            if (!empty($themeMatches[1])) {
                $results['themes'] = array_unique($themeMatches[1]);
            }

            // Controlla anche wp-json per informazioni aggiuntive
            $wpJson = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$domain}/wp-json/"]);
            if ($wpJson) {
                $jsonData = json_decode($wpJson, true);
                if (isset($jsonData['namespaces'])) {
                    $results['api_namespaces'] = $jsonData['namespaces'];
                }
            }
        }

        return $results;
    }

    protected function performPortScan(string $domain): array
    {
        $results = [
            'scanned_ports' => [],
            'open_ports' => [],
            'services' => []
        ];

        // Porte più comuni da scannerizzare
        $commonPorts = [21, 22, 23, 25, 53, 80, 110, 143, 443, 993, 995, 1433, 3306, 3389, 5432, 8080, 8443, 9000];
        
        $ip = gethostbyname($domain);
        if ($ip === $domain) {
            return $results;
        }

        foreach ($commonPorts as $port) {
            $results['scanned_ports'][] = $port;
            
            $connection = @fsockopen($ip, $port, $errno, $errstr, 3);
            if ($connection) {
                $results['open_ports'][] = $port;
                
                // Banner grabbing
                $banner = $this->grabBanner($connection, $port);
                if ($banner) {
                    $results['services'][$port] = [
                        'banner' => $banner,
                        'service' => $this->identifyService($port, $banner)
                    ];
                }
                
                fclose($connection);
            }
        }

        return $results;
    }

    protected function grabBanner($connection, int $port): ?string
    {
        $banner = '';
        
        // Invia richieste specifiche per protocollo
        switch ($port) {
            case 80:
            case 8080:
                fwrite($connection, "GET / HTTP/1.1\r\nHost: localhost\r\n\r\n");
                break;
            case 443:
            case 8443:
                fwrite($connection, "GET / HTTP/1.1\r\nHost: localhost\r\n\r\n");
                break;
            case 21:
                // FTP non richiede comando iniziale
                break;
            case 22:
                // SSH restituisce banner automaticamente
                break;
            case 25:
                fwrite($connection, "EHLO localhost\r\n");
                break;
            default:
                fwrite($connection, "\r\n");
                break;
        }
        
        // Leggi la risposta
        $timeout = 2;
        $start = time();
        while (time() - $start < $timeout) {
            $data = fread($connection, 1024);
            if ($data === false || $data === '') {
                break;
            }
            $banner .= $data;
            if (strlen($banner) > 2048) { // Limita la dimensione del banner
                break;
            }
        }
        
        return trim($banner) ?: null;
    }

    protected function identifyService(int $port, string $banner): string
    {
        $services = [
            21 => 'FTP',
            22 => 'SSH',
            23 => 'Telnet',
            25 => 'SMTP',
            53 => 'DNS',
            80 => 'HTTP',
            110 => 'POP3',
            143 => 'IMAP',
            443 => 'HTTPS',
            993 => 'IMAPS',
            995 => 'POP3S',
            1433 => 'SQL Server',
            3306 => 'MySQL',
            3389 => 'RDP',
            5432 => 'PostgreSQL',
            8080 => 'HTTP-Alt',
            8443 => 'HTTPS-Alt',
            9000 => 'HTTP-Alt'
        ];

        $baseService = $services[$port] ?? 'Unknown';
        
        // Prova a identificare software specifico dal banner
        $software = $this->identifyServiceSoftware($banner);
        
        return $software ? "{$baseService} ({$software})" : $baseService;
    }

    protected function identifyServiceSoftware(string $banner): ?string
    {
        $patterns = [
            '/Apache\/([0-9\.]+)/' => 'Apache',
            '/nginx\/([0-9\.]+)/' => 'Nginx',
            '/OpenSSH[_\s]([0-9\.]+)/' => 'OpenSSH',
            '/Microsoft-IIS\/([0-9\.]+)/' => 'IIS',
            '/MySQL/' => 'MySQL',
            '/PostgreSQL/' => 'PostgreSQL',
            '/ProFTPD/' => 'ProFTPD',
            '/vsftpd/' => 'vsftpd',
            '/Postfix/' => 'Postfix',
            '/Exim/' => 'Exim'
        ];

        foreach ($patterns as $pattern => $software) {
            if (preg_match($pattern, $banner, $matches)) {
                return isset($matches[1]) ? "{$software} {$matches[1]}" : $software;
            }
        }

        return null;
    }

    protected function analyzeCVE(array $portScanResults): array
    {
        if (!$this->openaiApiKey || empty($portScanResults['services'])) {
            return ['cve_analysis' => 'Nessun servizio trovato o API non disponibile'];
        }

        $services = $portScanResults['services'];
        $serviceInfo = [];

        foreach ($services as $port => $service) {
            $serviceInfo[] = [
                'port' => $port,
                'service' => $service['service'],
                'banner' => substr($service['banner'], 0, 500) // Limita la lunghezza
            ];
        }

        if (empty($serviceInfo)) {
            return ['cve_analysis' => 'Nessun servizio identificato'];
        }

        try {
            $client = OpenAI::client($this->openaiApiKey);
            $prompt = "Analizza i seguenti servizi identificati tramite port scan e banner grabbing. Per ciascun servizio, identifica potenziali CVE o vulnerabilità note basandoti sul software e sulla versione rilevata. Fornisci una risposta in formato JSON con chiave 'vulnerabilities' contenente un array di oggetti con 'port', 'service', 'potential_cves' e 'risk_level'.\n\nServizi trovati:\n" . json_encode($serviceInfo, JSON_PRETTY_PRINT);

            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sei un esperto di cybersecurity specializzato in analisi di vulnerabilità. Analizza i servizi forniti e identifica potenziali CVE basandoti sulle informazioni del banner.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2
            ]);

            $content = json_decode($response->choices[0]->message->content, true);
            return $content ?: ['cve_analysis' => 'Errore nel parsing della risposta AI'];

        } catch (\Exception $e) {
            Log::error("Errore nell'analisi CVE: " . $e->getMessage());
            return ['cve_analysis' => 'Errore durante l\'analisi CVE'];
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

ISTRUZIONI SPECIFICHE PER LA VALUTAZIONE:
1. Se un dominio è protetto da Cloudflare (cloudflare_detection.is_cloudflare = true), riduci il punteggio di rischio del 15-20% poiché Cloudflare offre protezione DDoS e WAF.
2. Se è rilevato WordPress (wordpress_analysis.is_wordpress = true), considera:
   - Versione WordPress obsoleta: aumenta il rischio
   - Plugin identificati: cerca vulnerabilità note per i plugin specifici
   - Temi personalizzati: potrebbero avere vulnerabilità
3. Analizza i risultati del port scan (port_scan):
   - Porte aperte non necessarie aumentano la superficie di attacco
   - Servizi identificati con versioni obsolete sono ad alto rischio
4. Considera l'analisi CVE (cve_analysis):
   - Vulnerabilità identificate aumentano significativamente il rischio
   - Priorità alta per CVE con punteggio CVSS elevato
5. Combina tutti i fattori per una valutazione olistica del rischio

{$summaryString}

Ecco i dati raccolti (in forma riassunta per ogni target):
{$dataString}

Fornisci la risposta esclusivamente in formato JSON, con le chiavi "risk_percentage" (numero intero da 0 a 100) e "critical_points" (array di stringhe in italiano che descrivono i punti critici specifici trovati).
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

        return $summary;
    }
}
