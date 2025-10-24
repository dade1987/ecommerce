<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvoicePrintService
{
    public function print(Invoice $invoice): string
    {
        try {
            // Carica le relazioni necessarie
            $invoice->load(['customer', 'items.productTwins', 'items.internalProduct']);

            // Genera il PDF
            $pdf = Pdf::loadView('pdfs.invoice', [
                'invoice' => $invoice,
                'company' => $this->getCompanyInfo(),
            ]);

            // Imposta le opzioni del PDF
            $pdf->setPaper('a4');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isPhpEnabled', true);
            $pdf->setOption('chroot', public_path());
            $pdf->setOption('tempDir', storage_path('app/temp'));
            $pdf->setOption('logOutputFile', storage_path('logs/dompdf.log'));

            // Genera il nome del file
            $filename = 'fattura_' . $invoice->invoice_number . '_' . date('Y-m-d') . '.pdf';
            $filepath = 'invoices/' . $filename;

            // Assicurati che la directory esista
            $directory = storage_path('app/public/invoices');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Salva il PDF
            Storage::disk('public')->put($filepath, $pdf->output());

            // Aggiorna il percorso nella fattura
            $invoice->update(['pdf_file_path' => $filepath]);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Errore nella generazione del PDF per la fattura ' . $invoice->id . ': ' . $e->getMessage());
            throw new \Exception('Errore nella generazione del PDF: ' . $e->getMessage());
        }
    }

    public function download(Invoice $invoice): string
    {
        $filepath = $this->print($invoice);
        return asset('storage/' . $filepath);
    }

    public function getFilePath(Invoice $invoice): string
    {
        if (!$invoice->pdf_file_path) {
            $this->print($invoice);
        }
        return storage_path('app/public/' . $invoice->pdf_file_path);
    }

    public function testPdfGeneration(): bool
    {
        try {
            // Crea un PDF di test semplice
            $pdf = Pdf::loadView('pdfs.test', [
                'message' => 'Test PDF generato con successo!',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            $pdf->setPaper('a4');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('chroot', public_path());
            $pdf->setOption('tempDir', storage_path('app/temp'));

            // Genera il PDF di test
            $testFilepath = 'test_pdf_' . date('Y-m-d_H-i-s') . '.pdf';
            Storage::disk('public')->put($testFilepath, $pdf->output());

            // Verifica che il file sia stato creato
            if (Storage::disk('public')->exists($testFilepath)) {
                // Rimuovi il file di test
                Storage::disk('public')->delete($testFilepath);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Errore nel test di generazione PDF: ' . $e->getMessage());
            return false;
        }
    }

    private function getCompanyInfo(): array
    {
        return [
            'name' => 'La Tua Azienda SRL',
            'address' => 'Via Roma 123, 12345 Milano',
            'vat_number' => 'IT12345678901',
            'fiscal_code' => '12345678901',
            'phone' => '+39 02 1234567',
            'email' => 'info@tuoazienda.it',
            'website' => 'www.tuoazienda.it',
        ];
    }
} 