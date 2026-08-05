<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvPrintQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_contains_export_quality_checklist(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSee('data-cv-print-quality', false)
            ->assertSeeText('Vérification avant export')
            ->assertSeeText('Vérifier le CV classique')
            ->assertSeeText('Vérifier le CV moderne')
            ->assertSeeText('Vérifier la version ATS')
            ->assertSeeText('format A4')
            ->assertSeeText('échelle 100 %');
    }

    public function test_print_css_contains_pagination_protection(): void
    {
        $css = file_get_contents(
            resource_path('css/cv-print.css')
        );

        $this->assertIsString($css);
        $this->assertStringContainsString(
            'CV Phase 8 — Pagination',
            $css
        );
        $this->assertStringContainsString(
            'orphans: 3;',
            $css
        );
        $this->assertStringContainsString(
            'widows: 3;',
            $css
        );
        $this->assertStringContainsString(
            'break-after: avoid-page;',
            $css
        );
    }

    public function test_all_cv_templates_still_render(): void
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
