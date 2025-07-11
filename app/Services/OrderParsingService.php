<?php

namespace App\Services;

use App\Models\Bom;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class OrderParsingService
{
    /**
     * Simula il parsing di un file d'ordine da parte di un'AI.
     *
     * In un'implementazione reale, qui ci sarebbe la logica per:
     * 1. Inviare il file a un servizio AI (es. Gemini).
     * 2. Ricevere una risposta JSON strutturata.
     * 3. Cercare nel database la Distinta Base corrispondente.
     *
     * Per questa POC, simuliamo il risultato basandoci sul nome del file.
     *
     * @param UploadedFile $file
     * @return array|null Un array con ['customer' => '...', 'bom' => Bom] o null se non trova corrispondenze.
     */
    public function parseOrderFromFile(UploadedFile $file): ?array
    {
        $fileName = $file->getClientOriginalName();

        // --- INIZIO LOGICA FITTIZIA (da sostituire con la chiamata AI) ---

        $customer = 'Cliente da File S.r.l.'; // Valore fittizio estratto dall'AI
        $bom = null;

        // Simuliamo la ricerca di una keyword nel testo estratto dall'AI
        if (Str::contains($fileName, 'decorativo', true)) {
            // L'AI ha capito che si tratta del pannello decorativo
            $bom = Bom::where('internal_code', 'PAN-DEC-Q10-ALU-W')->first();
        } elseif (Str::contains($fileName, 'standard', true)) {
            // L'AI ha capito che è il pannello standard
            $bom = Bom::where('internal_code', 'LAM-ST-R5T8-INOX304')->first();
        }

        // --- FINE LOGICA FITTIZIA ---

        if (!$bom) {
            return null; // Nessuna corrispondenza trovata
        }

        return [
            'customer' => $customer,
            'bom' => $bom,
        ];
    }
} 