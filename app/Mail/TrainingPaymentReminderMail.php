<?php

namespace App\Mail;

use App\Models\TrainingEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrainingPaymentReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public TrainingEnrollment $enrollment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel de paiement — ' . $this->enrollment->training->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.training-payment-reminder',
            with: [
                'studentName' => $this->enrollment->user->name,
                'trainingTitle' => $this->enrollment->training->title,
                'amountDue' => $this->enrollment->current_amount_due,
                'amountPaid' => $this->enrollment->amount_paid,
                'amountRemaining' => $this->enrollment->amount_remaining,
            ],
        );
    }
}
