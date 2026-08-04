<?php

namespace App\Actions\JobWatch;

use App\Models\JobMatch;
use App\Models\JobOffer;
use App\Models\JobWatch;

final class RunJobWatch
{
    public function __construct(
        private readonly MatchJobOffer $matchJobOffer
    ) {}

    /**
     * @return array{
     *     analyzed:int,
     *     matched:int,
     *     stale_removed:int
     * }
     */
    public function execute(JobWatch $jobWatch): array
    {
        $staleRemoved = $this->removeStaleMatches($jobWatch);
        $analyzed = 0;
        $matched = 0;

        JobOffer::query()
            ->where('country_code', 'MA')
            ->where('status', 'active')
            ->where(function ($query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('id')
            ->chunkById(
                100,
                function ($jobOffers) use (
                    $jobWatch,
                    &$analyzed,
                    &$matched
                ): void {
                    foreach ($jobOffers as $jobOffer) {
                        $analyzed++;

                        $result = $this->matchJobOffer->execute(
                            $jobWatch,
                            $jobOffer
                        );

                        if ($result !== null) {
                            $matched++;
                        }
                    }
                }
            );

        $jobWatch->forceFill([
            'last_run_at' => now(),
            'next_run_at' => $jobWatch->status === 'active'
                ? now()->addMinutes(
                    (int) $jobWatch->frequency_minutes
                )
                : null,
        ])->save();

        return [
            'analyzed' => $analyzed,
            'matched' => $matched,
            'stale_removed' => $staleRemoved,
        ];
    }

    private function removeStaleMatches(JobWatch $jobWatch): int
    {
        return JobMatch::query()
            ->where('job_watch_id', $jobWatch->getKey())
            ->whereHas(
                'jobOffer',
                function ($query): void {
                    $query
                        ->where(function ($query): void {
                            $query
                                ->whereNull('country_code')
                                ->orWhere('country_code', '!=', 'MA');
                        })
                        ->orWhere('status', '!=', 'active')
                        ->orWhere(function ($query): void {
                            $query
                                ->whereNotNull('expires_at')
                                ->where('expires_at', '<=', now());
                        });
                }
            )
            ->delete();
    }
}
