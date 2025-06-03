<?php

namespace App\Http\Controllers\Api;

use App\Exports\OrdineExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use function Safe\json_decode;

class CalzaturieroController extends Controller
{
  private string $apiKey;

  public function __construct()
  {
    $this->apiKey = config('services.gemini.key');

    if (! $this->apiKey) {
      throw new \RuntimeException('Devi impostare GEMINI_API_KEY in config/services.php');
    }
  }

  /**
   * Processa l'ordine del cliente:
   * - Carica il file PDF su Gemini con il Files API.
   * - Chiede a Gemini di estrarre i dati in un JSON pulito.
   * - Converte il JSON in array PHP e lo esporta in Excel.
   */
  public function processCustomerOrder(Request $request)
  {
    if (! $request->hasFile('file')) {
      return response()->json(['error' => 'Nessun file caricato'], 400);
    }

    // 1. Salva localmente il PDF e ottieni il percorso completo
    $file = $request->file('file');
    $path = $file->store('uploads', 'public');
    $fullPath = storage_path("app/public/{$path}");

    // 2. Carica il PDF su Gemini (Files API – upload/v1beta/files) :contentReference[oaicite:0]{index=0}
    //    Usiamo multipart/form-data: il server Gemini restituisce { "file": { "name": "...", "uri": "https://..." } }
    $multipartResponse = Http::withHeaders([
      'x-goog-api-key' => $this->apiKey,
    ])->attach(
      'file',
      fopen($fullPath, 'r'),
      basename($fullPath)
    )->post(
      "https://generativelanguage.googleapis.com/upload/v1beta/files?key={$this->apiKey}"
    );

    if ($multipartResponse->failed()) {
      Log::error('Errore upload PDF su Gemini', [
        'status' => $multipartResponse->status(),
        'body'   => $multipartResponse->body(),
      ]);
      return response()->json(['error' => 'Impossibile caricare il PDF su Gemini.'], 500);
    }

    $fileInfo = $multipartResponse->json('file');
    // $fileInfo['name'] e $fileInfo['uri'] sono disponibili
    $fileUri = $fileInfo['uri'];

    // 3. Prompt aggiornato come richiesto
    $prompt = "Utilizza il file PDF allegato per estrarre i seguenti dati relativi all'ordine di produzione. Rispondi esclusivamente con un JSON valido e pulito, senza alcuna formattazione markdown o blocchi di codice. Se un'informazione non è presente nel file, imposta il valore a \"N/A\". Segui esattamente questo formato:

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
   - È un identificativo assegnato all'ordine, e non deve coincidere con la descrizione dell’articolo, marcatura tecnica o numero di matricola (es. \"183639 / NEW ATOMIC OVER 50B272\" non è un numero ordine).
   - Di solito è presente in etichette o nel corpo della mail con formati come: “SS25-C7709”, “INDUSTRIA”, “The Row”, “YSMPIN 2024 1704”, ecc.
   - Se invece fosse assente, scrivi esattamente `\"N/A\"` e non dedurre valori.

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
     - **quantita_per_taglia**: Registra tutte le taglie coinvolte nell’ordine senza aggiungere taglie precedenti o successive.
     - **note_di_produzione**: Eventuali richieste particolari, note tecniche o modifiche. Se nulla è indicato, lascia stringa vuota (non \"N/A\").

6. **condizioni_pagamento**: Se presenti nel documento, altrimenti \"N/A\".

7. **modalita_spedizione**: Se presenti nel documento, altrimenti \"N/A\".

⚠️ Non aggiungere o indovinare nessuna informazione. Se non trovi un dato, scrivi esattamente `\"N/A\"`.
";

    // 5. Invia la richiesta a Gemini: il campo "response_mime_type" va dentro "generation_config" :contentReference[oaicite:0]{index=0}
    $generatePayload = [
      'contents' => [
        [
          'parts' => [
            ['text' => $prompt],
            ['fileData' => [
              'mimeType' => 'application/pdf',
              'fileUri'  => $fileUri,
            ]],
          ],
        ],
      ],
      'generation_config' => [
        'response_mime_type' => 'application/json'
      ],
    ];

    $geminiResponse = Http::withHeaders([
      'x-goog-api-key' => $this->apiKey,
      'Content-Type'   => 'application/json',
    ])->post(
      "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key={$this->apiKey}",
      $generatePayload
    );

    if ($geminiResponse->failed()) {
      Log::error('Errore durante il generateContent di Gemini', [
        'status' => $geminiResponse->status(),
        'body'   => $geminiResponse->body(),
      ]);
      return response()->json([
        'error' => 'Impossibile generare il JSON da Gemini. Controlla i log per dettagli.'
      ], 500);
    }

    // 6. Estrai il JSON restituito
    $candidates = $geminiResponse->json('candidates', []);
    if (! isset($candidates[0]['content']['parts'][0]['text'])) {
      return response()->json(['error' => 'Risposta Gemini non formattata come JSON.'], 500);
    }
    $jsonText = $candidates[0]['content']['parts'][0]['text'];

    // 7. Decodifica il JSON in un array PHP
    $orderData = json_decode($jsonText, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('JSON non valido da Gemini', ['jsonText' => $jsonText]);
      return response()->json(['error' => 'Errore di parsing del JSON restituito da Gemini.'], 500);
    }

    return response()->json($orderData);

    // 8. Esporta in Excel
    return Excel::download(new OrdineExport($orderData['ordine']), 'ordine.xlsx');
  }
}
