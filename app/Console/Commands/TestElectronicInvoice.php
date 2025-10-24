<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\ElectronicInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestElectronicInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml:test {invoice_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa la generazione e l\'invio della fattura elettronica';

    /**
     * Execute the console command.
     */
    public function handle(ElectronicInvoiceService $electronicService)
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

        $this->info("Avvio test fatturazione elettronica per la fattura {$invoice->invoice_number}...");

        // Test 1: Generazione XML
        $this->info("1. Test generazione XML...");
        try {
            $result = $electronicService->testXmlGeneration();
            
            if ($result) {
                $this->info("✅ Test generazione XML completato con successo!");
            } else {
                $this->error("❌ Test generazione XML fallito!");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Errore nel test generazione XML: ' . $e->getMessage());
            return 1;
        }

        // Test 2: Invio al SDI
        $this->info("2. Test invio al Sistema di Interscambio...");
        try {
            $success = $electronicService->sendToSdi($invoice);
            
            if ($success) {
                $this->info("✅ Fattura elettronica inviata con successo!");
                
                // Verifica che il file XML sia stato creato
                if ($invoice->xml_file_path && Storage::disk('public')->exists($invoice->xml_file_path)) {
                    $this->info("✅ File XML salvato correttamente");
                    $this->info("Percorso file: {$invoice->xml_file_path}");
                    $this->info("URL download: " . asset('storage/' . $invoice->xml_file_path));
                    
                    $fileSize = Storage::disk('public')->size($invoice->xml_file_path);
                    $this->info("Dimensione file: " . number_format($fileSize / 1024, 2) . " KB");
                    
                    // Mostra un preview del contenuto XML
                    $xmlContent = Storage::disk('public')->get($invoice->xml_file_path);
                    $this->info("Preview XML (primi 200 caratteri):");
                    $this->line(substr($xmlContent, 0, 200) . "...");
                    
                } else {
                    $this->error("❌ File XML non trovato!");
                }
                
            } else {
                $this->error("❌ Errore nell'invio della fattura elettronica!");
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Errore durante l\'invio: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info("🎉 Tutti i test completati con successo!");
        return 0;
    }
} 