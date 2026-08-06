<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $statusLabel;

    public function __construct(
        public Registration $registration,
        public string $newStatus
    ) {
        $this->statusLabel = match ($newStatus) {
            'under_review' => 'En examen',
            'incomplete' => 'Dossier incomplet',
            'accepted' => 'Acceptée',
            'rejected' => 'Refusée',
            default => $newStatus,
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Mise à jour de votre inscription : {$this->statusLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.registration-status-changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}