<?php

namespace Tests\Feature\Registration\Concerns;

use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\Module;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesRegistrationFixtures
{
    protected function activateRegistrationModule(): Module
    {
        return Module::query()->updateOrCreate(
            ['slug' => 'inscription'],
            [
                'name' => 'Inscriptions',
                'description' => 'Gestion des demandes d’inscription.',
                'version' => '1.0.0',
                'route_prefix' => 'student/registrations',
                'is_active' => true,
                'is_core' => false,
                'menu_order' => 20,
            ]
        );
    }

    protected function createVerifiedUser(
        array $attributes = []
    ): User {
        return User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            ...$attributes,
        ]);
    }

    protected function createAdmin(): User
    {
        $admin = $this->createVerifiedUser();

        $role = Role::query()->firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrateur',
                'description' => 'Administration de la plateforme.',
                'is_active' => true,
            ]
        );

        $admin->roles()->syncWithoutDetaching([
            $role->id,
        ]);

        return $admin;
    }

    /**
     * @return array{
     *     0: AcademicLevel,
     *     1: AcademicProgram
     * }
     */
    protected function createAcademicPath(
        array $levelAttributes = [],
        array $programAttributes = []
    ): array {
        $token = Str::lower(Str::random(10));

        $level = AcademicLevel::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Niveau '.$token,
            'slug' => 'niveau-'.$token,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
            ...$levelAttributes,
        ]);

        $program = AcademicProgram::query()->create([
            'uuid' => (string) Str::uuid(),
            'academic_level_id' => $level->id,
            'name' => 'Filière '.$token,
            'slug' => 'filiere-'.$token,
            'description' => null,
            'duration_semesters' => 6,
            'is_active' => true,
            'sort_order' => 1,
            ...$programAttributes,
        ]);

        return [$level, $program];
    }

    protected function createRegistration(
        User $user,
        AcademicLevel $level,
        AcademicProgram $program,
        array $attributes = []
    ): Registration {
        return Registration::query()->create([
            'user_id' => $user->id,
            'academic_level_id' => $level->id,
            'academic_program_id' => $program->id,
            'academic_year' => '2026-2027',
            'status' => Registration::STATUS_DRAFT,
            'first_name' => 'Ali',
            'last_name' => 'Bahtit',
            'phone' => '0600000000',
            'birth_date' => '1995-01-10',
            'gender' => 'homme',
            'address' => 'Centre-ville',
            'city' => 'Casablanca',
            'country' => 'Maroc',
            'student_note' => null,
            ...$attributes,
        ]);
    }

    protected function createDocument(
        Registration $registration,
        User $uploader,
        string $type,
        bool $verified = false,
        array $attributes = []
    ): RegistrationDocument {
        return RegistrationDocument::query()->create([
            'registration_id' => $registration->id,
            'uploaded_by' => $uploader->id,
            'type' => $type,
            'title' => null,
            'disk' => 'local',
            'path' => sprintf(
                'registrations/%s/%s.pdf',
                $registration->uuid,
                $type
            ),
            'original_name' => $type.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'is_verified' => $verified,
            'verified_at' => $verified ? now() : null,
            'verified_by' => null,
            'admin_note' => null,
            ...$attributes,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function registrationPayload(
        AcademicLevel $level,
        AcademicProgram $program,
        array $overrides = []
    ): array {
        return [
            'academic_level_id' => $level->id,
            'academic_program_id' => $program->id,
            'academic_year' => '2026-2027',
            'first_name' => 'Ali',
            'last_name' => 'Bahtit',
            'phone' => '0600000000',
            'birth_date' => '1995-01-10',
            'gender' => 'homme',
            'address' => 'Centre-ville',
            'city' => 'Casablanca',
            'country' => 'Maroc',
            'student_note' => 'Demande de test.',
            ...$overrides,
        ];
    }
}
