<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElectronicInvoiceService
{
    public function sendToSdi(Invoice $invoice): bool
    {
        try {
            Log::info('Inizio processo fatturazione elettronica per fattura: ' . $invoice->invoice_number);
            
            // Carica le relazioni necessarie
            $invoice->load(['customer', 'items.productTwins', 'items.internalProduct']);
            
            Log::info('Relazioni caricate per fattura: ' . $invoice->invoice_number);

            // Verifica che la directory XML esista
            $xmlDirectory = storage_path('app/public/invoices/xml');
            if (!file_exists($xmlDirectory)) {
                Log::info('Creazione directory XML: ' . $xmlDirectory);
                mkdir($xmlDirectory, 0755, true);
            }

            // Genera l'XML della fattura elettronica
            Log::info('Generazione XML per fattura: ' . $invoice->invoice_number);
            $xml = $this->generateXml($invoice);
            
            if (empty($xml)) {
                Log::error('XML generato vuoto per fattura: ' . $invoice->invoice_number);
                return false;
            }
            
            Log::info('XML generato con successo per fattura: ' . $invoice->invoice_number . ' (lunghezza: ' . strlen($xml) . ')');

            // Genera il nome del file
            $filename = 'fattura_' . $invoice->invoice_number . '_' . date('Y-m-d') . '.xml';
            $filepath = 'invoices/xml/' . $filename;
            
            Log::info('Salvataggio XML in: ' . $filepath);

            // Salva l'XML
            $saved = Storage::disk('public')->put($filepath, $xml);
            
            if (!$saved) {
                Log::error('Errore nel salvataggio del file XML: ' . $filepath);
                return false;
            }
            
            Log::info('XML salvato con successo: ' . $filepath);

            // Aggiorna il percorso nella fattura
            $invoice->update(['xml_file_path' => $filepath]);
            Log::info('Percorso XML aggiornato nel database per fattura: ' . $invoice->invoice_number);

            // Simula l'invio al Sistema di Interscambio
            // In produzione, qui andrebbe la vera integrazione con SDI
            Log::info('Invio al SDI per fattura: ' . $invoice->invoice_number);
            $success = $this->sendToSdiEndpoint($xml, $invoice);

            if ($success) {
                Log::info('Fattura elettronica inviata con successo: ' . $invoice->invoice_number);
            } else {
                Log::error('Errore nell\'invio della fattura elettronica: ' . $invoice->invoice_number);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Errore fatturazione elettronica per fattura ' . $invoice->id . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    private function generateXml(Invoice $invoice): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<p:FatturaElettronica versione="FPR12" xmlns:p="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2">';
        
        // Header
        $xml .= '<FatturaElettronicaHeader>';
        $xml .= '<DatiTrasmissione>';
        $xml .= '<IdTrasmittente>';
        $xml .= '<IdPaese>IT</IdPaese>';
        $xml .= '<IdCodice>12345678901</IdCodice>';
        $xml .= '</IdTrasmittente>';
        $xml .= '<ProgressivoInvio>' . $invoice->id . '</ProgressivoInvio>';
        $xml .= '<FormatoTrasmissione>FPR12</FormatoTrasmissione>';
        $xml .= '<CodiceDestinatario>0000000</CodiceDestinatario>';
        $xml .= '<PECDestinatario>' . ($invoice->customer->email ?? '') . '</PECDestinatario>';
        $xml .= '</DatiTrasmissione>';
        
        // Cedente Prestatore
        $xml .= '<CedentePrestatore>';
        $xml .= '<DatiAnagrafici>';
        $xml .= '<IdFiscaleIVA>';
        $xml .= '<IdPaese>IT</IdPaese>';
        $xml .= '<IdCodice>12345678901</IdCodice>';
        $xml .= '</IdFiscaleIVA>';
        $xml .= '<CodiceFiscale>12345678901</CodiceFiscale>';
        $xml .= '<Anagrafica>';
        $xml .= '<Denominazione>La Tua Azienda SRL</Denominazione>';
        $xml .= '</Anagrafica>';
        $xml .= '<RegimeFiscale>RF01</RegimeFiscale>';
        $xml .= '</DatiAnagrafici>';
        $xml .= '<Sede>';
        $xml .= '<Indirizzo>Via Roma 123</Indirizzo>';
        $xml .= '<CAP>12345</CAP>';
        $xml .= '<Comune>Milano</Comune>';
        $xml .= '<Provincia>MI</Provincia>';
        $xml .= '<Nazione>IT</Nazione>';
        $xml .= '</Sede>';
        $xml .= '</CedentePrestatore>';
        
        // Cessionario Committente
        $xml .= '<CessionarioCommittente>';
        $xml .= '<DatiAnagrafici>';
        $xml .= '<CodiceFiscale>' . ($invoice->customer->fiscal_code ?? '00000000000') . '</CodiceFiscale>';
        $xml .= '<Anagrafica>';
        $xml .= '<Denominazione>' . htmlspecialchars($invoice->customer->name, ENT_XML1, 'UTF-8') . '</Denominazione>';
        $xml .= '</Anagrafica>';
        $xml .= '</DatiAnagrafici>';
        $xml .= '<Sede>';
        $xml .= '<Indirizzo>' . htmlspecialchars($invoice->customer->address ?? 'Indirizzo non specificato', ENT_XML1, 'UTF-8') . '</Indirizzo>';
        $xml .= '<CAP>00000</CAP>';
        $xml .= '<Comune>Comune non specificato</Comune>';
        $xml .= '<Provincia>XX</Provincia>';
        $xml .= '<Nazione>IT</Nazione>';
        $xml .= '</Sede>';
        $xml .= '</CessionarioCommittente>';
        $xml .= '</FatturaElettronicaHeader>';
        
        // Body
        $xml .= '<FatturaElettronicaBody>';
        $xml .= '<DatiGenerali>';
        $xml .= '<DatiGeneraliDocumento>';
        $xml .= '<TipoDocumento>TD01</TipoDocumento>';
        $xml .= '<Divisa>EUR</Divisa>';
        $xml .= '<Data>' . $invoice->invoice_date->format('Y-m-d') . '</Data>';
        $xml .= '<Numero>' . $invoice->invoice_number . '</Numero>';
        $xml .= '</DatiGeneraliDocumento>';
        $xml .= '</DatiGenerali>';
        
        // Dettaglio Linee
        foreach ($invoice->items as $item) {
            $xml .= '<DettaglioLinee>';
            $xml .= '<NumeroLinea>' . $item->id . '</NumeroLinea>';
            $xml .= '<Descrizione>' . htmlspecialchars($item->internalProduct->name, ENT_XML1, 'UTF-8') . '</Descrizione>';
            $xml .= '<Quantita>' . $item->quantity . '</Quantita>';
            $xml .= '<PrezzoUnitario>' . number_format($item->unit_price, 2, '.', '') . '</PrezzoUnitario>';
            $xml .= '<PrezzoTotale>' . number_format($item->total_price, 2, '.', '') . '</PrezzoTotale>';
            $xml .= '<AliquotaIVA>22.00</AliquotaIVA>';
            
            // Aggiungi UUID ProductTwin se presenti
            if ($item->productTwins->count() > 0) {
                $xml .= '<AltriDatiGestionali>';
                $xml .= '<TipoDato>ProductTwin_UUID</TipoDato>';
                $xml .= '<RiferimentoTesto>';
                foreach ($item->productTwins as $twin) {
                    $xml .= $twin->uuid . ';';
                }
                $xml .= '</RiferimentoTesto>';
                $xml .= '</AltriDatiGestionali>';
            }
            
            $xml .= '</DettaglioLinee>';
        }
        
        // Dati Riepilogo
        $xml .= '<DatiRiepilogo>';
        $xml .= '<AliquotaIVA>22.00</AliquotaIVA>';
        $xml .= '<ImponibileImporto>' . number_format($invoice->subtotal, 2, '.', '') . '</ImponibileImporto>';
        $xml .= '<Imposta>' . number_format($invoice->tax_amount, 2, '.', '') . '</Imposta>';
        $xml .= '</DatiRiepilogo>';
        
        $xml .= '</FatturaElettronicaBody>';
        $xml .= '</p:FatturaElettronica>';

        return $xml;
    }

    private function sendToSdiEndpoint(string $xml, Invoice $invoice): bool
    {
        // Simula l'invio al Sistema di Interscambio
        // In produzione, qui andrebbe la vera integrazione
        
        try {
            // Per ora, simuliamo sempre un successo
            // In un ambiente reale, qui andrebbe la chiamata HTTP al SDI
            
            // Simula un delay per rendere più realistico
            sleep(1);
            
            // Log della simulazione
            Log::info('Simulazione invio SDI per fattura: ' . $invoice->invoice_number);
            
            // In un ambiente di test, possiamo simulare anche un fallimento casuale
            // per testare la gestione degli errori
            if (config('app.env') === 'testing' && rand(1, 10) === 1) {
                Log::warning('Simulazione fallimento SDI per test');
                return false;
            }
            
            return true;

        } catch (\Exception $e) {
            Log::error('Errore invio SDI per fattura ' . $invoice->invoice_number . ': ' . $e->getMessage());
            return false;
        }
    }

    public function downloadXml(Invoice $invoice): string
    {
        if (!$invoice->xml_file_path) {
            $this->sendToSdi($invoice);
        }
        
        return asset('storage/' . $invoice->xml_file_path);
    }

    public function getXmlFilePath(Invoice $invoice): string
    {
        if (!$invoice->xml_file_path) {
            $this->sendToSdi($invoice);
        }
        
        return storage_path('app/public/' . $invoice->xml_file_path);
    }

    public function testXmlGeneration(): bool
    {
        try {
            // Crea una fattura di test
            $testInvoice = new Invoice();
            $testInvoice->id = 999;
            $testInvoice->invoice_number = 'TEST-2025-000001';
            $testInvoice->invoice_date = now();
            $testInvoice->customer = (object) [
                'name' => 'Cliente Test',
                'email' => 'test@example.com',
                'fiscal_code' => '12345678901',
                'address' => 'Via Test 123'
            ];
            $testInvoice->items = collect([
                (object) [
                    'id' => 1,
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00,
                    'internalProduct' => (object) ['name' => 'Prodotto Test'],
                    'productTwins' => collect()
                ]
            ]);
            $testInvoice->subtotal = 100.00;
            $testInvoice->tax_amount = 22.00;

            // Genera l'XML di test
            $xml = $this->generateXml($testInvoice);
            
            // Verifica che l'XML sia valido
            if (strlen($xml) > 100 && strpos($xml, 'FatturaElettronica') !== false) {
                Log::info('Test generazione XML completato con successo');
                return true;
            } else {
                Log::error('Test generazione XML fallito: XML non valido');
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Errore nel test di generazione XML: ' . $e->getMessage());
            return false;
        }
    }
} 