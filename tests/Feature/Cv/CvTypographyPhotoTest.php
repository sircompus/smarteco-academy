<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvTypographyPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_classic_cv_contains_typography_and_photo_classes(): void
    {
        [$user, $profile] = $this->verifiedUserWithProfile(
            'classique'
        );

        $this->actingAs($user)
            ->get(route('student.cv.download.cv'))
            ->assertOk()
            ->assertSee('data-cv-template="classique"', false)
            ->assertSee('class="cv-name"', false)
            ->assertSee('cv-title', false)
            ->assertSee('cv-description', false)
            ->assertSeeText($profile->full_name);
    }

    public function test_ats_cv_does_not_render_the_profile_photo(): void
    {
        [$user] = $this->verifiedUserWithProfile(
            'classique',
            'cv-photos/photo.jpg'
        );

        $this->actingAs($user)
            ->get(route('student.cv.download.ats'))
            ->assertOk()
            ->assertSee('data-cv-template="ats"', false)
            ->assertDontSee('cv-photo', false);
    }

    public function test_user_can_upload_a_cv_photo(): void
    {
        Storage::fake('public');

        [$user, $profile] = $this->verifiedUserWithProfile(
            'classique'
        );

        $this->actingAs($user)
            ->patch(
                route('student.cv.profile.update'),
                $this->profilePayload($profile) + [
                    'photo' => UploadedFile::fake()
                        ->image('portrait.jpg', 600, 800),
                ]
            )
            ->assertRedirect();

        $profile->refresh();

        $this->assertNotNull($profile->photo_path);
        Storage::disk('public')->assertExists(
            $profile->photo_path
        );
    }

    public function test_user_can_remove_their_cv_photo(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put(
            'cv-photos/old-photo.jpg',
            'photo'
        );

        [$user, $profile] = $this->verifiedUserWithProfile(
            'classique',
            'cv-photos/old-photo.jpg'
        );

        $this->actingAs($user)
            ->patch(
                route('student.cv.profile.update'),
                $this->profilePayload($profile) + [
                    'remove_photo' => 1,
                ]
            )
            ->assertRedirect();

        $profile->refresh();

        $this->assertNull($profile->photo_path);

        Storage::disk('public')->assertMissing(
            'cv-photos/old-photo.jpg'
        );
    }

    private function verifiedUserWithProfile(
        string $template,
        ?string $photoPath = null
    ): array {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $profile = CvProfile::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'photo_path' => $photoPath,
            'cv_template' => $template,
            'portfolio_template' => 'elegant',
        ]);

        return [$user, $profile];
    }

    private function profilePayload(CvProfile $profile): array
    {
        return [
            'full_name' => $profile->full_name,
            'headline' => $profile->headline,
            'email' => $profile->email,
            'phone' => $profile->phone,
            'address' => $profile->address,
            'summary' => $profile->summary,
            'linkedin_url' => $profile->linkedin_url,
            'github_url' => $profile->github_url,
            'website_url' => $profile->website_url,
            'cv_template' => $profile->cv_template,
            'portfolio_template' => (
                $profile->portfolio_template ?: 'elegant'
            ),
        ];
    }
}
