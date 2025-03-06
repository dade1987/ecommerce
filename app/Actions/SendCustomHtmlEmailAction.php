<?php

namespace App\Actions;

use Illuminate\Support\Facades\Mail;
use Spatie\QueueableAction\QueueableAction;

class SendCustomHtmlEmailAction
{
    use QueueableAction;

    public function execute($customer, string $emailContent): void
    {
        // Sostituisci [UUID] con ?uuid= e il valore della proprietà uuid del customer
        $emailContent = str_replace('[UUID]', '?uuid='.$customer->uuid, $emailContent);

        Mail::send([], [], function ($message) use ($customer, $emailContent) {
            $message->to($customer->email)
                ->subject('Niente più clienti persi all’ora di pranzo (ci pensa EnjoyWork)')
                ->html($emailContent);
        });

        $customer->update(['status' => 'in_contact']);

    }
}
