<?php

namespace Tests\Feature\JobWatch;

use App\Models\JobOffer;
use App\Models\JobWatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunJobWatchWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_launch_a_job_watch_from_the_web(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'target_titles' => ['Développeur Laravel'],
                'preferred_locations' => ['Casablanca'],
                'contract_types' => ['cdi'],
                'remote_mode' => 'hybrid',
                'minimum_score' => 70,
                'status' => 'active',
            ]);

        $jobOffer = JobOffer::factory()->create([
            'title' => 'Développeur Laravel',
            'location' => 'Casablanca',
            'country_code' => 'MA',
            'description' => 'Développement PHP avec Laravel.',
            'requirements' => 'Laravel et PHP.',
            'contract_type' => 'cdi',
            'remote_mode' => 'hybrid',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'student.job-watches.run',
                    $jobWatch
                )
            );

        $response->assertRedirect(
            route(
                'student.job-watches.show',
                $jobWatch
            )
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_matches', [
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $jobOffer->id,
        ]);

        $this->assertNotNull(
            $jobWatch->fresh()->last_run_at
        );
    }

    public function test_another_user_cannot_launch_the_job_watch(): void
    {
        $owner = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $jobWatch = JobWatch::factory()
            ->for($owner)
            ->create();

        $response = $this
            ->actingAs($otherUser)
            ->post(
                route(
                    'student.job-watches.run',
                    $jobWatch
                )
            );

        $response->assertForbidden();
        $this->assertDatabaseCount('job_matches', 0);
    }

    public function test_a_disabled_job_watch_cannot_be_launched(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'status' => 'disabled',
            ]);

        JobOffer::factory()->create([
            'country_code' => 'MA',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'student.job-watches.run',
                    $jobWatch
                )
            );

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('job_matches', 0);
        $this->assertNull(
            $jobWatch->fresh()->last_run_at
        );
    }

    public function test_show_page_displays_the_run_button(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'status' => 'active',
            ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'student.job-watches.show',
                    $jobWatch
                )
            );

        $response->assertOk();
        $response->assertSee('Lancer la recherche');
    }
}
