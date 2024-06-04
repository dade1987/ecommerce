<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailNotification extends Notification
{
    use Queueable;

    private $form_data;

    public function __construct(array $form_data)
    {
        $this->form_data = $form_data;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Name: '.$this->form_data['name'])
                    ->line('Email: '.$this->form_data['email'])
                    ->line('Subject: '.$this->form_data['subject'])
                    ->line('Message: '.$this->form_data['message']);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
