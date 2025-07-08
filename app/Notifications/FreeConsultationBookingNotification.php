<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FreeConsultationBookingNotification extends Notification
{
    use Queueable;

    protected $details;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = new MailMessage();
        $mailMessage->subject($this->details['subject'] ?? 'ND')
            ->line($this->details['message'] ?? 'ND')
            ->line('Nome: ' . ($this->details['name'] ?? 'ND'))
            ->line('Telefono: ' . ($this->details['telephone_number'] ?? 'ND'))
            ->line('Inizio: ' . ($this->details['starts_at'] ?? 'ND'))
            ->line('Fine: ' . ($this->details['ends_at'] ?? 'ND'));

        if (!empty($this->details['request_notes'])) {
            $mailMessage->line('Note sulla richiesta: ' . ($this->details['request_notes'] ?? 'ND'));
        }

        $mailMessage->line('Grazie per aver utilizzato il nostro servizio!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'name' => $this->details['name'],
            'starts_at' => $this->details['starts_at'],
            'ends_at' => $this->details['ends_at'],
        ];
    }
}
