<?php

namespace App\Http\Controllers\Api;

use App\Exports\OrdineExport;
use App\Http\Controllers\Controller;
use App\Models\Extractor;
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
  public function processCustomerOrder(Request $request, $slug)
  {
    $extractor = Extractor::where('slug', $slug)->firstOrFail();

    if (! $request->hasFile('file') && ! $request->has('file_data_url')) {
        return response()->json(['error' => 'Nessun file o dato immagine fornito.'], 400);
    }
    
    if ($request->hasFile('file')) {
        // Gestione tradizionale del file caricato
        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $path = $file->store('uploads', 'public');
        $fullPath = storage_path("app/public/{$path}");
    } else {
        // Gestione del file da data URL (base64)
        $dataUrl = $request->input('file_data_url');
        
        // Estrai il mime type e i dati base64
        if (!preg_match('/^data:(.+);base64,/', $dataUrl, $type)) {
            return response()->json(['error' => 'Formato data URL non valido.'], 400);
        }
        $mimeType = $type[1];

        if (!str_starts_with($mimeType, 'image/') && !str_starts_with($mimeType, 'audio/')) {
            return response()->json(['error' => 'Tipo di file non supportato. Invia solo immagini o audio.'], 400);
        }

        $fileData = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1));

        if ($fileData === false) {
            return response()->json(['error' => 'Decodifica base64 fallita.'], 400);
        }

        // Salva i dati in un file temporaneo
        $fileName = 'uploads/' . uniqid() . '.' . explode('/', $mimeType)[1];
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $fileData);
        $path = $fileName;
        $fullPath = storage_path("app/public/{$path}");
    }

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
      Log::error('Errore upload file su Servizio AI', [
        'status' => $multipartResponse->status(),
        'body'   => $multipartResponse->body(),
      ]);
      return response()->json(['error' => 'Impossibile caricare il file sul servizio AI.'], 500);
    }

    $fileInfo = $multipartResponse->json('file');

    // Polling per verificare lo stato del file
    $fileNameOnGoogle = $fileInfo['name'];
    $maxAttempts = 15; // 30 secondi max
    $attempt = 0;
    $fileIsActive = false;

    do {
        $fileStatusResponse = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ])->get("https://generativelanguage.googleapis.com/v1beta/{$fileNameOnGoogle}");
        
        if ($fileStatusResponse->successful()) {
            $status = $fileStatusResponse->json('state');
            if ($status === 'ACTIVE') {
                $fileIsActive = true;
                break;
            }
            if ($status === 'FAILED') {
                 Log::error('Upload del file fallito sul servizio AI', ['response' => $fileStatusResponse->body()]);
                return response()->json(['error' => 'Il processing del file è fallito.'], 500);
            }
        }
        
        $attempt++;
        if ($attempt < $maxAttempts) {
            sleep(2); // Aspetta 2 secondi prima di riprovare
        }
    } while ($attempt < $maxAttempts);

    if (! $fileIsActive) {
        return response()->json(['error' => 'Timeout: Il file non è diventato attivo in tempo.'], 500);
    }

    // $fileInfo['name'] e $fileInfo['uri'] sono disponibili
    $fileUri = $fileInfo['uri'];

    // 3. Prompt aggiornato come richiesto
    $prompt = $extractor->prompt;

    // 5. Invia la richiesta a Gemini: il campo "response_mime_type" va dentro "generation_config" :contentReference[oaicite:0]{index=0}
    $generatePayload = [
      'contents' => [
        [
          'parts' => [
            ['text' => $prompt],
            ['fileData' => [
              'mimeType' => $mimeType,
              'fileUri'  => $fileInfo['name'],
            ]],
          ],
        ],
      ],
      'generation_config' => [
        'response_mime_type' => 'application/json'
      ],
    ];

    $model = str_starts_with($mimeType, 'audio/') ? 'gemini-1.5-flash-latest' : 'gemini-2.5-flash-preview-05-20';

    $geminiResponse = Http::withHeaders([
      'x-goog-api-key' => $this->apiKey,
      'Content-Type'   => 'application/json',
    ])->post(
      "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}",
      $generatePayload
    );

    if ($geminiResponse->failed()) {
      Log::error('Errore durante la generazione dei contenuti dal Servizio AI', [
        'status' => $geminiResponse->status(),
        'body'   => $geminiResponse->body(),
      ]);
      return response()->json([
        'error' => 'Impossibile generare il risultato dal servizio AI. Controlla i log per dettagli.'
      ], 500);
    }

    // 6. Estrai il JSON restituito
    $candidates = $geminiResponse->json('candidates', []);
    if (! isset($candidates[0]['content']['parts'][0]['text'])) {
      return response()->json(['error' => 'Risposta dal servizio AI non formattata correttamente.'], 500);
    }
    $jsonText = $candidates[0]['content']['parts'][0]['text'];

    // 7. Decodifica il JSON in un array PHP
    $orderData = json_decode($jsonText, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('JSON non valido dal Servizio AI', ['jsonText' => $jsonText]);
      return response()->json(['error' => 'Errore di parsing del JSON restituito dal servizio AI.'], 500);
    }

    // 8. Esporta in base al formato richiesto
    if (in_array($extractor->export_format, ['excel', 'csv'])) {
      if (empty($extractor->export_class)) {
        return response()->json(['error' => 'Classe di esportazione non specificata per questo estrattore.'], 400);
      }

      $exportClassName = "App\\Exports\\" . $extractor->export_class;

      if (! class_exists($exportClassName)) {
        return response()->json(['error' => "La classe di esportazione '{$exportClassName}' non esiste."], 500);
      }
      
      try {
        $fileName = 'ordine.' . ($extractor->export_format === 'csv' ? 'csv' : 'xlsx');
        return Excel::download(new $exportClassName($orderData), $fileName);
      } catch (\Exception $e) {
        Log::error("Errore durante l'esportazione " . strtoupper($extractor->export_format) . ": " . $e->getMessage(), ['data' => $orderData]);
        return response()->json([
            'error' => "Errore durante l'esportazione del file.",
            'message' => $e->getMessage()
        ], 500);
      }
    }

    return response()->json($orderData);
  }
}
