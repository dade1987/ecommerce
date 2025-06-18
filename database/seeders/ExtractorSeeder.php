<?php

namespace Database\Seeders;

use App\Models\Extractor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Extractor::updateOrCreate(
            ['slug' => 'tripodi'],
            ['prompt' => "Utilizza il file PDF allegato per estrarre i seguenti dati relativi all'ordine di produzione. Rispondi esclusivamente con un JSON valido e pulito, senza alcuna formattazione markdown o blocchi di codice. Se un'informazione non è presente nel file, imposta il valore a \"N/A\". Segui esattamente questo formato:

{
  \"ordine\": {
    \"numero_ordine\": \"stringa\",
    \"data_ordine\": \"YYYY-MM-DD\",
    \"cliente\": {
      \"nome\": \"stringa\",
      \"indirizzo_fatturazione\": \"stringa\",
      \"partita_iva\": \"stringa\"
    },
    \"destinazione_merce\": {
      \"indirizzo\": \"stringa\",
      \"referente\": \"stringa\"
    },
    \"dettagli_articoli\": [
      {
        \"data_consegna\": \"YYYY-MM-DD\",
        \"note_di_produzione\": \"stringa\",
        \"matricola\": \"numero\", 
        \"descrizione\": \"stringa\",
        \"calzata\": \"stringa\",
        \"colore\": \"stringa\",
        \"quantita_per_taglia\": [
          {
            \"taglia\": \"stringa\",
            \"quantita\": numero
          }
        ]
      }
    ],
    \"condizioni_pagamento\": \"stringa\",
    \"modalita_spedizione\": \"stringa\"
  }
}

### **Istruzioni per l'estrazione:**

1. **numero_ordine**:  
   - È un identificativo assegnato all'ordine, e non deve coincidere con la descrizione dell'articolo, marcatura tecnica o numero di matricola (es. \"183639 / NEW ATOMIC OVER 50B272\" non è un numero ordine).
   - Di solito è presente in etichette o nel corpo della mail con formati come: \"SS25-C7709\", \"INDUSTRIA\", \"The Row\", \"YSMPIN 2024 1704\", ecc.
   - Se invece fosse assente, scrivi esattamente `\"N/A\"` e non dedurre valori.

2. **data_ordine**: Se disponibile, estrai la data di emissione dell'ordine in formato \"YYYY-MM-DD\", altrimenti \"N/A\".

3. **cliente**:
   - **nome**: Il nome dell'azienda cliente. Se non disponibile, \"N/A\".
   - **indirizzo_fatturazione**: Se presente, estrai l'indirizzo di fatturazione, altrimenti \"N/A\".
   - **partita_iva**: Se disponibile, estrai la partita IVA, altrimenti \"N/A\".

4. **destinazione_merce**:
   - **indirizzo**: L'indirizzo di consegna della merce. Se non disponibile, \"N/A\".
   - **referente**: Il nome della persona di riferimento. Se non indicato, \"N/A\".

5. **dettagli_articoli** (array):
   - Per ogni articolo nell'ordine, estrai:
     - **data_consegna**: Se disponibile, la data specifica di consegna. Se non c'è, usa la data generale se presente, altrimenti \"N/A\".
     - **calzata**: Larghezza forma (es. D, E, EE, ecc.) o numero. Se non presente, \"N/A\".
     - **matricola**: Codice numerico univoco che segue la descrizione dell'articolo. Se assente, \"N/A\".
     - **descrizione**: Descrizione estesa dell'articolo. Se assente, \"N/A\".
     - **colore**: Se indicato, il colore. Se assente, \"N/A\".
     - **quantita_per_taglia**: Registra tutte le taglie coinvolte nell'ordine senza aggiungere taglie precedenti o successive.
     - **note_di_produzione**: Eventuali richieste particolari, note tecniche o modifiche. Se nulla è indicato, lascia stringa vuota (non \"N/A\").

6. **condizioni_pagamento**: Se presenti nel documento, altrimenti \"N/A\".

7. **modalita_spedizione**: Se presenti nel documento, altrimenti \"N/A\".

⚠️ Non aggiungere o indovinare nessuna informazione. Se non trovi un dato, scrivi esattamente `\"N/A\"`.
"]
        );
    }
}
