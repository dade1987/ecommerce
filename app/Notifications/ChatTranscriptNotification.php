<?php

namespace App\Notifications;

use App\Models\Quoter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ChatTranscriptNotification extends Notification
{
    use Queueable;

    public function __construct(public string $threadId)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Trascrizione chat - Thread ' . $this->threadId;
        $messages = Quoter::where('thread_id', $this->threadId)
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content', 'created_at']);

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.chat_transcript', [
                'threadId' => $this->threadId,
                'messages' => $messages,
            ]);
    }
}


