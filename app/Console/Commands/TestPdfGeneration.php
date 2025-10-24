<?php

namespace App\Console\Commands;

use App\Services\InvoicePrintService;
use Illuminate\Console\Command;

class TestPdfGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa la generazione del PDF';

    /**
     * Execute the console command.
     */
    public function handle(InvoicePrintService $invoicePrintService)
    {
        $this->info('Avvio test generazione PDF...');

        try {
            $result = $invoicePrintService->testPdfGeneration();

            if ($result) {
                $this->info('✅ Test PDF completato con successo!');
                $this->info('La generazione del PDF funziona correttamente.');
            } else {
                $this->error('❌ Test PDF fallito!');
                $this->error('La generazione del PDF non funziona.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Errore durante il test: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }

        return 0;
    }
} 