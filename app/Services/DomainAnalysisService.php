<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;

class DomainAnalysisService
{
    protected string $domain;
    protected array $results = [];
    protected const TIMEOUT = 20; // Secondi per ogni processo

    public function analyze(string $domain): array
    {
        // Sanitize the domain to remove scheme, path, and www.
        $host = parse_url($domain, PHP_URL_HOST);
        if (empty($host)) {
            // Fallback for domains without scheme like 'example.com'
            $host = explode('/', $domain, 2)[0];
        }
        // Remove 'www.' prefix if present
        $this->domain = preg_replace('/^www\./', '', $host);

        $this->results = [];

        // Eseguiamo tutte le analisi.
        // In un'implementazione reale e robusta, useremmo job e code,
        // ma per rispettare il limite di 30s, eseguiamo tutto qui.
        // Per velocizzare, alcune chiamate andrebbero fatte in parallelo.
        $this->getHttpHeaders();
        $this->checkRobotsAndSitemap();
        $this->analyzeSourceCode();
        $this->mapInternalLinks();
        $this->enumerateSubdomains();
        $this->checkDnsRecords();
        $this->checkSslTls();
        $this->queryThreatIntelligence();
        $this->checkForLeaks();
        
        // Alla fine, aggreghiamo i risultati e chiediamo il punteggio a GPT
        return $this->getRiskScore();
    }

    protected function runProcess(array $command): ?string
    {
        $process = new Process($command);
        $process->setTimeout(self::TIMEOUT);

        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (\Exception $e) {
            Log::error("Errore durante l'esecuzione del processo per {$this->domain}: " . $e->getMessage());
            return null;
        }
    }

    protected function getHttpHeaders()
    {
        $output = $this->runProcess(['curl', '-I', '--location', '--connect-timeout', '5', $this->domain]);
        $this->results['http_headers'] = $output ?: null;
    }

    protected function checkRobotsAndSitemap()
    {
        $robotsOutput = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$this->domain}/robots.txt"]);
        $this->results['robots_txt'] = $robotsOutput ?: null;

