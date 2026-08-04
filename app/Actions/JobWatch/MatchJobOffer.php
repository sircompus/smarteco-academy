<?php

namespace App\Actions\JobWatch;

use App\Models\JobMatch;
use App\Models\JobOffer;
use App\Models\JobWatch;
use App\Services\JobWatch\CvRecommendationProfileBuilder;
use App\Services\JobWatch\JobMatchingService;
use Illuminate\Support\Facades\DB;

final class MatchJobOffer
{
    public function __construct(
        private readonly JobMatchingService $matchingService,
        private readonly CvRecommendationProfileBuilder $profileBuilder
    ) {}

    public function execute(
        JobWatch $jobWatch,
        JobOffer $jobOffer
    ): ?JobMatch {
        return DB::transaction(function () use (
            $jobWatch,
            $jobOffer
        ): ?JobMatch {
            if (! $this->isMatchable($jobOffer)) {
                $this->deleteExistingMatch($jobWatch, $jobOffer);

                return null;
            }

            $jobWatch->loadMissing('keywords');
            $jobOffer->loadMissing('skills');

            $recommendationProfile = $this->profileBuilder->build(
                $jobWatch
            );

            $result = $this->matchingService->calculate(
                $this->watchPayload(
                    $jobWatch,
                    $recommendationProfile
                ),
                $this->offerPayload($jobOffer)
            );

            if (
                ($result['rejected'] ?? false)
                || ! ($result['eligible'] ?? false)
            ) {
                $this->deleteExistingMatch($jobWatch, $jobOffer);

                return null;
            }

            $components = $result['component_scores'] ?? [];

            $jobMatch = JobMatch::query()->updateOrCreate(
                [
                    'job_watch_id' => $jobWatch->getKey(),
                    'job_offer_id' => $jobOffer->getKey(),
                ],
                [
                    'score' => (int) ($result['score'] ?? 0),
                    'skill_score' => $this->component(
                        $components,
                        'skills'
                    ),
                    'title_score' => $this->component(
                        $components,
                        'title'
                    ),
                    'experience_score' => null,
                    'portfolio_score' => null,
                    'location_score' => $this->component(
                        $components,
                        'location'
                    ),
                    'contract_score' => $this->component(
                        $components,
                        'contract'
                    ),
                    'language_score' => null,
                    'score_details' => [
                        'components' => $components,
                        'matched_keywords' => array_values(
                            $result['matched_keywords'] ?? []
                        ),
                        'rejection_reasons' => $result[
                            'rejection_reasons'
                        ] ?? [],
                        'eligible' => (bool) (
                            $result['eligible'] ?? false
                        ),
                        'rejected' => (bool) (
                            $result['rejected'] ?? false
                        ),
                        'recommendation_profile' => (
                            $this->profileSummary(
                                $recommendationProfile
                            )
                        ),
                    ],
                    'matched_skills' => array_values(
                        $result['matched_skills'] ?? []
                    ),
                    'missing_skills' => array_values(
                        $result['missing_skills'] ?? []
                    ),
                ]
            );

            return $jobMatch->refresh();
        });
    }

    private function isMatchable(JobOffer $jobOffer): bool
    {
        if ($jobOffer->status !== 'active') {
            return false;
        }

        return $jobOffer->expires_at === null
            || ! $jobOffer->expires_at->isPast();
    }

    private function watchPayload(
        JobWatch $jobWatch,
        array $recommendationProfile
    ): array {
        $keywords = $jobWatch->keywords
            ->map(fn ($keyword): array => [
                'keyword' => $keyword->keyword,
                'type' => $keyword->type,
                'weight' => (int) $keyword->weight,
            ]);

        /*
         * Les compétences du CV et les tags du portfolio deviennent
         * des critères de type "skill". Ils influencent uniquement
         * le score de compétences, pas le score des mots-clés inclus.
         */
        $profileSkills = collect([
            ...($recommendationProfile['skill_names'] ?? []),
            ...($recommendationProfile['portfolio_tags'] ?? []),
        ])
            ->filter()
            ->unique()
            ->map(fn (string $skill): array => [
                'keyword' => $skill,
                'type' => 'skill',
                'weight' => 1,
            ]);

        return [
            'target_titles' => $jobWatch->target_titles ?? [],
            'preferred_locations' => (
                $jobWatch->preferred_locations ?? []
            ),
            'contract_types' => $jobWatch->contract_types ?? [],
            'remote_mode' => $jobWatch->remote_mode,
            'minimum_score' => (int) $jobWatch->minimum_score,
            'keywords' => $keywords
                ->concat($profileSkills)
                ->unique(
                    fn (array $keyword): string => (
                        $keyword['type'].'|'
                        .$keyword['keyword']
                    )
                )
                ->values()
                ->all(),
        ];
    }

    private function offerPayload(JobOffer $jobOffer): array
    {
        return [
            'title' => $jobOffer->title,
            'company' => $jobOffer->company,
            'location' => $jobOffer->location,
            'description' => $jobOffer->description,
            'requirements' => $jobOffer->requirements,
            'contract_type' => $jobOffer->contract_type,
            'remote_mode' => $jobOffer->remote_mode,
            'skills' => $jobOffer->skills
                ->pluck('name')
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function profileSummary(array $profile): array
    {
        return [
            'mode' => $profile['mode'] ?? 'both',
            'profile_found' => (bool) (
                $profile['profile_found'] ?? false
            ),
            'cv_profile_id' => $profile['cv_profile_id'] ?? null,
            'skill_names' => array_values(
                $profile['skill_names'] ?? []
            ),
            'experience_titles' => array_values(
                $profile['experience_titles'] ?? []
            ),
            'language_names' => array_values(
                $profile['language_names'] ?? []
            ),
            'portfolio_tags' => array_values(
                $profile['portfolio_tags'] ?? []
            ),
        ];
    }

    private function component(
        array $components,
        string $key
    ): ?int {
        if (! array_key_exists($key, $components)) {
            return null;
        }

        return max(
            0,
            min(100, (int) $components[$key])
        );
    }

    private function deleteExistingMatch(
        JobWatch $jobWatch,
        JobOffer $jobOffer
    ): void {
        JobMatch::query()
            ->where('job_watch_id', $jobWatch->getKey())
            ->where('job_offer_id', $jobOffer->getKey())
            ->delete();
    }
}
