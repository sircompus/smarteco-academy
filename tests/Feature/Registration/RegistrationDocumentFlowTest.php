<?php

namespace Tests\Feature\Registration;

use App\Models\Registration;
use App\Models\RegistrationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Registration\Concerns\CreatesRegistrationFixtures;
use Tests\TestCase;

class RegistrationDocumentFlowTest extends TestCase
{
    use CreatesRegistrationFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activateRegistrationModule();
        Storage::fake('local');
    }

    public function test_owner_can_upload_and_replace_document(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $user,
            $level,
            $program
        );

        $this->actingAs($user)
            ->post(
                route(
                    'student.registrations.documents.store',
                    $registration
                ),
                [
                    'type' => 'identity',
                    'title' => 'Carte nationale',
                    'file' => UploadedFile::fake()->create(
                        'identity.pdf',
                        100,
                        'application/pdf'
                    ),
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $document = RegistrationDocument::query()
            ->where('registration_id', $registration->id)
            ->where('type', 'identity')
            ->firstOrFail();

        Storage::disk('local')
            ->assertExists($document->path);

        $oldPath = $document->path;

        $this->actingAs($user)
            ->post(
                route(
                    'student.registrations.documents.store',
                    $registration
                ),
                [
                    'type' => 'identity',
                    'title' => 'Nouvelle carte nationale',
                    'file' => UploadedFile::fake()->create(
                        'identity-new.pdf',
                        120,
                        'application/pdf'
                    ),
                ]
            )
            ->assertSessionHasNoErrors();

        $document->refresh();

        $this->assertDatabaseCount(
            'registration_documents',
            1
        );

        Storage::disk('local')
            ->assertMissing($oldPath);

        Storage::disk('local')
            ->assertExists($document->path);

        $this->assertSame(
            'identity-new.pdf',
            $document->original_name
        );

        $this->assertFalse($document->is_verified);
    }

    public function test_owner_can_download_and_delete_document(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $user,
            $level,
            $program
        );

        $document = $this->createDocument(
            $registration,
            $user,
            'diploma'
        );

        Storage::disk('local')->put(
            $document->path,
            'fake pdf content'
        );

        $this->actingAs($user)
            ->get(route(
                'registration-documents.download',
                $document
            ))
            ->assertOk()
            ->assertDownload($document->original_name);

        $this->actingAs($user)
            ->delete(route(
                'student.registrations.documents.destroy',
                $document
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing(
            'registration_documents',
            ['id' => $document->id]
        );

        Storage::disk('local')
            ->assertMissing($document->path);
    }

    public function test_another_user_cannot_manage_or_download_document(): void
    {
        $owner = $this->createVerifiedUser();
        $intruder = $this->createVerifiedUser();

        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $owner,
            $level,
            $program
        );

        $document = $this->createDocument(
            $registration,
            $owner,
            'transcript'
        );

        Storage::disk('local')->put(
            $document->path,
            'fake pdf content'
        );

        $this->actingAs($intruder)
            ->post(
                route(
                    'student.registrations.documents.store',
                    $registration
                ),
                [
                    'type' => 'photo',
                    'file' => UploadedFile::fake()->image(
                        'photo.png'
                    ),
                ]
            )
            ->assertForbidden();

        $this->actingAs($intruder)
            ->get(route(
                'registration-documents.download',
                $document
            ))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route(
                'student.registrations.documents.destroy',
                $document
            ))
            ->assertForbidden();

        $this->assertDatabaseHas(
            'registration_documents',
            ['id' => $document->id]
        );
    }

    public function test_submitted_registration_documents_are_locked(): void
    {
        $user = $this->createVerifiedUser();
        [$level, $program] = $this->createAcademicPath();

        $registration = $this->createRegistration(
            $user,
            $level,
            $program,
            ['status' => Registration::STATUS_SUBMITTED]
        );

        $document = $this->createDocument(
            $registration,
            $user,
            'identity'
        );

        $this->actingAs($user)
            ->post(
                route(
                    'student.registrations.documents.store',
                    $registration
                ),
                [
                    'type' => 'photo',
                    'file' => UploadedFile::fake()->image(
                        'photo.png'
                    ),
                ]
            )
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route(
                'student.registrations.documents.destroy',
                $document
            ))
            ->assertForbidden();
    }
}
