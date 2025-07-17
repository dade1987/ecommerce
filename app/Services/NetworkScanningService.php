<?php

namespace App\Services;

use React\EventLoop\Loop;
use React\Socket\Connector;
use React\Socket\TimeoutConnector;
use React\Stream\WritableResourceStream;
use React\Stream\ReadableResourceStream;
use Illuminate\Support\Facades\Log;
use React\Promise\Promise;
use React\Promise\Deferred;
use OpenAI;

class NetworkScanningService
{
    protected $loop;
    protected $connector;
    protected $timeout;
    protected $results = [];
    protected ?string $openaiApiKey;
    protected string $locale = 'en';

    public function __construct()
    {
        $this->loop = Loop::get();
        $this->timeout = 5; // 5 secondi di timeout (aumentato)
        $this->connector = new TimeoutConnector(
            new Connector([], $this->loop),
            $this->timeout,
            $this->loop
        );
        $this->openaiApiKey = config('services.openai.key');
    }

    /**
     * Esegue port scan su un host specifico
     */
    public function portScan(string $host, ?array $ports = null, string $locale = 'en'): array
    {
        $this->locale = $locale;
        $this->results = [
            'host' => $host,
            'scanned_ports' => [],
            'open_ports' => [],
            'closed_ports' => [],
            'services' => [],
            'scan_duration' => 0
        ];

        if ($ports === null) {
            $ports = $this->getCommonPorts();
        }

        $this->results['scanned_ports'] = $ports;
        $startTime = microtime(true);

        // Risolvi IP dell'host
        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            Log::warning("Impossibile risolvere l'host: {$host}");
            return $this->results;
        }

        $promises = [];
        foreach ($ports as $port) {
            $promises[] = $this->scanPort($ip, $port);
        }

        // Attendi tutti i risultati
        \React\Promise\all($promises)->then(function($results) {
            // I risultati vengono già processati nei callback individuali
        })->otherwise(function($error) {
            Log::error("Errore durante il port scan: " . $error->getMessage());
        });

        // Esegui l'event loop fino a quando tutte le promesse sono completate
        $this->loop->run();

        $this->results['scan_duration'] = round(microtime(true) - $startTime, 2);
        
