<?php

namespace App\Mail;

use App\Models\PackEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public PackEnrollment $enrollment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel de paiement — ' . $this->enrollment->pack->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-reminder',
            with: [
                'studentName' => $this->enrollment->user->name,
                'packName' => $this->enrollment->pack->name,
                'amountDue' => $this->enrollment->amount_due,
                'amountPaid' => $this->enrollment->amount_paid,
                'amountRemaining' => $this->enrollment->amount_remaining,
            ],
        );
    }
}
