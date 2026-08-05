<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvLivePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_contains_integrated_live_preview(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSee('data-cv-live-preview', false)
            ->assertSee('data-cv-preview-panel', false)
            ->assertSee('data-cv-preview-frame', false)
            ->assertSeeText('Aperçu instantané')
            ->assertSeeText('Prévisualiser la version ATS')
            ->assertSeeText('Ouvrir en grand');
    }

    public function test_all_three_preview_endpoints_are_available(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route(
                'student.cv.download.cv',
                ['template' => 'classique']
            ))
            ->assertOk()
            ->assertSee(
                'data-cv-template="classique"',
                false
            );

        $this->actingAs($user)
            ->get(route(
                'student.cv.download.cv',
                ['template' => 'moderne']
            ))
            ->assertOk()
            ->assertSee(
                'data-cv-template="moderne"',
                false
            );

        $this->actingAs($user)
            ->get(route('student.cv.download.ats'))
            ->assertOk()
            ->assertSee(
                'data-cv-template="ats"',
                false
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