        return $this->results;
    }

    /**
     * Scansiona una singola porta
     */
    protected function scanPort(string $ip, int $port): Promise
    {
        $deferred = new Deferred();

        $this->connector->connect("tcp://{$ip}:{$port}")
            ->then(function($connection) use ($ip, $port, $deferred) {
                $this->results['open_ports'][] = $port;
                
                // Prova a fare banner grabbing
                $this->grabBanner($connection, $port)
                    ->then(function($banner) use ($port, $deferred) {
                        if ($banner) {
                            $service = $this->identifyService($port, $banner);
                            $this->results['services'][$port] = [
                                'service' => $service,
                                'banner' => $banner,
                                'software' => $this->extractSoftwareInfo($banner)
                            ];
                        }
                        $deferred->resolve($port);
                    })
                    ->otherwise(function($error) use ($port, $deferred) {
                        // Anche se il banner grabbing fallisce, la porta è aperta
                        $deferred->resolve($port);
                    });
                
                $connection->close();
            })
            ->otherwise(function($error) use ($port, $deferred) {
                $this->results['closed_ports'][] = $port;
                $deferred->resolve($port);
            });

        return $deferred->promise();
    }

    /**
     * Esegue banner grabbing su una connessione
     */
    protected function grabBanner($connection, int $port): Promise
    {
        $deferred = new Deferred();
        $banner = '';
        $timeout = 2;

        // Imposta timeout per la lettura del banner
        $timer = $this->loop->addTimer($timeout, function() use ($deferred, $banner) {
            $deferred->resolve($banner);
        });

        // Invia richiesta specifica per protocollo
        $request = $this->getProtocolRequest($port);
        if ($request) {
            $connection->write($request);
        }

        // Leggi la risposta
        $connection->on('data', function($data) use (&$banner, $deferred, $timer) {
            $banner .= $data;
            
            // Limita la dimensione del banner
            if (strlen($banner) > 1024) {
                $banner = substr($banner, 0, 1024);
                $this->loop->cancelTimer($timer);
                $deferred->resolve($banner);
            }
        });

        $connection->on('close', function() use ($deferred, $banner, $timer) {
            $this->loop->cancelTimer($timer);
            $deferred->resolve($banner);
        });

        $connection->on('error', function($error) use ($deferred, $banner, $timer) {
            $this->loop->cancelTimer($timer);
            $deferred->resolve($banner);
        });

        return $deferred->promise();
    }

    /**
     * Restituisce la richiesta appropriata per il protocollo
     */
    protected function getProtocolRequest(int $port): ?string
    {
        switch ($port) {
            case 21: // FTP
                return null; // FTP invia banner automaticamente
            case 22: // SSH
                return null; // SSH invia banner automaticamente
            case 23: // Telnet
                return null; // Telnet invia banner automaticamente
            case 25: // SMTP
                return "EHLO scanner.local\r\n";
            case 53: // DNS
                return null; // DNS non supporta banner grabbing testuale
            case 80: // HTTP
            case 8080:
            case 8000:
            case 8081:
                return "GET / HTTP/1.1\r\nHost: localhost\r\nUser-Agent: NetworkScanner/1.0\r\n\r\n";
            case 110: // POP3
                return "USER test\r\n";
            case 143: // IMAP
                return "a001 CAPABILITY\r\n";
            case 443: // HTTPS
            case 8443:
                return "GET / HTTP/1.1\r\nHost: localhost\r\nUser-Agent: NetworkScanner/1.0\r\n\r\n";
            case 993: // IMAPS
            case 995: // POP3S
                return null; // Richiedono TLS
            case 1433: // SQL Server
                return null; // Protocollo binario
            case 3306: // MySQL
                return null; // MySQL invia banner automaticamente
            case 3389: // RDP
                return null; // Protocollo binario
            case 5432: // PostgreSQL
                return null; // PostgreSQL invia banner automaticamente
            default:
                return "\r\n";
        }
    }

    /**
     * Identifica il servizio dalla porta e dal banner usando GPT
     */
    protected function identifyService(int $port, string $banner): string
    {
        // Mappa base dei servizi per fallback
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
            8000 => 'HTTP-Alt',
            8080 => 'HTTP-Alt',
            8081 => 'HTTP-Alt',
            8443 => 'HTTPS-Alt',
            9000 => 'HTTP-Alt'
        ];

        $baseService = $services[$port] ?? 'Unknown';
        
        // Se non abbiamo un banner valido, restituisci solo il servizio base
        if (empty($banner) || strlen(trim($banner)) < 3) {
            return $baseService;
        }

        // Usa GPT per identificare il software dal banner
        $gptResult = $this->analyzeServiceWithGpt($port, $banner);
        
        return $gptResult ?: $baseService;
    }

    /**
     * Analizza il banner con GPT per identificare il software specifico
     */
    protected function analyzeServiceWithGpt(int $port, string $banner): ?string
    {
        if (!$this->openaiApiKey) {
            Log::warning("OpenAI API key non configurata per l'analisi dei banner");
            return $this->fallbackBannerAnalysis($port, $banner);
        }

        try {
            $client = OpenAI::client($this->openaiApiKey);

            $prompt = $this->buildBannerAnalysisPrompt($port, $banner);

            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => trans('prompts.banner_analysis_system', [], $this->locale)],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
                'max_tokens' => 500, // Limita token per velocità
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Risposta GPT non valida per l'analisi del banner sulla porta {$port}");
                return $this->fallbackBannerAnalysis($port, $banner);
            }

            return $content['service_identification'] ?? $this->fallbackBannerAnalysis($port, $banner);

        } catch (\Exception $e) {
            Log::error("Errore durante l'analisi GPT del banner sulla porta {$port}: " . $e->getMessage());
            return $this->fallbackBannerAnalysis($port, $banner);
        }
    }

    protected function fallbackBannerAnalysis(int $port, string $banner): ?string
    {
        $services = [
            21 => 'FTP',
            22 => 'SSH',
            25 => 'SMTP',
            80 => 'HTTP',
            443 => 'HTTPS',
            3306 => 'MySQL',
            5432 => 'PostgreSQL'
        ];

        $baseService = $services[$port] ?? 'Unknown';
        
        // Prova pattern matching di base per versioni critiche
        $criticalPatterns = [
            '/vsftpd\s*2\.3\.4/i' => 'vsftpd 2.3.4 (backdoor vulnerability)',
            '/apache\/2\.2\.15/i' => 'Apache 2.2.15 (multiple vulnerabilities)',
            '/openssh[_\s]6\.6/i' => 'OpenSSH 6.6 (multiple vulnerabilities)',
            '/mysql.*5\.5/i' => 'MySQL 5.5 (EOL version)',
        ];

        foreach ($criticalPatterns as $pattern => $description) {
            if (preg_match($pattern, $banner)) {
                return $description;
            }
        }

        return $baseService;
    }

    /**
     * Costruisce il prompt per l'analisi del banner con GPT
     */
    protected function buildBannerAnalysisPrompt(int $port, string $banner): string
    {
        return trans('prompts.banner_analysis_user', ['port' => $port, 'banner' => $banner], $this->locale);
    }

    /**
     * Estrae informazioni software dal banner - ora deprecato, usa GPT
     * @deprecated Usa analyzeServiceWithGpt invece
     */
    protected function extractSoftwareInfo(string $banner): ?string
    {
        // Manteniamo questo metodo per compatibilità ma ora usa GPT
        $gptResult = $this->analyzeServiceWithGpt(0, $banner);
        
        if ($gptResult) {
            return $gptResult;
        }

        // Fallback ai pattern esistenti solo se GPT non è disponibile
        $patterns = [
            // Web servers
            '/Server:\s*Apache\/([0-9\.]+)/i' => 'Apache',
            '/Server:\s*nginx\/([0-9\.]+)/i' => 'Nginx',
            '/Server:\s*Microsoft-IIS\/([0-9\.]+)/i' => 'IIS',
            '/Server:\s*lighttpd\/([0-9\.]+)/i' => 'Lighttpd',
            '/Server:\s*([^\r\n]+)/i' => 'Server',
            
            // SSH
            '/SSH-[0-9\.]+-OpenSSH[_\s]([0-9\.]+)/i' => 'OpenSSH',
            '/SSH-[0-9\.]+-([^\r\n\s]+)/i' => 'SSH',
            
            // FTP
            '/220.*vsftpd\s*([0-9\.]+)/i' => 'vsftpd',
            '/220.*ProFTPD\s*([0-9\.]+)/i' => 'ProFTPD',
            '/220.*FileZilla\s*([0-9\.]+)/i' => 'FileZilla',
            '/220.*Pure-FTPd\s*([0-9\.]+)/i' => 'Pure-FTPd',
            '/220.*Microsoft FTP Service/i' => 'Microsoft FTP',
            
            // Mail servers
            '/220.*Postfix/i' => 'Postfix',
            '/220.*Exim\s*([0-9\.]+)/i' => 'Exim',
            '/220.*Microsoft ESMTP MAIL Service/i' => 'Microsoft SMTP',
            '/220.*Sendmail\s*([0-9\.]+)/i' => 'Sendmail',
            
            // Database
            '/MySQL/i' => 'MySQL',
            '/PostgreSQL/i' => 'PostgreSQL',
            '/Microsoft SQL Server/i' => 'SQL Server',
            
            // Others
            '/Telnet/i' => 'Telnet',
            '/HTTP\/1\.[01]\s+\d+\s+([^\r\n]+)/i' => 'HTTP',
        ];

        foreach ($patterns as $pattern => $service) {
            if (preg_match($pattern, $banner, $matches)) {
                if (isset($matches[1]) && $matches[1] !== $service) {
                    return "{$service} {$matches[1]}";
                }
                return $service;
            }
        }

        // Se non troviamo pattern specifici, prova a estrarre info generiche
        $lines = explode("\n", $banner);
        $firstLine = trim($lines[0]);
        
        if (strlen($firstLine) > 3 && strlen($firstLine) < 100) {
            return substr($firstLine, 0, 50);
        }

        return null;
    }

    /**
     * Restituisce le porte più comuni da scansionare
     */
    protected function getCommonPorts(): array
    {
        return [
            21,    // FTP
            22,    // SSH
            23,    // Telnet
            25,    // SMTP
            53,    // DNS
            80,    // HTTP
            110,   // POP3
            143,   // IMAP
            443,   // HTTPS
            993,   // IMAPS
            995,   // POP3S
            1433,  // SQL Server
            3306,  // MySQL
            3389,  // RDP
            5432,  // PostgreSQL
            8000,  // HTTP-Alt
            8080,  // HTTP-Alt
            8081,  // HTTP-Alt
            8443,  // HTTPS-Alt
            9000,  // HTTP-Alt
        ];
    }

    /**
     * Scansiona porte specifiche per WordPress
     */
    public function scanWordPressSpecific(string $host): array
    {
        $wpPorts = [80, 443, 8080, 8443];
        return $this->portScan($host, $wpPorts);
    }

    /**
     * Scansiona porte database comuni
     */
    public function scanDatabasePorts(string $host): array
    {
        $dbPorts = [3306, 5432, 1433, 1521, 27017];
        return $this->portScan($host, $dbPorts);
    }

    /**
     * Scansiona porte mail comuni
     */
    public function scanMailPorts(string $host): array
    {
        $mailPorts = [25, 587, 465, 110, 995, 143, 993];
        return $this->portScan($host, $mailPorts);
    }

    /**
     * Estrae informazioni dettagliate dai banner per l'analisi CVE
     */
    public function extractCVEInfo(array $scanResults): array
    {
        $cveInfo = [];
        
        if (isset($scanResults['services'])) {
            foreach ($scanResults['services'] as $port => $service) {
                $software = $service['software'] ?? null;
                $banner = $service['banner'] ?? '';
                
                if ($software) {
                    $cveInfo[] = [
                        'port' => $port,
                        'service' => $service['service'],
                        'software' => $software,
                        'banner_snippet' => substr($banner, 0, 200),
                        'version' => $this->extractVersion($software),
                        'risk_indicators' => $this->findRiskIndicators($banner)
                    ];
                }
            }
        }
        
        return $cveInfo;
    }

    /**
     * Estrae versione dal software identificato
     */
    protected function extractVersion(string $software): ?string
    {
        if (preg_match('/([0-9]+\.[0-9]+(\.[0-9]+)?)/', $software, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Trova indicatori di rischio nel banner
     */
    protected function findRiskIndicators(string $banner): array
    {
        $indicators = [];
        
        // Versioni obsolete comuni
        $obsoleteVersions = [
            'Apache/2.2' => 'Apache 2.2 obsoleto',
            'Apache/2.4.6' => 'Apache 2.4.6 vulnerabile',
            'nginx/1.10' => 'Nginx 1.10 obsoleto',
            'OpenSSH_6' => 'OpenSSH 6.x obsoleto',
            'OpenSSH_7.4' => 'OpenSSH 7.4 vulnerabile',
            'MySQL 5.5' => 'MySQL 5.5 obsoleto',
            'PHP/5.' => 'PHP 5.x obsoleto',
            'PHP/7.0' => 'PHP 7.0 obsoleto',
            'PHP/7.1' => 'PHP 7.1 obsoleto',
        ];
        
        foreach ($obsoleteVersions as $pattern => $message) {
            if (stripos($banner, $pattern) !== false) {
                $indicators[] = $message;
            }
        }
        
        // Configurazioni insicure
        $insecureConfigs = [
            'Server: Apache' => 'Server header esposto',
            'X-Powered-By: PHP' => 'PHP version disclosure',
            'Server: nginx' => 'Nginx version disclosure',
            'allow_url_include' => 'allow_url_include abilitato',
            'expose_php' => 'expose_php abilitato',
        ];
        
        foreach ($insecureConfigs as $pattern => $message) {
            if (stripos($banner, $pattern) !== false) {
                $indicators[] = $message;
            }
        }
        
        return $indicators;
    }
} 