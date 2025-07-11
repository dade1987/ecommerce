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
            // Aggiungiamo un timeout generale per sicurezza
            set_time_limit(40); // 30s per l'analisi + 10s di margine

            // Cerca una scansione precedente per questo dominio per preservare la % di rischio
            $existingScan = ScannedWebsite::where('domain', $this->domain)->first();

            $this->result = $analysisService->analyze($this->domain);

            if (isset($this->result['error'])) {
                // Logghiamo l'errore proveniente dal servizio di analisi per il debug
                $errorMessage = is_array($this->result['error']) ? json_encode($this->result['error']) : $this->result['error'];
                Log::error("Errore ricevuto dal servizio di analisi per {$this->domain}: " . $errorMessage);
                // Impostiamo lo stato di errore per la vista (che mostrerà un messaggio generico)
                $this->error = true;
            } else {
                // Se esiste una scansione precedente e l'analisi attuale è andata a buon fine,
                // sovrascriviamo la percentuale di rischio con quella già salvata.
                if ($existingScan && isset($this->result['risk_percentage'])) {
                    $this->result['risk_percentage'] = $existingScan->risk_percentage;
                }
                
                // Salviamo i dati nel database (aggiornando o creando)
                $this->saveAnalysisResult();
            }
        } catch (\Exception $e) {
            // Logghiamo l'eccezione imprevista con lo stack trace per un debug completo
            Log::error("Eccezione imprevista durante l'analisi per {$this->domain}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Impostiamo lo stato di errore per la vista (che mostrerà un messaggio generico)
            $this->error = true;
        } finally {
            $this->analysing = false;
        }
    }

    private function saveAnalysisResult()
    {
        try {
            // Otteniamo l'IP del dominio
            $ipAddress = gethostbyname($this->domain);
            
            ScannedWebsite::updateOrCreate(
                ['domain' => $this->domain], // Criterio per la ricerca
                [ // Valori da aggiornare o creare
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
            // Log dell'errore ma non interrompiamo il flusso
            Log::error("Errore nel salvataggio dei dati di scansione per {$this->domain}: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.domain-analyzer');
    }
}
