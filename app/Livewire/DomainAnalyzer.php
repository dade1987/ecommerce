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
            set_time_limit(120);

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
            
            ScannedWebsite::updateOrCreate(
                ['domain' => $this->domain],
                [
                    'email' => $this->email,
                    'phone_number' => $this->phone,
                    'risk_percentage' => $this->result['risk_percentage'],
                    'critical_points' => $this->result['critical_points'] ?? [],
                    'raw_data' => $this->result['raw_data'] ?? [],
                    'ip_address' => $ipAddress !== $this->domain ? $ipAddress : null,
                    'scanned_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Errore nel salvataggio dei dati di scansione per {$this->domain}: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.domain-analyzer');
    }
}
