<?php

namespace Tests\Feature\JobWatch;

use App\Actions\JobWatch\RunJobWatch;
use App\Models\JobOffer;
use App\Models\JobSource;
use App\Models\JobWatch;
use App\Models\User;
use App\Services\JobWatch\Importers\MoroccoCsvJobImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MoroccoJobOffersTest extends TestCase
{
    use RefreshDatabase;

    private string $csvPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->csvPath = storage_path(
            'framework/testing/offres-maroc.csv'
        );

        File::ensureDirectoryExists(
            dirname($this->csvPath)
        );
    }

    protected function tearDown(): void
    {
        File::delete($this->csvPath);

        parent::tearDown();
    }

    public function test_it_imports_a_moroccan_csv_offer(): void
    {
        File::put(
            $this->csvPath,
            implode(PHP_EOL, [
                'external_id;title;company;location;description;requirements;contract_type;remote_mode;experience_level;url;published_at;expires_at;skills',
                'ma-001;Développeur Laravel;SmartEco;Casablanca;Développement web;Laravel et PHP;CDI;Hybride;Junior;https://example.test/jobs/ma-001;2026-08-04;2026-09-04;Laravel|PHP|MySQL',
            ])
        );

        $result = app(MoroccoCsvJobImporter::class)->import(
            $this->csvPath
        );

        $this->assertSame(1, $result['received']);
        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['skipped']);

        $offer = JobOffer::query()->firstOrFail();

        $this->assertSame('MA', $offer->country_code);
        $this->assertSame('Casablanca', $offer->location);
        $this->assertSame('cdi', $offer->contract_type);
        $this->assertSame('hybrid', $offer->remote_mode);

        $this->assertEqualsCanonicalizing(
            ['laravel', 'php', 'mysql'],
            $offer->skills
                ->pluck('normalized_name')
                ->all()
        );
    }

    public function test_reimport_updates_without_duplicates(): void
    {
        File::put(
            $this->csvPath,
            implode(PHP_EOL, [
                'external_id;title;company;location;url;skills',
                'ma-002;Data Analyst;Entreprise A;Rabat;https://example.test/jobs/ma-002;Python|SQL',
            ])
        );

        $importer = app(MoroccoCsvJobImporter::class);

        $first = $importer->import($this->csvPath);
        $second = $importer->import($this->csvPath);

        $this->assertSame(1, $first['created']);
        $this->assertSame(1, $second['updated']);
        $this->assertDatabaseCount('job_offers', 1);
        $this->assertDatabaseCount('job_offer_skills', 2);
    }

    public function test_run_job_watch_ignores_international_offers(): void
    {
        $user = User::factory()->create();

        $jobWatch = JobWatch::factory()
            ->for($user)
            ->create([
                'target_titles' => ['Développeur'],
                'preferred_locations' => [],
                'contract_types' => [],
                'remote_mode' => 'any',
                'minimum_score' => 0,
            ]);

        $moroccanOffer = JobOffer::factory()->create([
            'title' => 'Développeur',
            'country_code' => 'MA',
            'location' => 'Casablanca',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $internationalOffer = JobOffer::factory()->create([
            'title' => 'Développeur',
            'country_code' => 'DE',
            'location' => 'Berlin',
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $result = app(RunJobWatch::class)->execute($jobWatch);

        $this->assertSame(1, $result['analyzed']);

        $this->assertDatabaseHas('job_matches', [
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $moroccanOffer->id,
        ]);

        $this->assertDatabaseMissing('job_matches', [
            'job_watch_id' => $jobWatch->id,
            'job_offer_id' => $internationalOffer->id,
        ]);
    }

    public function test_restrict_command_archives_non_moroccan_offers(): void
    {
        JobSource::factory()->create([
            'slug' => 'arbeitnow',
            'is_active' => true,
        ]);

        $moroccanOffer = JobOffer::factory()->create([
            'country_code' => 'MA',
            'status' => 'active',
        ]);

        $internationalOffer = JobOffer::factory()->create([
            'country_code' => 'DE',
            'status' => 'active',
        ]);

        $this->artisan(
            'job-watch:restrict-morocco',
            ['--force' => true]
        )->assertSuccessful();

        $this->assertSame(
            'active',
            $moroccanOffer->fresh()->status
        );

        $this->assertSame(
            'archived',
            $internationalOffer->fresh()->status
        );

        $this->assertFalse(
            JobSource::query()
                ->where('slug', 'arbeitnow')
                ->firstOrFail()
                ->is_active
        );
    }
}
