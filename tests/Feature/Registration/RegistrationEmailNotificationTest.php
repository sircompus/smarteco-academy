<?php

namespace Tests\Feature\Registration;

use App\Mail\RegistrationStatusChangedMail;
use App\Mail\RegistrationSubmittedMail;
use App\Models\Registration;
use App\Models\RegistrationEmailLog;
use App\Services\RegistrationEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Registration\Concerns\CreatesRegistrationFixtures;
use Tests\TestCase;

class RegistrationEmailNotificationTest extends TestCase
{
    use CreatesRegistrationFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activateRegistrationModule();
    }

    public function test_submission_sends_confirmation_only_once_and_creates_log(): void
    {
        Mail::fake();

        $student = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $student,
            $level,
            $program
        );

        foreach ([
            'identity',
            'diploma',
            'transcript',
        ] as $type) {
            $this->createDocument(
                $registration,
                $student,
                $type
            );
        }

        $this->actingAs($student)
            ->patch(route(
                'student.registrations.submit',
                $registration
            ))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $registration->refresh();

        Mail::assertSent(
            RegistrationSubmittedMail::class,
            fn (RegistrationSubmittedMail $mail): bool => $mail->hasTo($student->email)
                && $mail->registration->is($registration)
        );

        $log = RegistrationEmailLog::query()
            ->where('registration_id', $registration->id)
            ->where('event_key', 'submitted')
            ->firstOrFail();

        $this->assertSame('submitted', $log->email_type);
        $this->assertSame(
            Registration::STATUS_SUBMITTED,
            $log->status
        );
        $this->assertSame($student->email, $log->recipient);
        $this->assertNotNull($log->sent_at);
        $this->assertNull($log->failed_at);
        $this->assertNull($log->error_message);

        app(RegistrationEmailService::class)
            ->sendSubmitted($registration);

        Mail::assertSent(RegistrationSubmittedMail::class, 1);
        $this->assertDatabaseCount('registration_email_logs', 1);
    }

    public function test_admin_status_changes_send_emails_for_the_four_notifiable_statuses(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();

        $scenarios = [
            [
                Registration::STATUS_SUBMITTED,
                Registration::STATUS_UNDER_REVIEW,
                'Dossier en cours d’examen.',
            ],
            [
                Registration::STATUS_SUBMITTED,
                Registration::STATUS_INCOMPLETE,
                'Une pièce doit être remplacée.',
            ],
            [
                Registration::STATUS_UNDER_REVIEW,
                Registration::STATUS_ACCEPTED,
                'Admission validée.',
            ],
            [
                Registration::STATUS_SUBMITTED,
                Registration::STATUS_REJECTED,
                'Les conditions ne sont pas remplies.',
            ],
        ];

        foreach ($scenarios as [$initialStatus, $newStatus, $comment]) {
            $student = $this->createVerifiedUser();
            [$level, $program] = $this->createAcademicPath();

            $registration = $this->createRegistration(
                $student,
                $level,
                $program,
                ['status' => $initialStatus]
            );

            $this->actingAs($admin)
                ->patch(
                    route(
                        'admin.registrations.status.update',
                        $registration
                    ),
                    [
                        'status' => $newStatus,
                        'comment' => $comment,
                    ]
                )
                ->assertSessionHasNoErrors()
                ->assertSessionHas('success');

            $registration->refresh();

            $this->assertSame($newStatus, $registration->status);

            Mail::assertSent(
                RegistrationStatusChangedMail::class,
                fn (RegistrationStatusChangedMail $mail): bool => $mail->hasTo($student->email)
                    && $mail->registration->is($registration)
                    && $mail->newStatus === $newStatus
            );

            $history = $registration->histories()
                ->where('to_status', $newStatus)
                ->latest('id')
                ->firstOrFail();

            $this->assertDatabaseHas(
                'registration_email_logs',
                [
                    'registration_id' => $registration->id,
                    'status_history_id' => $history->id,
                    'event_key' => 'status_changed:'.$history->id,
                    'email_type' => 'status_changed',
                    'status' => $newStatus,
                    'recipient' => $student->email,
                    'failed_at' => null,
                    'error_message' => null,
                ]
            );

            $this->assertNotNull(
                RegistrationEmailLog::query()
                    ->where('registration_id', $registration->id)
                    ->where('event_key', 'status_changed:'.$history->id)
                    ->firstOrFail()
                    ->sent_at
            );
        }

        Mail::assertSent(
            RegistrationStatusChangedMail::class,
            count($scenarios)
        );

        $this->assertDatabaseCount(
            'registration_email_logs',
            count($scenarios)
        );
    }

    public function test_suspended_status_does_not_send_email_or_create_log(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $student = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $student,
            $level,
            $program,
            ['status' => Registration::STATUS_UNDER_REVIEW]
        );

        $this->actingAs($admin)
            ->patch(
                route(
                    'admin.registrations.status.update',
                    $registration
                ),
                [
                    'status' => Registration::STATUS_SUSPENDED,
                    'comment' => 'Inscription suspendue temporairement.',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame(
            Registration::STATUS_SUSPENDED,
            $registration->fresh()->status
        );

        Mail::assertNothingSent();
        $this->assertDatabaseCount('registration_email_logs', 0);
    }
}
