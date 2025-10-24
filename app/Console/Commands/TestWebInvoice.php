<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\ElectronicInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestWebInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'web:invoice {invoice_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula l\'invio della fattura elettronica dall\'interfaccia web';

    /**
     * Execute the console command.
     */
    public function handle()
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

        $this->info("Simulazione invio fattura elettronica per: {$invoice->invoice_number}");

        try {
            // Simula esattamente quello che fa la pagina Filament
            $electronicService = app(\App\Services\ElectronicInvoiceService::class);
            $success = $electronicService->sendToSdi($invoice);

            if ($success) {
                $invoice->update(['status' => 'issued']);
                
                $this->info("✅ Fattura elettronica inviata con successo!");
                $this->info("XML generato e inviato al Sistema di Interscambio.");
                $this->info("Stato fattura aggiornato a: issued");
                
                if ($invoice->xml_file_path) {
                    $this->info("File XML: {$invoice->xml_file_path}");
                }
                
            } else {
                $this->error("❌ Errore nell'invio della fattura elettronica!");
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Errore fatturazione elettronica: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
} 