<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoicePrintService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestInvoicePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:invoice {invoice_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa la generazione del PDF di una fattura specifica';

    /**
     * Execute the console command.
     */
    public function handle(InvoicePrintService $invoicePrintService)
    {
        $invoiceId = $this->argument('invoice_id');

        if (!$invoiceId) {
            // Trova la prima fattura disponibile
            $invoice = Invoice::with(['customer', 'items.productTwins', 'items.internalProduct'])->first();
            
            if (!$invoice) {
                $this->error('❌ Nessuna fattura trovata nel database!');
                return 1;
            }
            
            $invoiceId = $invoice->id;
            $this->info("Usando la fattura ID: {$invoiceId}");
        } else {
            $invoice = Invoice::with(['customer', 'items.productTwins', 'items.internalProduct'])->find($invoiceId);
            
            if (!$invoice) {
                $this->error("❌ Fattura con ID {$invoiceId} non trovata!");
                return 1;
            }
        }

        $this->info("Avvio test generazione PDF per la fattura {$invoice->invoice_number}...");

        try {
            $filepath = $invoicePrintService->print($invoice);
            
            $this->info("✅ PDF generato con successo!");
            $this->info("Percorso file: {$filepath}");
            $this->info("URL download: " . asset('storage/' . $filepath));
            
            // Verifica che il file esista
            if (Storage::disk('public')->exists($filepath)) {
                $this->info("✅ File salvato correttamente su disco");
                $fileSize = Storage::disk('public')->size($filepath);
                $this->info("Dimensione file: " . number_format($fileSize / 1024, 2) . " KB");
            } else {
                $this->error("❌ File non trovato su disco!");
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Errore durante la generazione: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
} 