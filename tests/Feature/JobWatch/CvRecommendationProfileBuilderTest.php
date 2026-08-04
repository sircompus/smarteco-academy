<?php

namespace Tests\Feature\JobWatch;

use App\Models\CvProfile;
use App\Models\JobWatch;
use App\Models\User;
use App\Services\JobWatch\CvRecommendationProfileBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CvRecommendationProfileBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_mode_loads_cv_and_portfolio_data(): void
    {
        [$profile, $jobWatch] = $this->profileAndWatch('both');

        $this->seedProfile($profile);

        $result = app(
            CvRecommendationProfileBuilder::class
        )->build($jobWatch);

        $this->assertTrue($result['profile_found']);
        $this->assertSame('both', $result['mode']);
        $this->assertSame(
            ['laravel'],
            $result['skill_names']
        );
        $this->assertSame(
            ['developpeur backend'],
            $result['experience_titles']
        );
        $this->assertSame(
            ['francais'],
            $result['language_names']
        );
        $this->assertEqualsCanonicalizing(
            ['laravel', 'mysql'],
            $result['portfolio_tags']
        );
        $this->assertCount(1, $result['projects']);
        $this->assertStringContainsString(
            'laravel',
            $result['searchable_text']
        );
    }

    public function test_cv_mode_ignores_portfolio_projects(): void
    {
        [$profile, $jobWatch] = $this->profileAndWatch('cv');

        $this->seedProfile($profile);

        $result = app(
            CvRecommendationProfileBuilder::class
        )->build($jobWatch);

        $this->assertTrue($result['profile_found']);
        $this->assertNotEmpty($result['skills']);
        $this->assertNotEmpty($result['experiences']);
        $this->assertSame([], $result['projects']);
        $this->assertSame([], $result['portfolio_tags']);
        $this->assertSame('', $result['portfolio_text']);
    }

    public function test_portfolio_mode_ignores_cv_sections(): void
    {
        [$profile, $jobWatch] = $this->profileAndWatch(
            'portfolio'
        );

        $this->seedProfile($profile);

        $result = app(
            CvRecommendationProfileBuilder::class
        )->build($jobWatch);

        $this->assertTrue($result['profile_found']);
        $this->assertSame([], $result['skills']);
        $this->assertSame([], $result['experiences']);
        $this->assertSame([], $result['languages']);
        $this->assertCount(1, $result['projects']);
        $this->assertNotSame('', $result['portfolio_text']);
    }

    public function test_missing_cv_profile_returns_a_safe_empty_profile(): void
    {
        $jobWatch = JobWatch::factory()->create([
            'cv_profile_id' => null,
            'source_mode' => 'portfolio',
        ]);

        $result = app(
            CvRecommendationProfileBuilder::class
        )->build($jobWatch);

        $this->assertFalse($result['profile_found']);
        $this->assertSame('portfolio', $result['mode']);
        $this->assertSame([], $result['skills']);
        $this->assertSame([], $result['projects']);
        $this->assertSame('', $result['searchable_text']);
    }

    private function profileAndWatch(
        string $mode
    ): array {
        $user = User::factory()->create();

        $profile = CvProfile::query()->forceCreate([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'headline' => 'Développeur Laravel',
            'summary' => 'Développement PHP et MySQL.',
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'cv_profile_id' => $profile->id,
                'source_mode' => $mode,
            ]);

        return [$profile, $jobWatch];
    }

    private function seedProfile(CvProfile $profile): void
    {
        $profile->skills()->create([
            'name' => 'Laravel',
            'category' => 'Backend',
            'level' => 'expert',
            'sort_order' => 0,
        ]);

        $profile->experiences()->create([
            'company' => 'SmartEco',
            'position' => 'Développeur Backend',
            'location' => 'Casablanca',
            'description' => 'API PHP et Laravel.',
            'is_current' => true,
            'sort_order' => 0,
        ]);

        $profile->educations()->create([
            'institution' => 'Université',
            'degree' => 'Master',
            'field_of_study' => 'Informatique',
            'description' => 'Développement logiciel.',
            'is_current' => false,
            'sort_order' => 0,
        ]);

        $profile->languages()->create([
            'name' => 'Français',
            'level' => 'courant',
            'sort_order' => 0,
        ]);

        $profile->certifications()->create([
            'name' => 'Laravel Developer',
            'issuer' => 'SmartEco',
            'sort_order' => 0,
        ]);

        $profile->projects()->create([
            'title' => 'Plateforme Laravel',
            'description' => 'Application web avec MySQL.',
            'tags' => 'Laravel, MySQL',
            'sort_order' => 0,
        ]);
    }
}
