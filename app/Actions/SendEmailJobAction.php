<?php

namespace App\Actions;

use App\Models\SentMessage;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use function Safe\json_decode;
use Spatie\QueueableAction\QueueableAction;

class SendEmailJobAction
{
    use QueueableAction;

    public function execute(array $data, $record)
    {
        // Recupera il contenuto del sito
        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '.
                    'AppleWebKit/537.36 (KHTML, like Gecko) '.
                    'Chrome/115.0.0.0 Safari/537.36',
            ],
            'timeout' => 30,
        ]);

        try {
            $response = $client->get($record->website);
            $html = $response->getBody()->getContents();
        } catch (\Exception $e) {
            Log::error('Errore durante il recupero del sito web per '.$record->website.': '.$e->getMessage());

            return;
        }

        // Estrai il testo dal body del sito
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $body = $dom->getElementsByTagName('body')->item(0);
        $textContent = $body ? strip_tags($dom->saveHTML($body)) : '';

        // Sostituisci [UUID] con ?uuid=$record->uuid nell'email prompt
        $emailPrompt = str_replace('[UUID]', '?uuid='.$record->uuid, $data['email_prompt']);

        $combinedPrompt = "Leggi il seguente testo estratto dal sito web del cliente e riassumi in poche frasi cosa fa il cliente:\n\n"
            .$textContent.
            "\n\nUtilizza questo riassunto per generare un'email senza oggetto seguendo questo schema:\n\n"
            ."Buongiorno,\n\n"
            ."sono Davide Cavallini.\n\n"
            ."Ho visitato il vostro sito e mi ha colpito [inserisci qui tre punti salienti dal riassunto].\n\n"
            ."Credo che potreste avvantaggiarvi di\n\n"
            .'[descrivi in tre righe il seguente prodotto: '.$emailPrompt."]\n\n"
            ."per motivi che stanno nel riassunto.\n\n"
            ."Ecco il link per la demo: LINK DIRETTO SENZA PARENTESI.\n\n"
            ."Grazie e cordiali saluti,\n"
            .'Davide';

        // Chiamata a OpenAI per generare il contenuto dell'email
        try {
            $payload = [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sei un assistente che genera email basate su un prompt fornito.'],
                    ['role' => 'user', 'content' => $combinedPrompt],
                ],
                'max_tokens' => 1024,
            ];
            $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';
            $apiResponse = $client->post($openaiEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);
            $apiResult = json_decode($apiResponse->getBody()->getContents(), true);
            $emailContent = $apiResult['choices'][0]['message']['content'] ?? 'Nessun risultato';
            $emailContent = nl2br($emailContent);
        } catch (\Exception $e) {
            Log::error('Errore durante la generazione dell\'email per '.$record->email.': '.$e->getMessage());

            return;
        }

        // Invia l'email, aggiorna lo status e logga il messaggio
        try {
            Mail::html($emailContent, function ($message) use ($record) {
                $message->to($record->email)
                    ->subject('C\'è una cosa interessante nel tuo sito internet.');
            });
            $record->update(['status' => 'in_contact']);
            SentMessage::create([
                'message' => $emailContent,
                'contact' => $record->email,
                'type'    => 'email',
            ]);

            Notification::make()
                ->title('Successo')
                ->body('Email inviata correttamente a '.$record->email)
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Errore nell\'invio dell\'email a '.$record->email.': '.$e->getMessage());
            Notification::make()
                ->title('Errore')
                ->body('Errore nell\'invio dell\'email a '.$record->email.': '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
