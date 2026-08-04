<?php

namespace Tests\Feature\JobWatch;

use App\Models\JobOffer;
use App\Models\JobSource;
use App\Models\JobWatch;
use App\Models\User;
use App\Services\JobWatch\JobOfferImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobOfferImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_an_arbeitnow_offer_and_skills(): void
    {
        Http::fake([
            'https://www.arbeitnow.com/api/job-board-api*' => (
                Http::response($this->payload(), 200)
            ),
        ]);

        $result = app(JobOfferImporter::class)->import(
            'arbeitnow',
            1
        );

        $this->assertSame(1, $result['received']);
        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);

        $source = JobSource::query()
            ->where('slug', 'arbeitnow')
            ->firstOrFail();

        $offer = JobOffer::query()
            ->where('external_id', 'laravel-developer-123')
            ->firstOrFail();

        $this->assertSame($source->id, $offer->job_source_id);
        $this->assertSame('Développeur Laravel', $offer->title);
        $this->assertSame('SmartEco', $offer->company);
        $this->assertSame('Casablanca', $offer->location);
        $this->assertSame('cdi', $offer->contract_type);
        $this->assertSame('remote', $offer->remote_mode);
        $this->assertSame('active', $offer->status);
        $this->assertStringNotContainsString(
            '<p>',
            (string) $offer->description
        );

        $this->assertEqualsCanonicalizing(
            ['laravel', 'php'],
            $offer->skills
                ->pluck('normalized_name')
                ->all()
        );

        $this->assertNotNull(
            $source->fresh()->last_success_at
        );
    }

    public function test_reimport_updates_without_creating_duplicates(): void
    {
        Http::fake([
            'https://www.arbeitnow.com/api/job-board-api*' => (
                Http::response($this->payload(), 200)
            ),
        ]);

        $importer = app(JobOfferImporter::class);

        $first = $importer->import('arbeitnow', 1);
        $second = $importer->import('arbeitnow', 1);

        $this->assertSame(1, $first['created']);
        $this->assertSame(1, $second['updated']);
        $this->assertDatabaseCount('job_offers', 1);
        $this->assertDatabaseCount('job_offer_skills', 2);
    }

    public function test_international_import_does_not_create_a_moroccan_match(): void
    {
        Http::fake([
            'https://www.arbeitnow.com/api/job-board-api*' => (
                Http::response($this->payload(), 200)
            ),
        ]);

        $user = User::factory()->create();

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'cv_profile_id' => null,
                'source_mode' => 'cv',
                'target_titles' => ['Développeur Laravel'],
                'preferred_locations' => [],
                'contract_types' => [],
                'remote_mode' => 'any',
                'minimum_score' => 0,
                'status' => 'active',
            ]);

        $this->artisan(
            'job-watch:import-offers',
            [
                '--source' => 'arbeitnow',
                '--pages' => 1,
                '--match' => true,
            ]
        )->assertSuccessful();

        $offer = JobOffer::query()->firstOrFail();

        $this->assertDatabaseMissing('job_matches', [
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $offer->id,
        ]);

        $this->assertNotNull(
            $jobWatch->fresh()->last_run_at
        );
    }

    private function payload(): array
    {
        return [
            'data' => [
                [
                    'slug' => 'laravel-developer-123',
                    'company_name' => 'SmartEco',
                    'title' => 'Développeur Laravel',
                    'description' => (
                        '<p>Développement PHP et Laravel.</p>'
                    ),
                    'remote' => true,
                    'url' => (
                        'https://www.arbeitnow.com/jobs/'
                        .'laravel-developer-123'
                    ),
                    'tags' => ['Laravel', 'PHP'],
                    'job_types' => ['Full-time'],
                    'location' => 'Casablanca',
                    'created_at' => 1785792634,
                ],
            ],
            'links' => [
                'next' => null,
            ],
        ];
    }
}
