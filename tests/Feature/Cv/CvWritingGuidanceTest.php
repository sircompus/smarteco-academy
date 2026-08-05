<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvWritingGuidanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_contains_live_writing_counters(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSee(
                'data-cv-writing-counter',
                false
            )
            ->assertSee(
                'data-counter-context="summary"',
                false
            )
            ->assertSee(
                'data-counter-context="experience-new"',
                false
            )
            ->assertSee(
                'data-counter-context="project-new"',
                false
            )
            ->assertSeeText('Recommandé :')
            ->assertSeeText(
                'sans puce, sans tiret et sans point'
            );
    }

    public function test_summary_counter_uses_ats_recommended_range(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSeeText('30 à 150');
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
