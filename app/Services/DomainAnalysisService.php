<?php

namespace App\Services;

use App\Events\AnalysisStatusUpdated;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use OpenAI;

class DomainAnalysisService
{
    protected const TIMEOUT = 20; // Secondi per ogni processo
    protected const MAX_SUBDOMAINS_TO_SCAN = 10; // Limite per evitare timeout e costi eccessivi

    protected ?string $openaiApiKey;
    protected ?int $userId;
    protected array $fullResults = [];
    protected string $originalDomain;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.key');
    }

    public function analyze(string $domain, ?int $userId): array
    {
        $this->originalDomain = $this->sanitizeDomain($domain);
        $this->userId = $userId;
        $this->fullResults['analysis'] = [];
        $this->fullResults['summary'] = [
            'subdomains_found' => 0,
            'subdomains_scanned' => 0,
        ];

        $this->updateStatus("Avvio dell'analisi per {$this->originalDomain}...");

        $this->updateStatus('Ricerca dei sottodomini tramite registri pubblici...');
        $subdomains = $this->enumerateSubdomains($this->originalDomain);
        $this->fullResults['summary']['subdomains_found'] = count($subdomains);

        if (empty($subdomains)) {
            $this->updateStatus("Nessun sottodominio trovato. Analizzo solo il dominio principale: {$this->originalDomain}");
            $domainsToScan = [$this->originalDomain];
        } else {
            $this->updateStatus('Selezione dei 5 sottodomini più rilevanti tramite Intelligenza Artificiale...');
            $domainsToScan = $this->selectSubdomainsWithAI($subdomains);
        }
        
        $this->fullResults['summary']['subdomains_scanned'] = count($domainsToScan);
        $this->fullResults['summary']['scanned_targets'] = $domainsToScan;

        $this->updateStatus('Analisi delle vulnerabilità in corso sui target selezionati...');
        foreach ($domainsToScan as $currentDomain) {
            $this->updateStatus("Scansione in corso: {$currentDomain}...");
            $this->fullResults['analysis'][$currentDomain] = $this->analyzeSingleDomain($currentDomain);
        }
        
        $this->updateStatus('Aggregazione dei dati e valutazione finale del rischio...');
        return $this->getRiskScore();
    }
    
    protected function updateStatus(string $message): void
    {
        if ($this->userId) {
            broadcast(new AnalysisStatusUpdated($message, $this->userId));
        }
        Log::info("[Analysis:{$this->originalDomain}] " . $message);
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
Sulla base di questa analisi passiva per il dominio {$this->originalDomain} e i suoi sottodomini, fornisci una stima percentuale del rischio di compromissione entro i prossimi 3 mesi.
Evidenzia i punti più critici trovati nell'intera infrastruttura.

{$summaryString}

Ecco i dati raccolti (in forma riassunta per ogni target):
{$dataString}

Fornisci la risposta esclusivamente in formato JSON, con le chiavi "risk_percentage" e "critical_points".
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

        return $summary;
    }
}
