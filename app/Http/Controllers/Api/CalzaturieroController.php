<?php

namespace App\Http\Controllers\Api;

use App\Exports\OrdineExport;
use App\Http\Controllers\Controller;
use App\Mail\ProductionReportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use OpenAI;
use OpenAI\Client;
use function Safe\fopen;
use function Safe\json_decode;

class CalzaturieroController extends Controller
{
    public Client $client;

    public function __construct()
    {
        $apiKey = config('openapi.key');
        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Processa l'ordine del cliente:
     * - Invia il file PDF ad OpenAI e chiede a GPT di estrarre le seguenti informazioni:
     *   Nome Cliente, Data Consegna, Destinazione Merce, Quantità per Taglia.
     * - GPT processa il PDF direttamente (usando l'allegato) e restituisce un JSON pulito.
     * - Il report viene inviato via email agli indirizzi indicati.
     */
    public function processCustomerOrder(Request $request)
    {
        if ($request->hasFile('file')) {
            // Crea un nuovo thread per ogni richiesta
            $thread = $this->client->threads()->create([]);
            $threadId = $thread->id;

            // Salva il file e ottieni il percorso completo
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $fullPath = storage_path('app/public/'.$path);
            $fileResource = fopen($fullPath, 'r');

            // Carica il file su OpenAI
            $uploadResponse = $this->client->files()->upload([
                'purpose' => 'assistants',
                'file'    => $fileResource,
            ]);

            // Costruisci il prompt in modo che GPT utilizzi il file PDF allegato per estrarre i dati
            $messageContent = "Utilizza il file PDF allegato per estrarre i seguenti dati relativi all'ordine di produzione. Rispondi esclusivamente con un JSON valido e pulito, senza alcuna formattazione markdown o blocchi di codice. Se un'informazione non è presente nel file, imposta il valore a \"N/A\". Segui esattamente questo formato:

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
        \"quantita_per_taglia\": {
          \"Per ogni taglia presente nell'ordine, riconosci prima se si tratta di taglie italiane, europee o americane. In base al sistema di taglie rilevato, includi la taglia stessa e le 5 precedenti e 5 successive secondo lo standard corretto, mantenendo eventuali mezze taglie nella posizione appropriata (es. 9.5 va tra 9 e 10), impostando quantità 0 se non presenti.\": numero
        }
      }
    ],
    \"condizioni_pagamento\": \"stringa\",
    \"modalita_spedizione\": \"stringa\"
  }
}

### **Istruzioni per l'estrazione:**

1. **numero_ordine**:  
   - È un identificativo assegnato al progetto o ordine, e **non deve coincidere con la descrizione dell’articolo, marcatura tecnica o numero di matricola** (es. \"183639 / NEW ATOMIC OVER 50B272\" non è un numero ordine).
   - Può essere presente in etichette o nel corpo della mail con formati come: “SS25-C7709”, “INDUSTRIA”, “The Row”, “YSMPIN 2024 1704”, ecc.
   - Se nel documento è assente, scrivi esattamente `\"N/A\"` e **non dedurre** valori.

2. **data_ordine**: Se disponibile, estrai la data di emissione dell’ordine in formato \"YYYY-MM-DD\", altrimenti \"N/A\".

3. **cliente**:
   - **nome**: Il nome dell’azienda cliente. Se non disponibile, \"N/A\".
   - **indirizzo_fatturazione**: Se presente, estrai l’indirizzo di fatturazione, altrimenti \"N/A\".
   - **partita_iva**: Se disponibile, estrai la partita IVA, altrimenti \"N/A\".

4. **destinazione_merce**:
   - **indirizzo**: L’indirizzo di consegna della merce. Se non disponibile, \"N/A\".
   - **referente**: Il nome della persona di riferimento. Se non indicato, \"N/A\".

5. **dettagli_articoli** (array):
   - Per ogni articolo nell'ordine, estrai:
     - **data_consegna**: Se disponibile, la data specifica di consegna. Se non c'è, usa la data generale se presente, altrimenti \"N/A\".
     - **calzata**: Larghezza forma (es. D, E, EE, ecc.) o numero. Se non presente, \"N/A\".
     - **matricola**: Codice numerico univoco che segue la descrizione dell’articolo. Se assente, \"N/A\".
     - **descrizione**: Descrizione estesa dell’articolo. Se assente, \"N/A\".
     - **colore**: Se indicato, il colore. Se assente, \"N/A\".
     - **quantita_per_taglia**: Registra tutte le taglie coinvolte nell’ordine e aggiungi 5 taglie precedenti e successive, con quantità 0 se mancanti.
     - **note_di_produzione**: Eventuali richieste particolari, note tecniche o modifiche. Se nulla è indicato, lascia stringa vuota (non \"N/A\").

6. **condizioni_pagamento**: Se presenti nel documento, altrimenti \"N/A\".

7. **modalita_spedizione**: Se presenti nel documento, altrimenti \"N/A\".

⚠️ Non aggiungere o indovinare nessuna informazione. Se non trovi un dato, scrivi esattamente `\"N/A\"`.
";

            // Invia il messaggio a GPT, allegando il file caricato
            $this->client->threads()->messages()->create($threadId, [
                'role'        => 'user',
                'content'     => $messageContent,
                'attachments' => [
                    [
                        'file_id' => $uploadResponse->id,
                        'tools'   => [
                            ['type' => 'file_search'],
                        ],
                    ],
                ],
            ]);

            // Avvia il run del thread con il modello indicato (ad esempio, GPT-4o)
            $run = $this->client->threads()->runs()->create(
                threadId: $threadId,
                parameters: [
                    //'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0', // Sostituisci se necessario
                    'assistant_id' => 'asst_UR3ku4W44oi6O1IcbFn7EY8Y',
                    'model'        => 'gpt-4o',
                    'temperature'  => 0.2,
                ]
            );

            // Attende il completamento del run
            $this->retrieveRunResult($threadId, $run->id);

            // Recupera i messaggi dal thread per ottenere il JSON estratto
            $messages = $this->client->threads()->messages()->list($threadId)->data;
            $jsonResponse = null;
            foreach ($messages as $message) {
                if ($message->role === 'assistant') {
                    $jsonResponse = $message->content[0]->text->value;
                    break;
                }
            }
            if (! $jsonResponse && count($messages) > 0) {
                $lastMessage = end($messages);
                $jsonResponse = $lastMessage->content[0]->text->value;
            }

            Log::info('processCustomerOrder: JSON ricevuto', ['jsonResponse' => $jsonResponse]);

            // Decodifica il JSON in un array associativo PHP
            $orderData = json_decode($jsonResponse, true);

            if ($orderData === null) {
                return response()->json(['error' => 'Errore nell\'estrazione dei dati dal PDF.'], 500);
            }

            //dd($orderData);

            return Excel::download(new OrdineExport($orderData['ordine']), 'ordine.xlsx');

            // Invia il report via email con i dati dell'ordine come contenuto (corpo dell'email)
            /*Mail::to(['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com', 'andrea.tripodi@formificiostf.it'])
                ->send((new ProductionReportMail($orderData))->subject('AI TEST'));

            return response()->json(['message' => 'Documento di produzione inviato via email con successo'], 200);*/
        } else {
            return response()->json(['error' => 'Nessun file caricato'], 400);
        }
    }

    /**
     * Esegue il polling finché il run non risulta completato
     */
    private function retrieveRunResult($threadId, $runId)
    {
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);
            if ($run->status === 'completed') {
                return $run;
            }
            sleep(1);
        }
    }
}
