<?php

namespace Tests\Feature\Registration;

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Registration\Concerns\CreatesRegistrationFixtures;
use Tests\TestCase;

class StudentRegistrationFlowTest extends TestCase
{
    use CreatesRegistrationFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activateRegistrationModule();
    }

    public function test_student_can_create_a_draft_with_history(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $response = $this->actingAs($user)
            ->post(
                route('student.registrations.store'),
                $this->registrationPayload(
                    $level,
                    $program
                )
            );

        $registration = Registration::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $response->assertRedirect(
            route(
                'student.registrations.show',
                $registration
            )
        );

        $this->assertSame(
            Registration::STATUS_DRAFT,
            $registration->status
        );

        $this->assertDatabaseHas(
            'registration_status_history',
            [
                'registration_id' => $registration->id,
                'from_status' => null,
                'to_status' => Registration::STATUS_DRAFT,
                'changed_by' => $user->id,
            ]
        );
    }

    public function test_program_must_belong_to_selected_level(): void
    {
        $user = $this->createVerifiedUser();

        [$firstLevel] = $this->createAcademicPath();
        [, $otherProgram] = $this->createAcademicPath();

        $this->actingAs($user)
            ->post(
                route('student.registrations.store'),
                $this->registrationPayload(
                    $firstLevel,
                    $otherProgram
                )
            )
            ->assertStatus(422);

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_another_user_cannot_access_or_update_registration(): void
    {
        $owner = $this->createVerifiedUser();
        $intruder = $this->createVerifiedUser();

        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $owner,
            $level,
            $program
        );

        $this->actingAs($intruder)
            ->get(route(
                'student.registrations.show',
                $registration
            ))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(
                route(
                    'student.registrations.update',
                    $registration
                ),
                $this->registrationPayload(
                    $level,
                    $program,
                    ['first_name' => 'Intrus']
                )
            )
            ->assertForbidden();

        $this->assertSame(
            'Ali',
            $registration->fresh()->first_name
        );
    }

    public function test_submission_requires_three_required_documents(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $user,
            $level,
            $program
        );

        $this->actingAs($user)
            ->patch(route(
                'student.registrations.submit',
                $registration
            ))
            ->assertSessionHasErrors('documents');

        $this->assertSame(
            Registration::STATUS_DRAFT,
            $registration->fresh()->status
        );
    }

    public function test_student_can_submit_complete_registration(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $user,
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
                $user,
                $type
            );
        }

        $this->actingAs($user)
            ->patch(route(
                'student.registrations.submit',
                $registration
            ))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame(
            Registration::STATUS_SUBMITTED,
            $registration->status
        );

        $this->assertNotNull(
            $registration->submitted_at
        );

        $this->assertDatabaseHas(
            'registration_status_history',
            [
                'registration_id' => $registration->id,
                'from_status' => Registration::STATUS_DRAFT,
                'to_status' => Registration::STATUS_SUBMITTED,
                'changed_by' => $user->id,
            ]
        );

        $this->actingAs($user)
            ->get(route(
                'student.registrations.edit',
                $registration
            ))
            ->assertForbidden();
    }
}
