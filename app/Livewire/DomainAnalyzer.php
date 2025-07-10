<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DomainAnalysisService;
use App\Models\ScannedWebsite;
use Illuminate\Support\Facades\Log;

class DomainAnalyzer extends Component
{
    public ?string $domain = null;
    public ?array $result = null;
    public bool $analysing = false;
    public ?string $error = null;

    public function analyze(DomainAnalysisService $analysisService)
    {
        $this->validate([
            'domain' => 'required|string|min:3',
        ]);

        $this->analysing = true;
        $this->result = null;
        $this->error = null;

        try {
            // Aggiungiamo un timeout generale per sicurezza
            set_time_limit(40); // 30s per l'analisi + 10s di margine
            $this->result = $analysisService->analyze($this->domain);
            if (isset($this->result['error'])) {
                $this->error = $this->result['error'];
            } else {
                // Salviamo i dati nel database solo se l'analisi è riuscita
                $this->saveAnalysisResult();
            }
        } catch (\Exception $e) {
            $this->error = "Si è verificato un errore imprevisto durante l'analisi: " . $e->getMessage();
        } finally {
            $this->analysing = false;
        }
    }

    private function saveAnalysisResult()
    {
        try {
            // Otteniamo l'IP del dominio
            $ipAddress = gethostbyname($this->domain);
            
            ScannedWebsite::create([
                'domain' => $this->domain,
                'risk_percentage' => $this->result['risk_percentage'],
                'critical_points' => $this->result['critical_points'] ?? [],
                'raw_data' => $this->result['raw_data'] ?? [],
                'ip_address' => $ipAddress !== $this->domain ? $ipAddress : null,
                'scanned_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log dell'errore ma non interrompiamo il flusso
            Log::error("Errore nel salvataggio dei dati di scansione per {$this->domain}: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.domain-analyzer');
    }
}
