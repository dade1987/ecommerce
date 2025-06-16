<?php

namespace Database\Seeders;

use App\Models\Extractor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LuceExtractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prompt = <<<PROMPT
Utilizzando il file della bolletta della luce allegato, estrai le seguenti informazioni. Rispondi esclusivamente con un JSON pulito e valido, senza alcuna formattazione aggiuntiva o blocchi di codice. Se un dato non è presente, imposta il suo valore a "N/A".

{
  "bolletta": {
    "fornitore": "stringa",
    "dati_cliente": {
      "nome_cognome": "stringa",
      "codice_fiscale": "stringa",
      "indirizzo_fornitura": "stringa"
    },
    "dati_documento": {
      "numero_bolletta": "stringa",
      "data_emissione": "YYYY-MM-DD",
      "periodo_riferimento": "stringa (es. Gennaio 2024)",
      "data_scadenza": "YYYY-MM-DD"
    },
    "dati_tecnici": {
      "codice_pod": "stringa (es. IT001E12345678)",
      "potenza_impegnata_kw": "numero",
      "potenza_disponibile_kw": "numero"
    },
    "consumi": {
      "consumo_fatturato_kwh": "numero",
      "consumo_fascia_f1_kwh": "numero",
      "consumo_fascia_f2_kwh": "numero",
      "consumo_fascia_f3_kwh": "numero"
    },
    "riepilogo_costi": {
      "totale_da_pagare_eur": "numero",
      "spesa_materia_energia_eur": "numero",
      "spesa_trasporto_gestione_contatore_eur": "numero",
      "spesa_oneri_di_sistema_eur": "numero",
      "imposte_e_iva_eur": "numero",
      "canone_rai_eur": "numero"
    }
  }
}

### Istruzioni Dettagliate:
1.  **fornitore**: Nome della società che emette la bolletta (es. Enel Energia, Servizio Elettrico Nazionale, etc.).
2.  **codice_pod**: Identificativo univoco del punto di prelievo, inizia sempre con "IT".
3.  **consumi**: Riporta i valori numerici dei kWh consumati. Se una fascia non è presente, usa 0.
4.  **riepilogo_costi**: Riporta i valori numerici in euro. Usa il punto come separatore dei decimali. Se il canone RAI non è addebitato, imposta il valore a 0.
5.  **Date**: Usa sempre il formato YYYY-MM-DD.
6.  **Precisione**: Estrai solo i dati presenti. Non calcolare o dedurre valori. Se un'informazione è assente, usa "N/A".
PROMPT;

        Extractor::updateOrCreate(
            ['slug' => 'luce'],
            [
                'prompt' => $prompt,
                'export_format' => 'excel',
                'export_class' => 'BollettaLuceExport',
            ]
        );
    }
}
