<?php

namespace App\Services;

use App\Models\Bom;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use function Safe\json_decode;

class OrderParsingService
{
    private string $apiKey;
    private string $model = 'gemini-1.5-flash';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        if (!$this->apiKey) {
            throw new \RuntimeException('GEMINI_API_KEY non è configurata in config/services.php');
        }
    }

    /**
     * Processa un file d'ordine usando un servizio AI per estrarre i dati.
     *
     * @param UploadedFile $file
     * @return array|null Un array con ['customer' => '...', 'bom' => Bom] o null se non trova corrispondenze.
     * @throws \Exception
     */
    public function parseOrderFromFile(UploadedFile $file): ?array
    {
        try {
            // 1. Carica il file sul servizio AI
            $multipartResponse = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                ->attach('file', $file->getContent(), $file->getClientOriginalName())
                ->post("https://generativelanguage.googleapis.com/upload/v1beta/files?key={$this->apiKey}");

            if ($multipartResponse->failed()) {
                throw new \Exception('Impossibile caricare il file sul servizio AI: ' . $multipartResponse->body());
            }
            $fileInfo = $multipartResponse->json('file');
            $fileNameOnGoogle = $fileInfo['name'];

            // 2. Attendi che il file sia attivo (polling)
            $this->waitForFileToBeActive($fileNameOnGoogle);

            // 3. Prepara ed esegui la chiamata per l'estrazione dei dati
            $prompt = "Sei un assistente intelligente per un'azienda di lamiere forate. Analizza il documento fornito. Estrai il nome del cliente e il codice prodotto interno per l'articolo richiesto. Il codice prodotto è una stringa alfanumerica come 'LAM-ST-R5T8-INOX304' o 'PAN-DEC-Q10-ALU-W'. Rispondi SOLO con un oggetto JSON valido nel seguente formato: {\"customer_name\": \"NOME_CLIENTE_QUI\", \"product_code\": \"CODICE_PRODOTTO_QUI\"}. Se non trovi una delle informazioni, imposta il suo valore su null.";

            $generatePayload = [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        ['fileData' => ['mimeType' => $file->getMimeType(), 'fileUri' => $fileInfo['uri']]],
                    ],
                ]],
                'generation_config' => ['response_mime_type' => 'application/json'],
            ];

            $geminiResponse = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", $generatePayload);

            if ($geminiResponse->failed()) {
                throw new \Exception('Impossibile generare il risultato dal servizio AI: ' . $geminiResponse->body());
            }

            // 4. Processa la risposta
            $candidates = $geminiResponse->json('candidates', []);
            $jsonText = $candidates[0]['content']['parts'][0]['text'] ?? null;
            if (!$jsonText) {
                throw new \Exception('Risposta dal servizio AI non formattata correttamente o vuota.');
            }

            $orderData = json_decode($jsonText, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON non valido dal servizio AI', ['response' => $jsonText]);
                throw new \Exception('JSON non valido ricevuto dal servizio AI.');
            }

            // 5. Cerca la BOM nel database e restituisci il risultato
            $productCode = $orderData['product_code'] ?? null;
            $customerName = $orderData['customer_name'] ?? 'Cliente non specificato';

            if (!$productCode) {
                return null;
            }

            $bom = Bom::where('internal_code', $productCode)->first();

            if (!$bom) {
                return null; // Nessuna corrispondenza trovata per il codice prodotto
            }

            return [
                'customer' => $customerName,
                'bom' => $bom,
            ];
        } catch (Throwable $e) {
            Log::error('Errore in OrderParsingService: ' . $e->getMessage(), ['exception' => $e]);
            // Rilancia l'eccezione per farla gestire dall'Action di Filament (che mostrerà una notifica)
            throw new \Exception('Errore durante l\'analisi del file: ' . $e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    private function waitForFileToBeActive(string $fileNameOnGoogle): void
    {
        $maxAttempts = 15;
        $attempt = 0;
        do {
            $fileStatusResponse = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                ->get("https://generativelanguage.googleapis.com/v1beta/{$fileNameOnGoogle}");

            if ($fileStatusResponse->successful()) {
                $status = $fileStatusResponse->json('state');
                if ($status === 'ACTIVE') {
                    return; // Successo
                }
                if ($status === 'FAILED') {
                    throw new \Exception('Upload del file fallito sul servizio AI: ' . $fileStatusResponse->body());
                }
            }
            $attempt++;
            if ($attempt < $maxAttempts) {
                sleep(2);
            }
        } while ($attempt < $maxAttempts);

        throw new \Exception('Timeout: Il file non è diventato attivo in tempo.');
    }
} 