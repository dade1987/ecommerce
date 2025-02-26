<?php

namespace App\Actions;

use Illuminate\Support\Facades\Mail;
use Spatie\QueueableAction\QueueableAction;

class SendCustomHtmlEmailAction
{
    use QueueableAction;

    public function execute($customer, string $emailContent): void
    {
        Mail::send([], [], function ($message) use ($customer, $emailContent) {
            $message->to($customer->email)
                ->subject('Fai interagire i Consumatori con il tuo Sommelier AI')
                ->html($emailContent);
        });
    }
}
