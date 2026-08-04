<?php

namespace Tests\Feature\JobWatch;

use App\Models\JobWatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportMoroccoJobOffersWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_import_a_moroccan_csv_from_the_web(): void
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
                'minimum_score' => 0,
                'status' => 'active',
            ]);

        $csv = implode(PHP_EOL, [
            'external_id;title;company;location;description;requirements;contract_type;remote_mode;url;skills',
            'ma-web-001;Développeur Laravel;SmartEco;Casablanca;Développement Laravel;Laravel PHP;CDI;Hybride;https://example.test/jobs/ma-web-001;Laravel|PHP',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'student.job-watches.import-morocco',
                    $jobWatch
                ),
                [
                    'offers_csv' => (
                        UploadedFile::fake()->createWithContent(
                            'offres-maroc.csv',
                            $csv
                        )
                    ),
                ]
            );

        $response->assertRedirect(
            route(
                'student.job-watches.show',
                $jobWatch
            )
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_offers', [
            'external_id' => 'ma-web-001',
            'country_code' => 'MA',
            'location' => 'Casablanca',
        ]);

        $this->assertDatabaseHas('job_matches', [
            'job_watch_id' => $jobWatch->id,
        ]);

        $this->assertNotNull(
            $jobWatch->fresh()->last_run_at
        );
    }

    public function test_another_user_cannot_import_for_the_watch(): void
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

        $csv = implode(PHP_EOL, [
            'title;location;url',
            'Data Analyst;Rabat;https://example.test/jobs/ma-002',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->post(
                route(
                    'student.job-watches.import-morocco',
                    $jobWatch
                ),
                [
                    'offers_csv' => (
                        UploadedFile::fake()->createWithContent(
                            'offres.csv',
                            $csv
                        )
                    ),
                ]
            );

        $response->assertForbidden();
        $this->assertDatabaseCount('job_offers', 0);
    }

    public function test_import_rejects_a_non_csv_file(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create();

        $response = $this
            ->actingAs($user)
            ->from(
                route(
                    'student.job-watches.show',
                    $jobWatch
                )
            )
            ->post(
                route(
                    'student.job-watches.import-morocco',
                    $jobWatch
                ),
                [
                    'offers_csv' => (
                        UploadedFile::fake()->createWithContent(
                            'offres.pdf',
                            'not a csv'
                        )
                    ),
                ]
            );

        $response->assertRedirect(
            route(
                'student.job-watches.show',
                $jobWatch
            )
        );

        $response->assertSessionHasErrors('offers_csv');
        $this->assertDatabaseCount('job_offers', 0);
    }
}
