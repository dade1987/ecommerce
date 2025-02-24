<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use App\Actions\SendEmailJobAction;
use Filament\Notifications\Notification;
use Spatie\QueueableAction\QueueableAction;

class SendBulkEmailAction
{
    use QueueableAction;

    public function execute(array $data, Collection $records)
    {
        $openaiApiKey = env('OPENAI_API_KEY');
        if (! $openaiApiKey) {
            Notification::make()
                ->title('Errore')
                ->body('Chiave API OpenAI non configurata.')
                ->danger()
                ->send();

            return;
        }

        foreach ($records as $record) {
            if (! $record->website) {
                continue;
            }

            // Invia il job in coda utilizzando il metodo onQueue()
            app(SendEmailJobAction::class)
                ->onQueue()
                ->execute($data, $record);
        }

        Notification::make()
            ->title('Successo')
            ->body('Sono state inviate le email per i record selezionati.')
            ->success()
            ->send();
    }
}
