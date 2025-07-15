<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DomainAnalysisService;
use App\Models\ScannedWebsite;
use Illuminate\Support\Facades\Log;

class DomainAnalyzer extends Component
{
    public ?string $domain = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?array $result = null;
    public bool $analysing = false;
    public ?string $error = null;

    public function analyze(DomainAnalysisService $analysisService)
    {
        $this->validate([
            'domain' => 'required|string|min:3',
            'email' => 'required|email',
            'phone' => 'nullable|string',
        ]);

        $this->analysing = true;
        $this->result = null;
        $this->error = null;

        try {
            set_time_limit(300); // 5 minuti per scansioni complete
            ini_set('max_execution_time', 300);

            $existingScan = ScannedWebsite::where('domain', $this->domain)->first();

            $this->result = $analysisService->analyze($this->domain);

            if (isset($this->result['error'])) {
                $errorMessage = is_array($this->result['error']) ? json_encode($this->result['error']) : $this->result['error'];
                Log::error("Errore ricevuto dal servizio di analisi per {$this->domain}: " . $errorMessage);
                $this->error = true;
            } else {
                if ($existingScan && isset($this->result['risk_percentage'])) {
                    $this->result['risk_percentage'] = $existingScan->risk_percentage;
                }
                
                $this->saveAnalysisResult();
            }
        } catch (\Exception $e) {
            Log::error("Eccezione imprevista durante l'analisi per {$this->domain}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = true;
        } finally {
            $this->analysing = false;
        }
    }

    private function saveAnalysisResult()
    {
        try {
            $ipAddress = gethostbyname($this->domain);
            
            // Prepara i dati estesi per il salvataggio
            $extendedData = [
                'analysis_data' => $this->result['raw_data'] ?? [],
                'cloudflare_protected' => $this->isCloudflareProtected(),
                'wordpress_detected' => $this->isWordPressDetected(),
                'open_ports' => $this->getOpenPorts(),
                'vulnerabilities' => $this->getVulnerabilities(),
                'security_summary' => $this->getSecuritySummary(),
            ];
            
            ScannedWebsite::updateOrCreate(
                ['domain' => $this->domain],
                [
                    'email' => $this->email,
                    'phone_number' => $this->phone,
                    'risk_percentage' => $this->result['risk_percentage'],
                    'critical_points' => $this->result['critical_points'] ?? [],
                    'raw_data' => $extendedData,
                    'ip_address' => $ipAddress !== $this->domain ? $ipAddress : null,
                    'scanned_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Errore nel salvataggio dei dati di scansione per {$this->domain}: " . $e->getMessage());
        }
    }

    private function isCloudflareProtected(): bool
    {
        if (!isset($this->result['raw_data'])) {
            return false;
        }

        foreach ($this->result['raw_data'] as $domain => $data) {
            if (isset($data['cloudflare_detection']['is_cloudflare']) && $data['cloudflare_detection']['is_cloudflare']) {
                return true;
            }
        }

        return false;
    }

    private function isWordPressDetected(): bool
    {
        if (!isset($this->result['raw_data'])) {
            return false;
        }

        foreach ($this->result['raw_data'] as $domain => $data) {
            if (isset($data['wordpress_analysis']['is_wordpress']) && $data['wordpress_analysis']['is_wordpress']) {
                return true;
            }
        }

        return false;
    }

    private function getOpenPorts(): array
    {
        $allPorts = [];
        
        if (!isset($this->result['raw_data'])) {
            return $allPorts;
        }

        foreach ($this->result['raw_data'] as $domain => $data) {
            if (isset($data['port_scan']['open_ports']) && is_array($data['port_scan']['open_ports'])) {
                $allPorts[$domain] = $data['port_scan']['open_ports'];
            }
        }

        return $allPorts;
    }

    private function getVulnerabilities(): array
    {
        $allVulnerabilities = [];
        
        if (!isset($this->result['raw_data'])) {
            return $allVulnerabilities;
        }

        foreach ($this->result['raw_data'] as $domain => $data) {
            if (isset($data['cve_analysis']['vulnerabilities']) && is_array($data['cve_analysis']['vulnerabilities'])) {
                $allVulnerabilities[$domain] = $data['cve_analysis']['vulnerabilities'];
            }
        }

        return $allVulnerabilities;
    }

    private function getSecuritySummary(): array
    {
        return [
            'cloudflare_protected' => $this->isCloudflareProtected(),
            'wordpress_detected' => $this->isWordPressDetected(),
            'total_open_ports' => count($this->getOpenPorts()),
            'has_vulnerabilities' => !empty($this->getVulnerabilities()),
            'analysis_timestamp' => now()->toISOString(),
        ];
    }

    public function render()
    {
        return view('livewire.domain-analyzer');
    }
}
