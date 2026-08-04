<?php

namespace Tests\Feature\JobWatch;

use App\Models\JobMatch;
use App\Models\JobOffer;
use App\Models\JobWatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMatchStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_a_job_match(): void
    {
        [$user, $jobWatch, $jobMatch] = $this->matchForOwner();

        $this->actingAs($user)
            ->patch(
                route(
                    'student.job-watches.matches.status',
                    [$jobWatch, $jobMatch]
                ),
                ['status' => 'saved']
            )
            ->assertRedirect();

        $jobMatch->refresh();

        $this->assertSame('saved', $jobMatch->status);
        $this->assertNotNull($jobMatch->saved_at);
    }

    public function test_owner_can_mark_a_job_match_as_applied(): void
    {
        [$user, $jobWatch, $jobMatch] = $this->matchForOwner();

        $this->actingAs($user)
            ->patch(
                route(
                    'student.job-watches.matches.status',
                    [$jobWatch, $jobMatch]
                ),
                ['status' => 'applied']
            )
            ->assertRedirect();

        $jobMatch->refresh();

        $this->assertSame('applied', $jobMatch->status);
        $this->assertNotNull($jobMatch->applied_at);
    }

    public function test_owner_can_dismiss_a_job_match(): void
    {
        [$user, $jobWatch, $jobMatch] = $this->matchForOwner();

        $this->actingAs($user)
            ->patch(
                route(
                    'student.job-watches.matches.status',
                    [$jobWatch, $jobMatch]
                ),
                ['status' => 'dismissed']
            )
            ->assertRedirect();

        $this->assertSame(
            'dismissed',
            $jobMatch->fresh()->status
        );
    }

    public function test_another_user_cannot_change_the_match_status(): void
    {
        [, $jobWatch, $jobMatch] = $this->matchForOwner();

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($otherUser)
            ->patch(
                route(
                    'student.job-watches.matches.status',
                    [$jobWatch, $jobMatch]
                ),
                ['status' => 'saved']
            )
            ->assertForbidden();

        $this->assertSame('new', $jobMatch->fresh()->status);
    }

    public function test_match_must_belong_to_the_selected_watch(): void
    {
        [$user, $jobWatch] = $this->matchForOwner();

        $otherWatch = JobWatch::factory()
            ->for($user)
            ->create();

        $otherOffer = JobOffer::factory()->create([
            'country_code' => 'MA',
        ]);

        $otherMatch = JobMatch::factory()->create([
            'job_watch_id' => $otherWatch->id,
            'job_offer_id' => $otherOffer->id,
            'status' => 'new',
        ]);

        $this->actingAs($user)
            ->patch(
                route(
                    'student.job-watches.matches.status',
                    [$jobWatch, $otherMatch]
                ),
                ['status' => 'saved']
            )
            ->assertNotFound();
    }

    private function matchForOwner(): array
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create();

        $jobOffer = JobOffer::factory()->create([
            'country_code' => 'MA',
            'location' => 'Casablanca',
            'status' => 'active',
        ]);

        $jobMatch = JobMatch::factory()->create([
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $jobOffer->id,
            'status' => 'new',
        ]);

        return [$user, $jobWatch, $jobMatch];
    }
}
