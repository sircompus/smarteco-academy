<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvSaveExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_contains_sticky_save_experience(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSee(
                'data-cv-save-experience',
                false
            )
            ->assertSeeText(
                'Enregistrer et actualiser l’aperçu'
            )
            ->assertSeeText(
                'Modifications non enregistrées'
            )
            ->assertSeeText('Ctrl + S');
    }

    public function test_profile_update_route_still_accepts_cv_template(): void
    {
        [$user, $profile] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->patch(
                route('student.cv.profile.update'),
                [
                    'full_name' => 'Profil CV mis à jour',
                    'headline' => null,
                    'email' => $user->email,
                    'phone' => null,
                    'address' => null,
                    'summary' => null,
                    'linkedin_url' => null,
                    'github_url' => null,
                    'website_url' => null,
                    'cv_template' => 'moderne',
                    'portfolio_template' => 'elegant',
                ]
            )
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame(
            'Profil CV mis à jour',
            $profile->full_name
        );

        $this->assertSame(
            'moderne',
            $profile->cv_template
        );
    }

    private function verifiedUserWithProfile(): array
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $profile = CvProfile::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'cv_template' => 'classique',
            'portfolio_template' => 'elegant',
        ]);

        return [$user, $profile];
    }
}
