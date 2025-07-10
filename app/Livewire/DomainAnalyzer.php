<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DomainAnalysisService;

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
            }
        } catch (\Exception $e) {
            $this->error = "Si è verificato un errore imprevisto durante l'analisi: " . $e->getMessage();
        } finally {
            $this->analysing = false;
        }
    }

    public function render()
    {
        return view('livewire.domain-analyzer');
    }
}
