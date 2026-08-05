<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvBuilderProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_displays_progress_and_quick_navigation(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSee(
                'data-cv-builder-overview',
                false
            )
            ->assertSee(
                'data-cv-completion-percentage',
                false
            )
            ->assertSee(
                'data-cv-builder-navigation',
                false
            )
            ->assertSeeText('Progression du CV')
            ->assertSeeText('Compléter mon CV')
            ->assertSee('href="#profile-info"', false)
            ->assertSee('href="#educations"', false)
            ->assertSee('href="#experiences"', false)
            ->assertSee('href="#skills"', false)
            ->assertSee('href="#languages"', false)
            ->assertSee('href="#projects"', false);
    }

    public function test_incomplete_profile_displays_recommended_actions(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSeeText('Prochaines actions recommandées')
            ->assertSeeText('Titre professionnel')
            ->assertSeeText('Formation')
            ->assertSeeText('Expérience ou stage')
            ->assertSeeText('Au moins cinq compétences')
            ->assertSeeText('Langues');
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
