<?php

namespace Tests\Feature\JobWatch;

use App\Models\CvProfile;
use App\Models\JobWatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class JobWatchCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_user_sees_only_their_own_job_watches(): void
    {
        $user = $this->verifiedUser();
        $otherUser = $this->verifiedUser();

        $ownWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'name' => 'Ma veille Laravel',
            ]);

        JobWatch::factory()
            ->for($otherUser)
            ->create([
                'name' => 'Veille privée étrangère',
            ]);

        $response = $this
            ->actingAs($user)
            ->get(route('student.job-watches.index'));

        $response
            ->assertOk()
            ->assertSee($ownWatch->name)
            ->assertDontSee('Veille privée étrangère');
    }

    public function test_user_can_create_a_job_watch_with_keywords(): void
    {
        $user = $this->verifiedUser();

        $cvProfile = $this->createCvProfile($user);

        $response = $this
            ->actingAs($user)
            ->post(route('student.job-watches.store'), [
                'name' => 'Développeur Laravel Maroc',
                'cv_profile_id' => $cvProfile->id,
                'source_mode' => 'both',
                'target_titles_text' => implode(PHP_EOL, [
                    'Développeur Laravel',
                    'Développeur PHP',
                    'développeur laravel',
                ]),
                'preferred_locations_text' => implode(PHP_EOL, [
                    'Casablanca',
                    'Rabat',
                ]),
                'contract_types' => [
                    'cdi',
                    'freelance',
                ],
                'remote_mode' => 'hybrid',
                'minimum_score' => 70,
                'frequency_minutes' => 1440,
                'status' => 'active',
                'keywords_include_text' => implode(PHP_EOL, [
                    'Laravel',
                    'PHP',
                    'laravel',
                ]),
                'keywords_exclude_text' => 'Senior',
            ]);

        $jobWatch = JobWatch::query()
            ->where('user_id', $user->id)
            ->where('name', 'Développeur Laravel Maroc')
            ->firstOrFail();

        $response->assertRedirect(
            route('student.job-watches.show', $jobWatch)
        );

        $this->assertSame(
            [
                'Développeur Laravel',
                'Développeur PHP',
            ],
            $jobWatch->target_titles
        );

        $this->assertSame(
            [
                'Casablanca',
                'Rabat',
            ],
            $jobWatch->preferred_locations
        );

        $this->assertSame('active', $jobWatch->status);
        $this->assertNotNull($jobWatch->next_run_at);

        $this->assertDatabaseHas('job_watch_keywords', [
            'job_watch_id' => $jobWatch->id,
            'normalized_keyword' => 'laravel',
            'type' => 'include',
        ]);

        $this->assertDatabaseHas('job_watch_keywords', [
            'job_watch_id' => $jobWatch->id,
            'normalized_keyword' => 'php',
            'type' => 'include',
        ]);

        $this->assertDatabaseHas('job_watch_keywords', [
            'job_watch_id' => $jobWatch->id,
            'normalized_keyword' => 'senior',
            'type' => 'exclude',
        ]);

        $this->assertDatabaseCount('job_watch_keywords', 3);
    }

    public function test_user_cannot_use_another_users_cv_profile(): void
    {
        $user = $this->verifiedUser();
        $otherUser = $this->verifiedUser();

        $foreignCvProfile = $this->createCvProfile($otherUser);

        $response = $this
            ->actingAs($user)
            ->post(route('student.job-watches.store'), [
                'name' => 'Veille interdite',
                'cv_profile_id' => $foreignCvProfile->id,
                'source_mode' => 'cv',
                'target_titles_text' => 'Développeur PHP',
                'preferred_locations_text' => 'Casablanca',
                'contract_types' => ['cdi'],
                'remote_mode' => 'any',
                'minimum_score' => 60,
                'frequency_minutes' => 1440,
                'status' => 'active',
            ]);

        $response->assertSessionHasErrors('cv_profile_id');

        $this->assertDatabaseMissing('job_watches', [
            'user_id' => $user->id,
            'name' => 'Veille interdite',
        ]);
    }

    public function test_user_can_update_their_job_watch(): void
    {
        $user = $this->verifiedUser();

        $cvProfile = $this->createCvProfile($user);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'cv_profile_id' => $cvProfile->id,
                'name' => 'Ancien nom',
                'status' => 'active',
            ]);

        $jobWatch->keywords()->create([
            'keyword' => 'Ancien mot',
            'normalized_keyword' => 'ancien mot',
            'type' => 'include',
            'weight' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(
                route('student.job-watches.update', $jobWatch),
                [
                    'name' => 'Nouveau nom',
                    'cv_profile_id' => $cvProfile->id,
                    'source_mode' => 'cv',
                    'target_titles_text' => 'Développeur Full Stack',
                    'preferred_locations_text' => 'Rabat',
                    'contract_types' => ['cdi'],
                    'remote_mode' => 'remote',
                    'minimum_score' => 80,
                    'frequency_minutes' => 360,
                    'status' => 'paused',
                    'keywords_include_text' => 'Laravel',
                    'keywords_exclude_text' => 'Stage',
                ]
            );

        $response->assertRedirect(
            route('student.job-watches.show', $jobWatch)
        );

        $jobWatch->refresh();

        $this->assertSame('Nouveau nom', $jobWatch->name);
        $this->assertSame('paused', $jobWatch->status);
        $this->assertNull($jobWatch->next_run_at);
        $this->assertSame(80, $jobWatch->minimum_score);

        $this->assertDatabaseMissing('job_watch_keywords', [
            'job_watch_id' => $jobWatch->id,
            'normalized_keyword' => 'ancien mot',
        ]);

        $this->assertDatabaseHas('job_watch_keywords', [
            'job_watch_id' => $jobWatch->id,
            'normalized_keyword' => 'laravel',
            'type' => 'include',
        ]);

        $this->assertDatabaseHas('job_watch_keywords', [
            'job_watch_id' => $jobWatch->id,
            'normalized_keyword' => 'stage',
            'type' => 'exclude',
        ]);
    }

    public function test_user_cannot_manage_another_users_job_watch(): void
    {
        $user = $this->verifiedUser();
        $owner = $this->verifiedUser();

        $jobWatch = JobWatch::factory()
            ->for($owner)
            ->create();

        $this
            ->actingAs($user)
            ->get(route('student.job-watches.show', $jobWatch))
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->put(
                route('student.job-watches.update', $jobWatch),
                []
            )
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->delete(route('student.job-watches.destroy', $jobWatch))
            ->assertForbidden();

        $this->assertDatabaseHas('job_watches', [
            'id' => $jobWatch->id,
        ]);
    }

    public function test_user_can_pause_and_reactivate_their_job_watch(): void
    {
        $user = $this->verifiedUser();

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'status' => 'active',
                'frequency_minutes' => 1440,
                'next_run_at' => now()->addDay(),
            ]);

        $this
            ->actingAs($user)
            ->patch(
                route('student.job-watches.status', $jobWatch)
            )
            ->assertRedirect();

        $jobWatch->refresh();

        $this->assertSame('paused', $jobWatch->status);
        $this->assertNull($jobWatch->next_run_at);

        $this
            ->actingAs($user)
            ->patch(
                route('student.job-watches.status', $jobWatch)
            )
            ->assertRedirect();

        $jobWatch->refresh();

        $this->assertSame('active', $jobWatch->status);
        $this->assertNotNull($jobWatch->next_run_at);
    }

    public function test_user_can_delete_their_job_watch(): void
    {
        $user = $this->verifiedUser();

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create();

        $response = $this
            ->actingAs($user)
            ->delete(
                route('student.job-watches.destroy', $jobWatch)
            );

        $response->assertRedirect(
            route('student.job-watches.index')
        );

        $this->assertDatabaseMissing('job_watches', [
            'id' => $jobWatch->id,
        ]);
    }

    private function createCvProfile(User $user): CvProfile
    {
        return CvProfile::query()->forceCreate([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);
    }

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }
}
