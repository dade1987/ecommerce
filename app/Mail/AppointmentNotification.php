<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public bool $isDeletion;

    /**
     * Create a new message instance.
     */
    public function __construct(public Appointment $appointment, bool $isDeletion = false)
    {
        $this->isDeletion = $isDeletion;
        $this->appointment->load('customer');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isDeletion ? 'Appuntamento Annullato' : 'Nuovo Appuntamento Creato';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointments.notification',
            with: [
                'customerName' => $this->appointment->customer->name,
                'appointmentDate' => $this->appointment->appointment_date,
                'withPerson' => $this->appointment->with_person,
                'customerPhone' => $this->appointment->customer->phone,
                'isDeletion' => $this->isDeletion,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
