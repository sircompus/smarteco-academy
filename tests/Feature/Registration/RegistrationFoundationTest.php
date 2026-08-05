<?php

namespace Tests\Feature\Registration;

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Registration\Concerns\CreatesRegistrationFixtures;
use Tests\TestCase;

class RegistrationFoundationTest extends TestCase
{
    use CreatesRegistrationFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activateRegistrationModule();
    }

    public function test_registration_generates_identity_and_relations(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $user,
            $level,
            $program
        );

        $this->assertTrue(
            Str::isUuid($registration->uuid)
        );

        $this->assertStringStartsWith(
            'REG-'.now()->format('Y').'-',
            $registration->reference
        );

        $this->assertTrue(
            $registration->user->is($user)
        );

        $this->assertTrue(
            $registration->level->is($level)
        );

        $this->assertTrue(
            $registration->program->is($program)
        );
    }

    public function test_editability_matches_registration_status(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $draft = $this->createRegistration(
            $user,
            $level,
            $program
        );

        $this->assertTrue($draft->canBeEdited());
        $this->assertTrue($draft->canBeSubmitted());

        $draft->update([
            'status' => Registration::STATUS_INCOMPLETE,
        ]);

        $this->assertTrue($draft->fresh()->canBeEdited());

        $draft->update([
            'status' => Registration::STATUS_SUBMITTED,
        ]);

        $this->assertFalse($draft->fresh()->canBeEdited());
        $this->assertFalse($draft->fresh()->canBeSubmitted());
    }

    public function test_student_index_displays_only_owned_registrations(): void
    {
        $owner = $this->createVerifiedUser();
        $otherUser = $this->createVerifiedUser();

        [$level, $program] = $this->createAcademicPath();

        $owned = $this->createRegistration(
            $owner,
            $level,
            $program
        );

        $foreign = $this->createRegistration(
            $otherUser,
            $level,
            $program,
            ['academic_year' => '2027-2028']
        );

        $this->actingAs($owner)
            ->get(route('student.registrations.index'))
            ->assertOk()
            ->assertSeeText($owned->reference)
            ->assertDontSeeText($foreign->reference);
    }
}
