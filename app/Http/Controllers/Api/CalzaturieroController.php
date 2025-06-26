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

    if (! $request->hasFile('file') && ! $request->input('file_data_url')) {
        return response()->json(['error' => 'Nessun file o dato immagine fornito.'], 400);
    }
    
    $prompt = $extractor->prompt;
    $generatePayload = [];
    // Imposta il modello predefinito. Verrà sovrascritto per l'audio.
    $model = 'gemini-2.5-flash-preview-05-20';
    
    $isInlineAudio = false;
    // Controlla se è un data URL audio e gestiscilo inline
    if ($request->has('file_data_url')) {
        $dataUrl = $request->input('file_data_url');
        // La regex rileva 'audio/*' e cattura il mime type completo
        if (preg_match('/^data:(audio\/.+?);base64,/', $dataUrl, $type)) {
            $isInlineAudio = true;
            $mimeType = $type[1];
            $base64Data = substr($dataUrl, strpos($dataUrl, ',') + 1);
            
            $generatePayload = [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        ['inlineData' => [
                            'mimeType' => $mimeType,
                            'data'  => $base64Data,
                        ]],
                    ],
                ]],
                'generation_config' => ['response_mime_type' => 'application/json'],
            ];
        }
    }
    
    // Se non è audio inline, usa il vecchio metodo con l'API Files
    if (! $isInlineAudio) {
        $fullPath = '';
        $mimeType = '';
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mimeType = $file->getMimeType();
            $path = $file->store('uploads', 'public');
            $fullPath = storage_path("app/public/{$path}");
        } else { // Altrimenti, deve essere un'immagine da data URL
            $dataUrl = $request->input('file_data_url');
            if (!preg_match('/^data:(image\/.+?);base64,/', $dataUrl, $type)) {
                return response()->json(['error' => 'Formato data URL non valido o tipo file non supportato (solo immagini o audio).'], 400);
            }
            $mimeType = $type[1];
            $fileData = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1));

            if ($fileData === false) {
                return response()->json(['error' => 'Decodifica base64 fallita.'], 400);
            }
            
            // Estrai l'estensione in modo sicuro dal mime type
            $extension = explode(';', explode('/', $mimeType)[1])[0];
            $fileName = 'uploads/' . uniqid() . '.' . $extension;
            \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $fileData);
            $fullPath = storage_path("app/public/{$fileName}");
        }
        
        // Carica il file sull'API di Google
        $multipartResponse = Http::withHeaders([
          'x-goog-api-key' => $this->apiKey,
        ])->attach('file', fopen($fullPath, 'r'), basename($fullPath))
          ->post("https://generativelanguage.googleapis.com/upload/v1beta/files?key={$this->apiKey}");

        if ($multipartResponse->failed()) {
          Log::error('Errore upload file su Servizio AI', ['status' => $multipartResponse->status(), 'body'   => $multipartResponse->body()]);
          return response()->json(['error' => 'Impossibile caricare il file sul servizio AI.'], 500);
        }

        $fileInfo = $multipartResponse->json('file');
        
        // Polling per verificare che il file sia attivo
        $fileNameOnGoogle = $fileInfo['name'];
        $maxAttempts = 15;
        $attempt = 0;
        $fileIsActive = false;

        do {
            $fileStatusResponse = Http::withHeaders(['x-goog-api-key' => $this->apiKey])->get("https://generativelanguage.googleapis.com/v1beta/{$fileNameOnGoogle}");
            
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
            if ($attempt < $maxAttempts) sleep(2);
        } while ($attempt < $maxAttempts);

        if (!$fileIsActive) {
            return response()->json(['error' => 'Timeout: Il file non è diventato attivo in tempo.'], 500);
        }
        
        sleep(2); // Pausa per mitigare race condition
        
        $fileUri = $fileInfo['uri'];
        
        $generatePayload = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['fileData' => [
                        'mimeType' => $mimeType,
                        'fileUri'  => $fileUri,
                    ]],
                ],
            ]],
            'generation_config' => ['response_mime_type' => 'application/json'],
        ];
    }
    
    // Esegui la chiamata a generateContent e gestisci la risposta
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

    $candidates = $geminiResponse->json('candidates', []);
    if (! isset($candidates[0]['content']['parts'][0]['text'])) {
      return response()->json(['error' => 'Risposta dal servizio AI non formattata correttamente.'], 500);
    }
    $jsonText = $candidates[0]['content']['parts'][0]['text'];

    $orderData = json_decode($jsonText, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('JSON non valido dal Servizio AI', ['jsonText' => $jsonText]);
      return response()->json(['error' => 'Errore di parsing del JSON restituito dal servizio AI.'], 500);
    }

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
