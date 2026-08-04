<?php

namespace App\Services\JobWatch;

use App\Models\JobSource;
use App\Services\JobWatch\Importers\ArbeitnowJobImporter;
use InvalidArgumentException;

final class JobOfferImporter
{
    public function __construct(
        private readonly ArbeitnowJobImporter $arbeitnowImporter
    ) {
    }

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
        string $sourceSlug = 'arbeitnow',
        int $pages = 1
    ): array {
        $source = $this->source($sourceSlug);

        if (! $source->is_active) {
            throw new InvalidArgumentException(
                sprintf(
                    'La source « %s » est désactivée.',
                    $source->name
                )
            );
        }

        return match ($sourceSlug) {
            'arbeitnow' => $this->arbeitnowImporter->import(
                $source,
                $pages
            ),
            default => throw new InvalidArgumentException(
                sprintf(
                    'Importeur non pris en charge : %s.',
                    $sourceSlug
                )
            ),
        };
    }

    private function source(string $sourceSlug): JobSource
    {
        return match ($sourceSlug) {
            'arbeitnow' => JobSource::query()->firstOrCreate(
                ['slug' => 'arbeitnow'],
                [
                    'name' => 'Arbeitnow',
                    'driver' => 'api',
                    'base_url' => (
                        'https://www.arbeitnow.com/api/job-board-api'
                    ),
                    'is_active' => true,
                    'configuration' => [
                        'provider' => 'arbeitnow',
                        'attribution' => 'Arbeitnow',
                    ],
                ]
            ),
            default => throw new InvalidArgumentException(
                sprintf(
                    'Source inconnue : %s.',
                    $sourceSlug
                )
            ),
        };
    }
}
