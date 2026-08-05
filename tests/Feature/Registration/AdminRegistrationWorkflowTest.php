<?php

namespace Tests\Feature\Registration;

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Registration\Concerns\CreatesRegistrationFixtures;
use Tests\TestCase;

class AdminRegistrationWorkflowTest extends TestCase
{
    use CreatesRegistrationFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activateRegistrationModule();
    }

    public function test_non_admin_cannot_access_admin_registration_area(): void
    {
        $student = $this->createVerifiedUser();

        $this->actingAs($student)
            ->get(route('admin.registrations.index'))
            ->assertForbidden();
    }

    public function test_admin_can_review_submitted_registration(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createVerifiedUser();

        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $student,
            $level,
            $program,
            ['status' => Registration::STATUS_SUBMITTED]
        );

        $this->actingAs($admin)
            ->patch(
                route(
                    'admin.registrations.status.update',
                    $registration
                ),
                [
                    'status' => Registration::STATUS_UNDER_REVIEW,
                    'comment' => 'Dossier en cours d’examen.',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame(
            Registration::STATUS_UNDER_REVIEW,
            $registration->status
        );

        $this->assertSame(
            $admin->id,
            $registration->reviewed_by
        );

        $this->assertNotNull(
            $registration->reviewed_at
        );

        $this->assertDatabaseHas(
            'registration_status_history',
            [
                'registration_id' => $registration->id,
                'from_status' => Registration::STATUS_SUBMITTED,
                'to_status' => Registration::STATUS_UNDER_REVIEW,
                'changed_by' => $admin->id,
            ]
        );
    }

    public function test_negative_status_requires_a_reason(): void
    {
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
                    'status' => Registration::STATUS_INCOMPLETE,
                    'comment' => null,
                ]
            )
            ->assertSessionHasErrors('comment');

        $this->assertSame(
            Registration::STATUS_UNDER_REVIEW,
            $registration->fresh()->status
        );
    }

    public function test_acceptance_requires_all_documents_to_be_verified(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createVerifiedUser();

        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $student,
            $level,
            $program,
            ['status' => Registration::STATUS_UNDER_REVIEW]
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

        $this->actingAs($admin)
            ->patch(
                route(
                    'admin.registrations.status.update',
                    $registration
                ),
                [
                    'status' => Registration::STATUS_ACCEPTED,
                    'comment' => 'Dossier complet.',
                ]
            )
            ->assertSessionHasErrors('status');

        $this->assertSame(
            Registration::STATUS_UNDER_REVIEW,
            $registration->fresh()->status
        );
    }

    public function test_admin_can_verify_documents_and_accept_registration(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createVerifiedUser();

        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $student,
            $level,
            $program,
            ['status' => Registration::STATUS_UNDER_REVIEW]
        );

        $documents = collect([
            'identity',
            'diploma',
            'transcript',
        ])->map(
            fn (string $type) => $this->createDocument(
                $registration,
                $student,
                $type
            )
        );

        foreach ($documents as $document) {
            $this->actingAs($admin)
                ->patch(
                    route(
                        'admin.registrations.documents.verify',
                        $document
                    ),
                    [
                        'is_verified' => 1,
                        'admin_note' => 'Document conforme.',
                    ]
                )
                ->assertSessionHasNoErrors()
                ->assertSessionHas('success');

            $document->refresh();

            $this->assertTrue($document->is_verified);
            $this->assertSame(
                $admin->id,
                $document->verified_by
            );
            $this->assertNotNull(
                $document->verified_at
            );
        }

        $this->actingAs($admin)
            ->patch(
                route(
                    'admin.registrations.status.update',
                    $registration
                ),
                [
                    'status' => Registration::STATUS_ACCEPTED,
                    'comment' => 'Admission validée.',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame(
            Registration::STATUS_ACCEPTED,
            $registration->fresh()->status
        );
    }

    public function test_invalid_status_transition_returns_422(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createVerifiedUser();

        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $student,
            $level,
            $program,
            ['status' => Registration::STATUS_DRAFT]
        );

        $this->actingAs($admin)
            ->patch(
                route(
                    'admin.registrations.status.update',
                    $registration
                ),
                [
                    'status' => Registration::STATUS_ACCEPTED,
                    'comment' => 'Transition interdite.',
                ]
            )
            ->assertStatus(422);

        $this->assertSame(
            Registration::STATUS_DRAFT,
            $registration->fresh()->status
        );
    }
}
