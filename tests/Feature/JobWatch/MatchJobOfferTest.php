<?php

namespace Tests\Feature\JobWatch;

use App\Actions\JobWatch\MatchJobOffer;
use App\Models\JobMatch;
use App\Models\JobOffer;
use App\Models\JobWatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchJobOfferTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_match_for_an_eligible_offer(): void
    {
        [$jobWatch, $jobOffer] = $this->matchingPair();

        $jobMatch = app(MatchJobOffer::class)->execute(
            $jobWatch,
            $jobOffer
        );

        $this->assertInstanceOf(JobMatch::class, $jobMatch);
        $this->assertSame(100, $jobMatch->score);
        $this->assertSame(100, $jobMatch->title_score);
        $this->assertSame(100, $jobMatch->skill_score);
        $this->assertSame(100, $jobMatch->location_score);
        $this->assertSame(100, $jobMatch->contract_score);
        $this->assertSame('new', $jobMatch->status);
        $this->assertSame(
            100,
            $jobMatch->score_details['components']['remote']
        );
        $this->assertSame(
            100,
            $jobMatch->score_details['components']['keywords']
        );
        $this->assertEqualsCanonicalizing(
            ['laravel', 'php'],
            $jobMatch->matched_skills
        );

        $this->assertDatabaseHas('job_matches', [
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $jobOffer->id,
            'score' => 100,
        ]);
    }

    public function test_it_updates_the_existing_match_without_duplicates(): void
    {
        [$jobWatch, $jobOffer] = $this->matchingPair([
            'minimum_score' => 0,
        ]);

        $action = app(MatchJobOffer::class);

        $firstMatch = $action->execute($jobWatch, $jobOffer);

        $firstMatch->update([
            'status' => 'saved',
            'saved_at' => now(),
        ]);

        $jobOffer->update([
            'location' => 'Paris',
            'contract_type' => 'cdd',
            'remote_mode' => 'onsite',
        ]);

        $secondMatch = $action->execute(
            $jobWatch->fresh(),
            $jobOffer->fresh()
        );

        $this->assertInstanceOf(JobMatch::class, $secondMatch);
        $this->assertSame($firstMatch->id, $secondMatch->id);
        $this->assertSame('saved', $secondMatch->status);
        $this->assertSame(65, $secondMatch->score);

        $this->assertDatabaseCount('job_matches', 1);
    }

    public function test_an_excluded_keyword_removes_an_existing_match(): void
    {
        [$jobWatch, $jobOffer] = $this->matchingPair();

        JobMatch::query()->create([
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $jobOffer->id,
            'score' => 90,
        ]);

        $jobOffer->update([
            'description' => 'Poste de directeur technique.',
        ]);

        $result = app(MatchJobOffer::class)->execute(
            $jobWatch,
            $jobOffer->fresh()
        );

        $this->assertNull($result);

        $this->assertDatabaseMissing('job_matches', [
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $jobOffer->id,
        ]);
    }

    public function test_inactive_or_expired_offers_are_ignored(): void
    {
        [$jobWatch, $inactiveOffer] = $this->matchingPair();

        $inactiveOffer->update([
            'status' => 'archived',
        ]);

        $expiredOffer = JobOffer::factory()->create([
            'title' => 'Développeur Laravel PHP',
            'location' => 'Casablanca',
            'description' => 'Laravel PHP MySQL',
            'requirements' => 'Laravel et PHP',
            'contract_type' => 'cdi',
            'remote_mode' => 'hybrid',
            'status' => 'active',
            'expires_at' => now()->subMinute(),
        ]);

        $inactiveResult = app(MatchJobOffer::class)->execute(
            $jobWatch,
            $inactiveOffer->fresh()
        );

        $expiredResult = app(MatchJobOffer::class)->execute(
            $jobWatch,
            $expiredOffer
        );

        $this->assertNull($inactiveResult);
        $this->assertNull($expiredResult);
        $this->assertDatabaseCount('job_matches', 0);
    }

    public function test_an_offer_below_the_minimum_score_is_not_stored(): void
    {
        $jobWatch = JobWatch::factory()->create([
            'target_titles' => ['Architecte cloud'],
            'preferred_locations' => ['Casablanca'],
            'contract_types' => ['cdi'],
            'remote_mode' => 'hybrid',
            'minimum_score' => 95,
        ]);

        $jobOffer = JobOffer::factory()->create([
            'title' => 'Assistant administratif',
            'location' => 'Paris',
            'description' => null,
            'requirements' => null,
            'contract_type' => 'cdd',
            'remote_mode' => 'onsite',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        JobMatch::query()->create([
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $jobOffer->id,
            'score' => 80,
        ]);

        $result = app(MatchJobOffer::class)->execute(
            $jobWatch,
            $jobOffer
        );

        $this->assertNull($result);

        $this->assertDatabaseMissing('job_matches', [
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $jobOffer->id,
        ]);
    }

    private function matchingPair(
        array $watchOverrides = []
    ): array {
        $jobWatch = JobWatch::factory()->create(array_merge([
            'target_titles' => [
                'Développeur Laravel',
                'Développeur PHP',
            ],
            'preferred_locations' => ['Casablanca'],
            'contract_types' => ['cdi'],
            'remote_mode' => 'hybrid',
            'minimum_score' => 70,
        ], $watchOverrides));

        $jobWatch->keywords()->createMany([
            [
                'keyword' => 'Laravel',
                'normalized_keyword' => 'laravel',
                'type' => 'include',
                'weight' => 2,
            ],
            [
                'keyword' => 'PHP',
                'normalized_keyword' => 'php',
                'type' => 'include',
                'weight' => 1,
            ],
            [
                'keyword' => 'Directeur',
                'normalized_keyword' => 'directeur',
                'type' => 'exclude',
                'weight' => 1,
            ],
        ]);

        $jobOffer = JobOffer::factory()->create([
            'title' => 'Développeur Laravel PHP',
            'company' => 'SmartEco',
            'location' => 'Casablanca',
            'description' => 'Développement avec Laravel et PHP.',
            'requirements' => 'Bonne maîtrise de MySQL.',
            'contract_type' => 'cdi',
            'remote_mode' => 'hybrid',
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
                'name' => 'PHP',
                'normalized_name' => 'php',
                'importance' => 5,
                'is_required' => true,
            ],
            [
                'name' => 'MySQL',
                'normalized_name' => 'mysql',
                'importance' => 3,
                'is_required' => false,
            ],
        ]);

        return [$jobWatch, $jobOffer];
    }
}
