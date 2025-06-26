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
        // Esempio: data:image/png;base64,iVBORw0KGgo...
        if (!preg_match('/^data:(image\/\w+);base64,/', $dataUrl, $type)) {
            return response()->json(['error' => 'Formato data URL non valido.'], 400);
        }
        $mimeType = $type[1];
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
    $prompt = $extractor->prompt;

    // 5. Invia la richiesta a Gemini: il campo "response_mime_type" va dentro "generation_config" :contentReference[oaicite:0]{index=0}
    $generatePayload = [
      'contents' => [
        [
          'parts' => [
            ['text' => $prompt],
            ['fileData' => [
              'mimeType' => $mimeType,
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

    // 8. Esporta in base al formato richiesto
    if ($extractor->export_format === 'excel') {
      if (empty($extractor->export_class)) {
        return response()->json(['error' => 'Classe di esportazione non specificata per questo estrattore.'], 400);
      }

      $exportClassName = "App\\Exports\\" . $extractor->export_class;

      if (! class_exists($exportClassName)) {
        return response()->json(['error' => "La classe di esportazione '{$exportClassName}' non esiste."], 500);
      }
      
      try {
        return Excel::download(new $exportClassName($orderData), 'ordine.xlsx');
      } catch (\Exception $e) {
        Log::error("Errore durante l'esportazione Excel: " . $e->getMessage(), ['data' => $orderData]);
        dd($orderData);
      }
    }

    return response()->json($orderData);
  }
}
