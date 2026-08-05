<?php

namespace Tests\Feature\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvPrintLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_classic_cv_uses_the_shared_print_layout(): void
    {
        [$user] = $this->verifiedUserWithProfile('classique');

        $this->actingAs($user)
            ->get(route('student.cv.download.cv'))
            ->assertOk()
            ->assertSee('data-cv-layout="print"', false)
            ->assertSee('data-cv-template="classique"', false)
            ->assertSeeText($user->name);
    }

    public function test_modern_cv_uses_the_shared_print_layout(): void
    {
        [$user, $profile] = $this->verifiedUserWithProfile(
            'moderne'
        );

        $this->actingAs($user)
            ->get(route('student.cv.download.cv'))
            ->assertOk()
            ->assertSee('data-cv-layout="print"', false)
            ->assertSee('data-cv-template="moderne"', false)
            ->assertSeeText($profile->full_name);
    }

    public function test_ats_cv_uses_the_shared_print_layout(): void
    {
        [$user, $profile] = $this->verifiedUserWithProfile(
            'classique'
        );

        $this->actingAs($user)
            ->get(route('student.cv.download.ats'))
            ->assertOk()
            ->assertSee('data-cv-layout="print"', false)
            ->assertSee('data-cv-template="ats"', false)
            ->assertSeeText($profile->full_name)
            ->assertSeeText('Version une colonne');
    }

    public function test_cv_download_routes_are_registered(): void
    {
        $this->assertTrue(
            Route::has('student.cv.download.cv')
        );

        $this->assertTrue(
            Route::has('student.cv.download.ats')
        );

        $this->assertTrue(
            Route::has('admin.cv.download.cv')
        );

        $this->assertTrue(
            Route::has('admin.cv.download.ats')
        );
    }

    private function verifiedUserWithProfile(
        string $template
    ): array {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $profile = CvProfile::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'cv_template' => $template,
            'portfolio_template' => 'elegant',
        ]);

        return [$user, $profile];
    }
}
