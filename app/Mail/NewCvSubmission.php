<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCvSubmission extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $cvPath
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: [new Address($this->email, $this->name)],
            subject: 'Nuova Candidatura Ricevuta',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-cv-submission',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('public', $this->cvPath),
        ];
    }
} 