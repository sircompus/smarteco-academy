<?php

namespace Tests\Feature\JobWatch;

use App\Models\JobMatch;
use App\Models\JobOffer;
use App\Models\JobOfferSkill;
use App\Models\JobSource;
use App\Models\JobWatch;
use App\Models\JobWatchKeyword;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobWatchFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_job_watch_belongs_to_its_user_and_generates_an_uuid(): void
    {
        $user = User::factory()->create();

        $watch = JobWatch::factory()
            ->for($user)
            ->create();

        $this->assertNotEmpty($watch->uuid);
        $this->assertTrue($watch->user->is($user));
        $this->assertIsArray($watch->target_titles);

        $this->assertDatabaseHas('job_watches', [
            'id' => $watch->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_the_foundation_relations_are_connected(): void
    {
        $user = User::factory()->create();

        $watch = JobWatch::factory()
            ->for($user)
            ->create();

        $keyword = JobWatchKeyword::factory()
            ->for($watch, 'jobWatch')
            ->create();

        $source = JobSource::factory()->create();

        $offer = JobOffer::factory()
            ->for($source, 'source')
            ->create();

        $skill = JobOfferSkill::factory()
            ->for($offer, 'jobOffer')
            ->create();

        $match = JobMatch::factory()
            ->for($watch, 'jobWatch')
            ->for($offer, 'jobOffer')
            ->create();

        $this->assertTrue($watch->keywords->contains($keyword));
        $this->assertTrue($watch->matches->contains($match));
        $this->assertTrue($source->offers->contains($offer));
        $this->assertTrue($offer->skills->contains($skill));
        $this->assertTrue($offer->matches->contains($match));
    }

    public function test_an_offer_fingerprint_must_be_unique(): void
    {
        $source = JobSource::factory()->create();
        $fingerprint = hash('sha256', 'same-offer');

        JobOffer::factory()
            ->for($source, 'source')
            ->create([
                'external_id' => 'offer-001',
                'fingerprint' => $fingerprint,
            ]);

        $this->expectException(QueryException::class);

        JobOffer::factory()
            ->for($source, 'source')
            ->create([
                'external_id' => 'offer-002',
                'fingerprint' => $fingerprint,
            ]);
    }

    public function test_external_id_must_be_unique_inside_the_same_source(): void
    {
        $source = JobSource::factory()->create();

        JobOffer::factory()
            ->for($source, 'source')
            ->create([
                'external_id' => 'external-001',
                'fingerprint' => hash('sha256', 'first-offer'),
            ]);

        $this->expectException(QueryException::class);

        JobOffer::factory()
            ->for($source, 'source')
            ->create([
                'external_id' => 'external-001',
                'fingerprint' => hash('sha256', 'second-offer'),
            ]);
    }

    public function test_a_watch_and_offer_can_have_only_one_match(): void
    {
        $watch = JobWatch::factory()->create();
        $offer = JobOffer::factory()->create();

        JobMatch::factory()
            ->for($watch, 'jobWatch')
            ->for($offer, 'jobOffer')
            ->create();

        $this->expectException(QueryException::class);

        JobMatch::factory()
            ->for($watch, 'jobWatch')
            ->for($offer, 'jobOffer')
            ->create();
    }

    public function test_the_policy_allows_only_the_owner_to_manage_a_watch(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $watch = JobWatch::factory()
            ->for($owner)
            ->create();

        $this->assertTrue($owner->can('view', $watch));
        $this->assertTrue($owner->can('update', $watch));
        $this->assertTrue($owner->can('delete', $watch));

        $this->assertFalse($otherUser->can('view', $watch));
        $this->assertFalse($otherUser->can('update', $watch));
        $this->assertFalse($otherUser->can('delete', $watch));
    }

    public function test_deleting_a_watch_cascades_its_keywords_and_matches(): void
    {
        $watch = JobWatch::factory()->create();
        $offer = JobOffer::factory()->create();

        $keyword = JobWatchKeyword::factory()
            ->for($watch, 'jobWatch')
            ->create();

        $match = JobMatch::factory()
            ->for($watch, 'jobWatch')
            ->for($offer, 'jobOffer')
            ->create();

        $watch->delete();

        $this->assertDatabaseMissing('job_watch_keywords', [
            'id' => $keyword->id,
        ]);

        $this->assertDatabaseMissing('job_matches', [
            'id' => $match->id,
        ]);

        $this->assertDatabaseHas('job_offers', [
            'id' => $offer->id,
        ]);
    }
}
