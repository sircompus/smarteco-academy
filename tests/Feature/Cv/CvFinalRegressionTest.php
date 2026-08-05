<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvFinalRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_contains_all_final_cv_components(): void
    {
        [$user] = $this->verifiedUserWithProfile();

        $this->actingAs($user)
            ->get(route('student.cv.edit'))
            ->assertOk()
            ->assertSee('data-cv-builder-overview', false)
            ->assertSee('data-cv-print-quality', false)
            ->assertSee('data-cv-template-selector', false)
            ->assertSee('data-cv-live-preview', false)
            ->assertSee('data-cv-preview-frame', false)
            ->assertSee('data-cv-save-experience', false)
            ->assertSee('data-cv-writing-counter', false)
            ->assertSeeText('Progression du CV')
            ->assertSeeText('Vérification avant export')
            ->assertSeeText('Aperçu instantané')
            ->assertSeeText(
                'Enregistrer et actualiser l’aperçu'
            );
    }

    public function test_all_three_cv_outputs_render_successfully(): void
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
            )
            ->assertSee('data-cv-layout="print"', false);

        $this->actingAs($user)
            ->get(route(
                'student.cv.download.cv',
                ['template' => 'moderne']
            ))
            ->assertOk()
            ->assertSee(
                'data-cv-template="moderne"',
                false
            )
            ->assertSee('data-cv-layout="print"', false);

        $this->actingAs($user)
            ->get(route('student.cv.download.ats'))
            ->assertOk()
            ->assertSee(
                'data-cv-template="ats"',
                false
            )
            ->assertSee('data-cv-layout="print"', false);
    }

    public function test_final_print_css_contains_requested_rules(): void
    {
        $css = file_get_contents(
            resource_path('css/cv-print.css')
        );

        $this->assertIsString($css);

        foreach ([
            'font-family: "Times New Roman", Times, serif;',
            'font-size: 12pt;',
            'font-size: 14pt;',
            'font-size: 16pt;',
            'font-size: 20pt;',
            'line-height: 1.5;',
            'text-indent: 1.25cm !important;',
            'orphans: 3;',
            'widows: 3;',
            'break-after: avoid-page;',
        ] as $expectedRule) {
            $this->assertStringContainsString(
                $expectedRule,
                $css
            );
        }
    }

    public function test_required_cv_files_exist(): void
    {
        foreach ([
            resource_path(
                'views/layouts/cv-print.blade.php'
            ),
            resource_path(
                'views/student/cv/_photo-field.blade.php'
            ),
            resource_path(
                'views/student/cv/_template-selector.blade.php'
            ),
            resource_path(
                'views/student/cv/_save-bar.blade.php'
            ),
            resource_path(
                'views/student/cv/_builder-overview.blade.php'
            ),
            resource_path(
                'views/student/cv/_text-counter.blade.php'
            ),
            resource_path(
                'views/student/cv/_print-quality.blade.php'
            ),
            resource_path(
                'views/student/cv/templates/classique.blade.php'
            ),
            resource_path(
                'views/student/cv/templates/moderne.blade.php'
            ),
            resource_path(
                'views/student/cv/templates/ats.blade.php'
            ),
        ] as $requiredFile) {
            $this->assertFileExists($requiredFile);
        }
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