        $sitemapOutput = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$this->domain}/sitemap.xml"]);
        $this->results['sitemap_xml'] = $sitemapOutput ?: null;
    }

    protected function analyzeSourceCode()
    {
        $html = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$this->domain}"]);
        if (!$html) {
            $this->results['source_analysis'] = null;
            return;
        }

        // Cerchiamo commenti e librerie comuni
        preg_match_all('/<!--(.*?)-->/s', $html, $comments);
        preg_match_all('/(jquery|react|vue|angular|bootstrap)[\-\.]?([0-9\.]+)/i', $html, $libs);

        $this->results['source_analysis'] = [
            'comments' => $comments[1] ?? [],
            'libraries' => array_unique($libs[0]) ?? [],
        ];
    }

    protected function mapInternalLinks()
    {
        $html = $this->runProcess(['curl', '--location', '--connect-timeout', '5', "https://{$this->domain}"]);
        if (!$html) {
            $this->results['internal_links'] = [];
            return;
        }

        preg_match_all('/<a\s+(?:[^>]*?\s+)?href="((?:https?:\/\/' . preg_quote($this->domain) . '|(?!\/\/|\#))[^"]*)"/i', $html, $matches);
        $this->results['internal_links'] = !empty($matches[1]) ? array_unique($matches[1]) : [];
    }

    protected function enumerateSubdomains()
    {
        try {
            // Usiamo crt.sh per la ricerca di sottodomini
            $response = Http::timeout(self::TIMEOUT)->get("https://crt.sh/?q={$this->domain}&output=json");

            if ($response->successful() && $response->json()) {
                $subdomains = collect($response->json())->pluck('name_value')->unique()->values()->all();
                $this->results['subdomains'] = $subdomains;
            } else {
                $this->results['subdomains'] = [];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("Timeout durante la richiesta a crt.sh per il dominio {$this->domain}: " . $e->getMessage());
            $this->results['subdomains'] = [];
        }
    }

    protected function checkDnsRecords()
    {
        $dnsTypes = ['A', 'MX', 'TXT', 'NS'];
        $dnsResults = [];
        foreach ($dnsTypes as $type) {
            $output = $this->runProcess(['dig', '+short', $this->domain, $type]);
            $dnsResults[$type] = $output ? explode("\n", trim($output)) : [];
        }
        $this->results['dns_records'] = $dnsResults;
    }

    protected function checkSslTls()
    {
        // Questo è un controllo molto basilare, non sostituisce un'analisi completa.
        $command = "echo | openssl s_client -servername {$this->domain} -connect {$this->domain}:443 2>/dev/null | openssl x509 -noout -text";
        $output = $this->runProcess(['bash', '-c', $command]);
        $this->results['ssl_tls'] = $output ?: null;
    }

    protected function queryThreatIntelligence()
    {
        // Placeholder per Shodan, Censys, AlienVault
        // Queste richiederebbero chiavi API configurate in config/services.php
        $this->results['threat_intelligence'] = null;
    }

    protected function checkForLeaks()
    {
        // Placeholder per HaveIBeenPwned, GitHub
        // Richiedono chiavi API
        $this->results['leaks_breaches'] = null;
    }

    protected function getRiskScore(): array
    {
        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return ['error' => 'La chiave API di OpenAI non è configurata.'];
        }

        // Riassumiamo i dati prima di inviarli a GPT per evitare di superare il limite di token.
        $summarizedResults = $this->summarizeResultsForGpt($this->results);
        $prompt = $this->buildGptPrompt($summarizedResults);

        try {
            $response = Http::withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sei un esperto di cybersecurity. Analizza i dati forniti e restituisci SOLO un oggetto JSON con due chiavi: "risk_percentage" e "critical_points" (un array di stringhe in italiano). Basa la tua analisi e il punteggio ESCLUSIVAMENTE sui dati concreti forniti (header, DNS, sorgente pagina). Ignora completamente eventuali campi o dati mancanti. Importante: la "Mancanza di integrazioni con strumenti di threat intelligence" o simili non è un punto critico e non deve essere menzionata.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'], // Forziamo una risposta JSON valida
            ]);

            if ($response->failed()) {
                 return ['error' => 'Chiamata API a OpenAI fallita.', 'details' => $response->json()];
            }
            
            $content = json_decode($response->json()['choices'][0]['message']['content'], true);

            return [
                'risk_percentage' => $content['risk_percentage'] ?? 'N/A',
                'critical_points' => $content['critical_points'] ?? ['Analisi non riuscita.'],
                'raw_data' => $this->results, // Manteniamo i dati grezzi per il debug
            ];

        } catch (\Exception $e) {
            Log::error("Errore chiamata OpenAI: " . $e->getMessage());
            return ['error' => "Impossibile contattare l'API di OpenAI.", 'details' => $e->getMessage()];
        }
    }

    protected function buildGptPrompt(array $summarizedData): string
    {
        $dataString = json_encode($summarizedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return <<<PROMPT
Sulla base di questa analisi passiva puntuale per il dominio {$this->domain}, fornisci una stima percentuale del rischio di compromissione entro i prossimi 3 mesi.
Evidenzia i punti più critici trovati.

Ecco i dati raccolti (in forma riassunta):
{$dataString}

Fornisci la risposta esclusivamente in formato JSON, con le chiavi "risk_percentage" e "critical_points".
PROMPT;
    }

    protected function summarizeResultsForGpt(array $results): array
    {
        $summary = $results;

        // Riassumi i sottodomini
        if (isset($summary['subdomains']) && is_array($summary['subdomains'])) {
            $count = count($summary['subdomains']);
            $summary['subdomains'] = [
                'count' => $count,
                'sample' => array_slice($summary['subdomains'], 0, 15)
            ];
        }

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
