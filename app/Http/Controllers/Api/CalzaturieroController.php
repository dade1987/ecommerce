<?php

namespace App\Http\Controllers\Api;

use App\Exports\OrdineExport;
use App\Http\Controllers\Controller;
use App\Models\Extractor;
use App\Models\ProcessedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory;
use function Safe\json_decode;
use function Safe\preg_match;
use function Safe\base64_decode;
use function Safe\fopen;
use Throwable;

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
     * - Salva il file e crea un record `ProcessedFile`.
     * - Carica il file su Gemini (inline per audio, Files API per altro).
     * - Chiama Gemini per l'estrazione dei dati.
     * - Aggiorna `ProcessedFile` con successo o fallimento.
     * - Esporta i dati o restituisce il JSON.
     */
    public function processCustomerOrder(Request $request, $slug)
    {
        $extractor = Extractor::where('slug', $slug)->firstOrFail();
        $processedFile = null;
        $locale = $request->input('locale', 'it'); // Default to Italian

        try {
            if (! $request->hasFile('file') && ! $request->input('file_data_url')) {
                return response()->json(['error' => 'Nessun file o dato immagine fornito.'], 400);
            }

            $prompt = $extractor->prompt;
            $model = 'gemini-2.5-flash-preview-05-20';
            $path = '';
            $originalFilename = null;
            $mimeType = '';
            $fullPath = '';
            $base64Data = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalFilename = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $path = $file->store('uploads', 'public');
                $fullPath = storage_path("app/public/{$path}");
            } elseif ($request->has('file_data_url')) {
                $dataUrl = $request->input('file_data_url');
                if (preg_match('/^data:(image\/.+?|audio\/.+?|application\/vnd\.openxmlformats-officedocument\.wordprocessingml\.document);base64,/', $dataUrl, $type)) {
                    $mimeType = $type[1];
                    $base64Data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                    $fileData = base64_decode($base64Data);

                    if ($fileData === false) throw new \Exception('Decodifica base64 fallita.');

                    $extension = explode(';', explode('/', $mimeType)[1])[0] ?? 'bin';
                    $fileName = 'uploads/' . uniqid() . '.' . $extension;
                    Storage::disk('public')->put($fileName, $fileData);
                    $path = $fileName;
                    $fullPath = storage_path("app/public/{$path}");
                    $originalFilename = basename($path);
                } else {
                    return response()->json(['error' => 'Formato data URL non valido o tipo file non supportato (solo immagini o audio).'], 400);
                }
            }

            $processedFile = ProcessedFile::create([
                'extractor_id'      => $extractor->id,
                'original_filename' => $originalFilename,
                'file_path'         => $path,
                'mime_type'         => $mimeType,
                'status'            => 'processing',
            ]);

            $generatePayload = [];
            $isAudio = str_starts_with($mimeType, 'audio/');

            if ($isAudio && $base64Data) {
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
            } elseif ($mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                // Handle DOCX file: convert to text using PhpOffice/PhpWord
                $phpWord = IOFactory::load($fullPath);
                
                $extractedText = '';
                
                // Iterate through sections and extract text
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        $extractedText .= $this->extractTextFromElement($element);
                    }
                }

                // Remove the original temporary DOCX file after extraction
                Storage::disk('public')->delete($path);
                
                $generatePayload = [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            ['inlineData' => [
                                'mimeType' => 'text/plain',
                                'data'  => base64_encode($extractedText),
                            ]],
                        ],
                    ]],
                    'generation_config' => ['response_mime_type' => 'application/json'],
                ];
            } else {
                $multipartResponse = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                    ->attach('file', fopen($fullPath, 'r'), basename($fullPath))
                    ->post("https://generativelanguage.googleapis.com/upload/v1beta/files?key={$this->apiKey}");

                if ($multipartResponse->failed()) {
                    throw new \Exception('Impossibile caricare il file sul servizio AI: ' . $multipartResponse->body());
                }
                $fileInfo = $multipartResponse->json('file');

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
                        if ($status === 'FAILED') throw new \Exception('Upload del file fallito sul servizio AI: ' . $fileStatusResponse->body());
                    }
                    $attempt++;
                    if ($attempt < $maxAttempts) sleep(2);
                } while ($attempt < $maxAttempts);

                if (!$fileIsActive) throw new \Exception('Timeout: Il file non è diventato attivo in tempo.');
                sleep(2);

                $generatePayload = [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            ['fileData' => ['mimeType' => $mimeType, 'fileUri' => $fileInfo['uri']]],
                        ],
                    ]],
                    'generation_config' => ['response_mime_type' => 'application/json'],
                ];
            }

            $geminiResponse = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type'   => 'application/json',
            ])->timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}", $generatePayload);

            if ($geminiResponse->failed()) {
                throw new \Exception('Impossibile generare il risultato dal servizio AI: ' . $geminiResponse->body());
            }

            $candidates = $geminiResponse->json('candidates', []);
            $jsonText = $candidates[0]['content']['parts'][0]['text'] ?? null;
            if (! $jsonText) {
                throw new \Exception('Risposta dal servizio AI non formattata correttamente o vuota.');
            }

            $orderData = json_decode($jsonText, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $processedFile->update([
                    'status' => 'failed',
                    'error_message' => 'JSON non valido dal Servizio AI.',
                    'gemini_response' => ['raw_response' => $jsonText],
                ]);
                return response()->json(['error' => 'Errore di parsing del JSON restituito dal servizio AI.', 'data' => $jsonText], 500);
            }

            $processedFile->update(['status' => 'success', 'gemini_response' => $orderData]);

            if (in_array($extractor->export_format, ['excel', 'csv'])) {
                if (empty($extractor->export_class)) {
                    throw new \Exception('Classe di esportazione non specificata per questo estrattore.');
                }
                $exportClassName = "App\\Exports\\" . $extractor->export_class;
                if (! class_exists($exportClassName)) {
                    throw new \Exception("La classe di esportazione '{$exportClassName}' non esiste.");
                }
                $baseFileName = trans('exports.filename', [], $locale);
                $fileName = $baseFileName . '.' . ($extractor->export_format === 'csv' ? 'csv' : 'xlsx');
                return Excel::download(new $exportClassName($orderData, $locale), $fileName);
            }

            return response()->json($orderData);
        } catch (Throwable $e) {
            Log::error('Errore in processCustomerOrder: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            if ($processedFile) {
                $processedFile->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
            // Non esporre dettagli interni all'utente
            return response()->json([
                'error' => 'Si è verificato un errore durante l\'elaborazione. Contatta il supporto se il problema persiste.',
            ], 500);
        }
    }

    private function extractTextFromElement($element)
    {
        $extractedText = '';

        // Handle specific PhpOffice\PhpWord element types
        if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
            $extractedText .= $element->getText() . "\n";
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $textElement) {
                if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                    $extractedText .= $textElement->getText();
                }
            }
            $extractedText .= "\n";
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $cellElement) {
                        $extractedText .= $this->extractTextFromElement($cellElement);
                    }
                }
            }
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\ListItem) {
            $extractedText .= $element->getText() . "\n";
        }

        return $extractedText;
    }
}
