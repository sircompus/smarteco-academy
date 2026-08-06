<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de soumission de votre inscription',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.registration-submitted',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}