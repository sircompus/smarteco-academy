<?php

namespace App\Services\JobWatch\Importers;

use App\Models\JobOffer;
use App\Models\JobSource;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class ArbeitnowJobImporter
{
    /**
     * @return array{
     *     source_id:int,
     *     pages:int,
     *     received:int,
     *     created:int,
     *     updated:int,
     *     skipped:int
     * }
     */
    public function import(
        JobSource $source,
        int $pages = 1
    ): array {
        $pages = max(1, min(10, $pages));

        $statistics = [
            'source_id' => (int) $source->getKey(),
            'pages' => 0,
            'received' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        try {
            for ($page = 1; $page <= $pages; $page++) {
                $payload = $this->request()
                    ->get(
                        $source->base_url
                            ?: 'https://www.arbeitnow.com/api/job-board-api',
                        ['page' => $page]
                    )
                    ->throw()
                    ->json();

                $jobs = $payload['data'] ?? [];

                if (! is_array($jobs) || $jobs === []) {
                    break;
                }

                $statistics['pages']++;
                $statistics['received'] += count($jobs);

                foreach ($jobs as $job) {
                    if (! is_array($job)) {
                        $statistics['skipped']++;

                        continue;
                    }

                    $result = $this->store($source, $job);

                    if ($result === null) {
                        $statistics['skipped']++;

                        continue;
                    }

                    $statistics[$result]++;
                }

                if (blank(data_get($payload, 'links.next'))) {
                    break;
                }
            }

            $source->forceFill([
                'last_success_at' => now(),
                'last_error_at' => null,
                'last_error' => null,
            ])->save();

            return $statistics;
        } catch (Throwable $exception) {
            $source->forceFill([
                'last_error_at' => now(),
                'last_error' => Str::limit(
                    $exception->getMessage(),
                    5000,
                    ''
                ),
            ])->save();

            throw $exception;
        }
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->withUserAgent(
                'SmartEco-Academy-JobWatch/1.0'
            )
            ->connectTimeout(10)
            ->timeout(30)
            ->retry(
                2,
                500,
                throw: false
            );
    }

    /**
     * @return 'created'|'updated'|null
     */
    private function store(
        JobSource $source,
        array $job
    ): ?string {
        $externalId = trim((string) ($job['slug'] ?? ''));
        $title = trim((string) ($job['title'] ?? ''));
        $url = trim((string) ($job['url'] ?? ''));

        if ($externalId === '' || $title === '' || $url === '') {
            return null;
        }

        return DB::transaction(function () use (
            $source,
            $job,
            $externalId,
            $title,
            $url
        ): string {
            $offer = JobOffer::query()->firstOrNew([
                'job_source_id' => $source->getKey(),
                'external_id' => $externalId,
            ]);

            $wasRecentlyCreated = ! $offer->exists;
            $description = $this->plainText(
                (string) ($job['description'] ?? '')
            );
            $company = trim(
                (string) ($job['company_name'] ?? '')
            );
            $location = trim(
                (string) ($job['location'] ?? '')
            );

            $offer->fill([
                'title' => $title,
                'normalized_title' => $this->normalize($title),
                'company' => $company !== '' ? $company : null,
                'normalized_company' => $company !== ''
                    ? $this->normalize($company)
                    : null,
                'location' => $location !== ''
                    ? $location
                    : null,
                'country_code' => null,
                'description' => $description !== ''
                    ? $description
                    : null,
                'requirements' => null,
                'contract_type' => $this->contractType(
                    $job['job_types'] ?? []
                ),
                'remote_mode' => $this->remoteMode(
                    $job,
                    $description,
                    $location
                ),
                'experience_level' => $this->experienceLevel(
                    $job['job_types'] ?? []
                ),
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => null,
                'url' => $url,
                'canonical_url' => $url,
                'fingerprint' => hash(
                    'sha256',
                    $source->slug.'|'.$externalId
                ),
                'raw_payload' => $job,
                'published_at' => $this->publishedAt(
                    $job['created_at'] ?? null
                ),
                'expires_at' => null,
                'first_seen_at' => $offer->first_seen_at ?? now(),
                'last_seen_at' => now(),
                'status' => 'active',
            ]);

            $offer->save();

            $this->replaceSkills(
                $offer,
                $job['tags'] ?? []
            );

            return $wasRecentlyCreated
                ? 'created'
                : 'updated';
        });
    }

    private function replaceSkills(
        JobOffer $offer,
        mixed $tags
    ): void {
        $skills = collect(is_array($tags) ? $tags : [])
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter()
            ->unique(
                fn (string $tag): string => $this->normalize($tag)
            )
            ->values();

        $offer->skills()->delete();

        if ($skills->isEmpty()) {
            return;
        }

        $offer->skills()->createMany(
            $skills
                ->map(fn (string $skill): array => [
                    'name' => $skill,
                    'normalized_name' => $this->normalize($skill),
                    'importance' => 3,
                    'is_required' => false,
                ])
                ->all()
        );
    }

    private function contractType(mixed $jobTypes): ?string
    {
        $text = $this->normalize(
            implode(
                ' ',
                is_array($jobTypes) ? $jobTypes : []
            )
        );

        return match (true) {
            str_contains($text, 'intern') => 'stage',
            str_contains($text, 'part time'),
            str_contains($text, 'part-time') => 'part_time',
            str_contains($text, 'freelance') => 'freelance',
            str_contains($text, 'contract'),
            str_contains($text, 'temporary') => 'cdd',
            str_contains($text, 'full time'),
            str_contains($text, 'full-time') => 'cdi',
            default => null,
        };
    }

    private function remoteMode(
        array $job,
        string $description,
        string $location
    ): string {
        if ((bool) ($job['remote'] ?? false)) {
            return 'remote';
        }

        $text = $this->normalize(
            $location.' '.$description
        );

        if (str_contains($text, 'hybrid')) {
            return 'hybrid';
        }

        return 'onsite';
    }

    private function experienceLevel(mixed $jobTypes): ?string
    {
        $types = collect(
            is_array($jobTypes) ? $jobTypes : []
        )
            ->map(fn (mixed $type): string => trim((string) $type))
            ->filter()
            ->implode(', ');

        return $types !== ''
            ? Str::limit($types, 50, '')
            : null;
    }

    private function publishedAt(mixed $timestamp): ?Carbon
    {
        if (! is_numeric($timestamp)) {
            return null;
        }

        return Carbon::createFromTimestampUTC(
            (int) $timestamp
        );
    }

    private function plainText(string $html): string
    {
        $decoded = html_entity_decode(
            $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $decoded = html_entity_decode(
            $decoded,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = strip_tags($decoded);

        return trim(
            preg_replace('/\s+/u', ' ', $text) ?? ''
        );
    }

    private function normalize(string $value): string
    {
        return Str::lower(
            trim(Str::ascii($value))
        );
    }
}
