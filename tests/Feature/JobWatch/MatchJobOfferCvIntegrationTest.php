<?php

namespace Tests\Feature\JobWatch;

use App\Actions\JobWatch\MatchJobOffer;
use App\Models\CvProfile;
use App\Models\JobOffer;
use App\Models\JobWatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MatchJobOfferCvIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cv_skills_influence_the_matching_score(): void
    {
        $user = User::factory()->create();

        $profile = CvProfile::query()->forceCreate([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'headline' => 'Développeur backend',
        ]);

        $profile->skills()->create([
            'name' => 'Laravel',
            'category' => 'Backend',
            'level' => 'expert',
            'sort_order' => 0,
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'cv_profile_id' => $profile->id,
                'source_mode' => 'cv',
                'target_titles' => ['Développeur backend'],
                'preferred_locations' => [],
                'contract_types' => [],
                'remote_mode' => 'any',
                'minimum_score' => 0,
            ]);

        $matchingOffer = JobOffer::factory()->create([
            'title' => 'Développeur backend',
            'description' => 'Développement Laravel.',
            'requirements' => 'Laravel',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $matchingOffer->skills()->create([
            'name' => 'Laravel',
            'normalized_name' => 'laravel',
            'importance' => 5,
            'is_required' => true,
        ]);

        $differentOffer = JobOffer::factory()->create([
            'title' => 'Développeur backend',
            'description' => 'Développement Python.',
            'requirements' => 'Python',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $differentOffer->skills()->create([
            'name' => 'Python',
            'normalized_name' => 'python',
            'importance' => 5,
            'is_required' => true,
        ]);

        $action = app(MatchJobOffer::class);

        $matchingResult = $action->execute(
            $jobWatch,
            $matchingOffer
        );

        $differentResult = $action->execute(
            $jobWatch,
            $differentOffer
        );

        $this->assertNotNull($matchingResult);
        $this->assertNotNull($differentResult);

        $this->assertSame(100, $matchingResult->skill_score);
        $this->assertSame(0, $differentResult->skill_score);

        $this->assertGreaterThan(
            $differentResult->score,
            $matchingResult->score
        );

        $this->assertContains(
            'laravel',
            $matchingResult->matched_skills
        );

        $this->assertContains(
            'laravel',
            $differentResult->missing_skills
        );

        $profileDetails = $matchingResult
            ->score_details['recommendation_profile'];

        $this->assertTrue($profileDetails['profile_found']);
        $this->assertSame($profile->id, $profileDetails['cv_profile_id']);
        $this->assertSame(['laravel'], $profileDetails['skill_names']);
    }

    public function test_portfolio_tags_influence_the_matching_score(): void
    {
        $user = User::factory()->create();

        $profile = CvProfile::query()->forceCreate([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        $profile->projects()->create([
            'title' => 'Plateforme de formation',
            'description' => 'Application web.',
            'tags' => 'Laravel, MySQL',
            'sort_order' => 0,
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'cv_profile_id' => $profile->id,
                'source_mode' => 'portfolio',
                'target_titles' => ['Développeur web'],
                'preferred_locations' => [],
                'contract_types' => [],
                'remote_mode' => 'any',
                'minimum_score' => 0,
            ]);

        $jobOffer = JobOffer::factory()->create([
            'title' => 'Développeur web',
            'description' => 'Projet Laravel et MySQL.',
            'requirements' => 'Laravel, MySQL',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $jobOffer->skills()->createMany([
            [
                'name' => 'Laravel',
                'normalized_name' => 'laravel',
                'importance' => 5,
                'is_required' => true,
            ],
            [
                'name' => 'MySQL',
                'normalized_name' => 'mysql',
                'importance' => 4,
                'is_required' => true,
            ],
        ]);

        $result = app(MatchJobOffer::class)->execute(
            $jobWatch,
            $jobOffer
        );

        $this->assertNotNull($result);
        $this->assertSame(100, $result->skill_score);
        $this->assertEqualsCanonicalizing(
            ['laravel', 'mysql'],
            $result->matched_skills
        );

        $this->assertEqualsCanonicalizing(
            ['laravel', 'mysql'],
            $result->score_details[
                'recommendation_profile'
            ]['portfolio_tags']
        );
    }

    public function test_a_foreign_cv_profile_is_not_loaded(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $foreignProfile = CvProfile::query()->forceCreate([
            'uuid' => (string) Str::uuid(),
            'user_id' => $otherUser->id,
        ]);

        $foreignProfile->skills()->create([
            'name' => 'Laravel',
            'level' => 'expert',
            'sort_order' => 0,
        ]);

        $jobWatch = JobWatch::factory()
            ->for($owner)
            ->create([
                'cv_profile_id' => $foreignProfile->id,
                'source_mode' => 'cv',
                'target_titles' => ['Développeur'],
                'preferred_locations' => [],
                'contract_types' => [],
                'remote_mode' => 'any',
                'minimum_score' => 0,
            ]);

        $jobOffer = JobOffer::factory()->create([
            'title' => 'Développeur',
            'description' => 'Laravel',
            'requirements' => 'Laravel',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $result = app(MatchJobOffer::class)->execute(
            $jobWatch,
            $jobOffer
        );

        $this->assertNotNull($result);

        $profileDetails = $result
            ->score_details['recommendation_profile'];

        $this->assertFalse($profileDetails['profile_found']);
        $this->assertNull($profileDetails['cv_profile_id']);
        $this->assertSame([], $profileDetails['skill_names']);
    }
}
