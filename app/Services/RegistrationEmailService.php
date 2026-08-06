<?php

namespace App\Services;

use App\Mail\RegistrationStatusChangedMail;
use App\Mail\RegistrationSubmittedMail;
use App\Models\Registration;
use App\Models\RegistrationEmailLog;
use App\Models\RegistrationStatusHistory;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use LogicException;
use Throwable;

class RegistrationEmailService
{
    public function sendSubmitted(Registration $registration): void
    {
        $this->sendOnce(
            registration: $registration,
            eventKey: 'submitted',
            emailType: 'submitted',
            status: 'submitted',
            statusHistory: null,
            mailable: new RegistrationSubmittedMail($registration),
        );
    }

    public function sendStatusChanged(
        Registration $registration,
        RegistrationStatusHistory $statusHistory,
        string $newStatus
    ): void {
        $this->sendOnce(
            registration: $registration,
            eventKey: 'status_changed:'.$statusHistory->getKey(),
            emailType: 'status_changed',
            status: $newStatus,
            statusHistory: $statusHistory,
            mailable: new RegistrationStatusChangedMail(
                $registration,
                $newStatus
            ),
        );
    }

    private function sendOnce(
        Registration $registration,
        string $eventKey,
        string $emailType,
        ?string $status,
        ?RegistrationStatusHistory $statusHistory,
        Mailable $mailable
    ): void {
        $registration->loadMissing('user');

        $recipient = $registration->user?->email;

        if (! $recipient) {
            throw new LogicException(
                "Aucune adresse e-mail trouvée pour l'inscription #{$registration->id}."
            );
        }

        $log = RegistrationEmailLog::firstOrCreate(
            [
                'registration_id' => $registration->id,
                'event_key' => $eventKey,
            ],
            [
                'status_history_id' => $statusHistory?->getKey(),
                'email_type' => $emailType,
                'status' => $status,
                'recipient' => $recipient,
            ]
        );

        if (! $log->wasRecentlyCreated) {
            return;
        }

        try {
            Mail::to($recipient)->send($mailable);

            $log->update([
                'sent_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'failed_at' => now(),
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    65535
                ),
            ]);

            throw $exception;
        }
    }
}
