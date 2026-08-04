<?php

namespace App\Services\JobWatch\Importers;

use App\Models\JobOffer;
use App\Models\JobSource;
use App\Services\JobWatch\MoroccoLocationDetector;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use SplFileObject;

final class MoroccoCsvJobImporter
{
    public function __construct(
        private readonly MoroccoLocationDetector $locationDetector
    ) {}

    /**
     * @return array{
     *     source_id:int,
     *     received:int,
     *     created:int,
     *     updated:int,
     *     skipped:int,
     *     errors:array<int,string>
     * }
     */
    public function import(string $filePath): array
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException(
                'Le fichier CSV est introuvable ou illisible : '.$filePath
            );
        }

        $source = $this->source();
        $delimiter = $this->detectDelimiter($filePath);
        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(
            SplFileObject::READ_CSV
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::DROP_NEW_LINE
        );
        $file->setCsvControl($delimiter);

        $headers = null;

        $statistics = [
            'source_id' => (int) $source->getKey(),
            'received' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($file as $lineNumber => $row) {
            if (! is_array($row) || $this->isEmptyRow($row)) {
                continue;
            }

            if ($headers === null) {
                $headers = $this->normalizeHeaders($row);
                $this->validateHeaders($headers);

                continue;
            }

            $statistics['received']++;

            $row = array_pad($row, count($headers), null);
            $data = array_combine(
                $headers,
                array_slice($row, 0, count($headers))
            );

            if (! is_array($data)) {
                $statistics['skipped']++;
                $statistics['errors'][] = sprintf(
                    'Ligne %d : structure CSV invalide.',
                    $lineNumber + 1
                );

                continue;
            }

            try {
                $result = $this->store(
                    $source,
                    $this->cleanRow($data)
                );

                if ($result === null) {
                    $statistics['skipped']++;
                    $statistics['errors'][] = sprintf(
                        'Ligne %d : titre, localisation ou URL manquant.',
                        $lineNumber + 1
                    );

                    continue;
                }

                $statistics[$result]++;
            } catch (\Throwable $exception) {
                $statistics['skipped']++;
                $statistics['errors'][] = sprintf(
                    'Ligne %d : %s',
                    $lineNumber + 1,
                    $exception->getMessage()
                );
            }
        }

        $source->forceFill([
            'last_success_at' => now(),
            'last_error_at' => null,
            'last_error' => null,
        ])->save();

        return $statistics;
    }

    /**
     * @return 'created'|'updated'|null
     */
    private function store(JobSource $source, array $data): ?string
    {
        $title = trim((string) ($data['title'] ?? ''));
        $location = trim((string) ($data['location'] ?? ''));
        $url = trim((string) ($data['url'] ?? ''));

        if ($title === '' || $location === '' || $url === '') {
            return null;
        }

        if (! $this->locationDetector->isMoroccan(null, $location)) {
            throw new RuntimeException(
                'Localisation non reconnue comme marocaine.'
            );
        }

        $externalId = trim(
            (string) ($data['external_id'] ?? '')
        );

        if ($externalId === '') {
            $externalId = hash(
                'sha256',
                implode('|', [
                    $title,
                    $data['company'] ?? '',
                    $location,
                    $url,
                ])
            );
        }

        return DB::transaction(function () use (
            $source,
            $data,
            $externalId,
            $title,
            $location,
            $url
        ): string {
            $offer = JobOffer::query()->firstOrNew([
                'job_source_id' => $source->getKey(),
                'external_id' => $externalId,
            ]);

            $created = ! $offer->exists;
            $company = trim((string) ($data['company'] ?? ''));

            $offer->fill([
                'title' => $title,
                'normalized_title' => $this->normalize($title),
                'company' => $company !== '' ? $company : null,
                'normalized_company' => $company !== ''
                    ? $this->normalize($company)
                    : null,
                'location' => $location,
                'country_code' => 'MA',
                'description' => $this->nullable(
                    $data['description'] ?? null
                ),
                'requirements' => $this->nullable(
                    $data['requirements'] ?? null
                ),
                'contract_type' => $this->contractType(
                    $data['contract_type'] ?? null
                ),
                'remote_mode' => $this->remoteMode(
                    $data['remote_mode'] ?? null
                ),
                'experience_level' => $this->nullable(
                    $data['experience_level'] ?? null
                ),
                'salary_min' => $this->decimal(
                    $data['salary_min'] ?? null
                ),
                'salary_max' => $this->decimal(
                    $data['salary_max'] ?? null
                ),
                'salary_currency' => $this->currency(
                    $data['salary_currency'] ?? null
                ),
                'url' => $url,
                'canonical_url' => $url,
                'fingerprint' => hash(
                    'sha256',
                    $source->slug.'|'.$externalId
                ),
                'raw_payload' => [
                    'import_type' => 'morocco_csv',
                    'row' => $data,
                ],
                'published_at' => $this->date(
                    $data['published_at'] ?? null
                ),
                'expires_at' => $this->date(
                    $data['expires_at'] ?? null
                ),
                'first_seen_at' => $offer->first_seen_at ?? now(),
                'last_seen_at' => now(),
                'status' => 'active',
            ]);

            $offer->save();

            $this->replaceSkills(
                $offer,
                (string) ($data['skills'] ?? '')
            );

            return $created ? 'created' : 'updated';
        });
    }

    private function source(): JobSource
    {
        return JobSource::query()->firstOrCreate(
            ['slug' => 'morocco-csv'],
            [
                'name' => 'Import CSV Maroc',
                'driver' => 'partner_feed',
                'base_url' => null,
                'is_active' => true,
                'configuration' => [
                    'country_code' => 'MA',
                    'format' => 'csv',
                    'delimiter' => ';',
                ],
            ]
        );
    }

    private function replaceSkills(JobOffer $offer, string $value): void
    {
        $skills = collect(preg_split('/[|,]/u', $value) ?: [])
            ->map(fn (string $skill): string => trim($skill))
            ->filter()
            ->unique(
                fn (string $skill): string => $this->normalize($skill)
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

    private function detectDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return ';';
        }

        $firstLine = fgets($handle) ?: '';
        fclose($handle);

        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');

        return $semicolonCount >= $commaCount ? ';' : ',';
    }

    private function normalizeHeaders(array $headers): array
    {
        return collect($headers)
            ->map(function (mixed $header, int $index): string {
                $value = trim((string) $header);

                if ($index === 0) {
                    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value)
                        ?? $value;
                }

                return Str::snake(
                    $this->locationDetector->normalize($value)
                );
            })
            ->all();
    }

    private function validateHeaders(array $headers): void
    {
        foreach (['title', 'location', 'url'] as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Colonne obligatoire absente : %s.',
                        $requiredHeader
                    )
                );
            }
        }
    }

    private function cleanRow(array $data): array
    {
        return collect($data)
            ->map(
                fn (mixed $value): ?string => is_string($value)
                    ? trim($value)
                    : null
            )
            ->all();
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)
            ->filter(
                fn (mixed $value): bool => trim((string) $value) !== ''
            )
            ->isEmpty();
    }

    private function normalize(string $value): string
    {
        return $this->locationDetector->normalize($value);
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function decimal(mixed $value): ?float
    {
        $value = str_replace(',', '.', trim((string) $value));

        return is_numeric($value) ? (float) $value : null;
    }

    private function currency(mixed $value): ?string
    {
        $value = Str::upper(trim((string) $value));

        return $value !== '' ? Str::limit($value, 3, '') : null;
    }

    private function date(mixed $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function contractType(mixed $value): ?string
    {
        $value = $this->normalize((string) $value);

        return match ($value) {
            'cdi', 'full time', 'full-time' => 'cdi',
            'cdd', 'contract', 'temporary' => 'cdd',
            'stage', 'internship', 'intern' => 'stage',
            'freelance', 'independant', 'independent' => 'freelance',
            'part time', 'part-time', 'temps partiel' => 'part_time',
            default => $value !== '' ? Str::limit($value, 50, '') : null,
        };
    }

    private function remoteMode(mixed $value): string
    {
        $value = $this->normalize((string) $value);

        return match ($value) {
            'remote', 'teletravail', 'télétravail' => 'remote',
            'hybrid', 'hybride' => 'hybrid',
            default => 'onsite',
        };
    }
}
