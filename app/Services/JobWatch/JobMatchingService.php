<?php

namespace App\Services\JobWatch;

final class JobMatchingService
{
    public function __construct(
        private readonly JobTextNormalizer $normalizer
    ) {}

    /**
     * Calcule un score sans écrire en base de données.
     *
     * Structure attendue pour $watch :
     * - target_titles: array
     * - preferred_locations: array
     * - contract_types: array
     * - remote_mode: string|null
     * - minimum_score: int
     * - keywords: array<array{keyword:string,type:string,weight?:int}>
     *
     * Structure attendue pour $offer :
     * - title: string
     * - company: string|null
     * - location: string|null
     * - description: string|null
     * - requirements: string|null
     * - contract_type: string|null
     * - remote_mode: string|null
     * - skills: array<string>
     */
    public function calculate(array $watch, array $offer): array
    {
        $keywords = $this->prepareKeywords($watch['keywords'] ?? []);
        $offerSkills = $this->normalizer->normalizeList(
            $offer['skills'] ?? []
        );

        $searchableText = implode(' ', array_filter([
            $offer['title'] ?? null,
            $offer['company'] ?? null,
            $offer['location'] ?? null,
            $offer['description'] ?? null,
            $offer['requirements'] ?? null,
            implode(' ', $offerSkills),
        ], fn (mixed $value): bool => is_string($value) && trim($value) !== ''));

        /*
         * Pour le score des compétences, le titre, l'entreprise et la
         * localisation ne sont pas considérés comme des preuves de compétence.
         */
        $skillSearchableText = implode(' ', array_filter([
            $offer['description'] ?? null,
            $offer['requirements'] ?? null,
            implode(' ', $offerSkills),
        ], fn (mixed $value): bool => is_string($value) && trim($value) !== ''));

        $excludedKeywords = collect($keywords)
            ->where('type', 'exclude')
            ->filter(
                fn (array $keyword): bool => $this->normalizer->containsPhrase(
                    $searchableText,
                    $keyword['keyword']
                )
            )
            ->pluck('keyword')
            ->values()
            ->all();

        if ($excludedKeywords !== []) {
            return [
                'score' => 0,
                'eligible' => false,
                'rejected' => true,
                'rejection_reasons' => [
                    'excluded_keywords' => $excludedKeywords,
                ],
                'component_scores' => $this->emptyComponentScores(),
                'matched_keywords' => [],
                'matched_skills' => [],
                'missing_skills' => [],
            ];
        }

        $titleScore = $this->titleScore(
            $watch['target_titles'] ?? [],
            $offer['title'] ?? ''
        );

        $locationScore = $this->preferenceScore(
            $watch['preferred_locations'] ?? [],
            $offer['location'] ?? ''
        );

        $contractScore = $this->exactPreferenceScore(
            $watch['contract_types'] ?? [],
            $offer['contract_type'] ?? null
        );

        $remoteScore = $this->remoteScore(
            $watch['remote_mode'] ?? null,
            $offer['remote_mode'] ?? null
        );

        $skillResult = $this->skillScore(
            $keywords,
            $offerSkills,
            $skillSearchableText
        );

        $keywordResult = $this->keywordScore(
            $keywords,
            $searchableText
        );

        $componentScores = [
            'title' => $titleScore,
            'location' => $locationScore,
            'contract' => $contractScore,
            'remote' => $remoteScore,
            'skills' => $skillResult['score'],
            'keywords' => $keywordResult['score'],
        ];

        $score = (int) round(
            ($titleScore * 0.35)
            + ($locationScore * 0.15)
            + ($contractScore * 0.10)
            + ($remoteScore * 0.10)
            + ($skillResult['score'] * 0.20)
            + ($keywordResult['score'] * 0.10)
        );

        $score = max(0, min(100, $score));
        $minimumScore = (int) ($watch['minimum_score'] ?? 0);

        return [
            'score' => $score,
            'eligible' => $score >= $minimumScore,
            'rejected' => false,
            'rejection_reasons' => [],
            'component_scores' => $componentScores,
            'matched_keywords' => $keywordResult['matched'],
            'matched_skills' => $skillResult['matched'],
            'missing_skills' => $skillResult['missing'],
        ];
    }

    private function titleScore(array $targetTitles, string $offerTitle): int
    {
        $normalizedTitles = $this->normalizer->normalizeList($targetTitles);

        if ($normalizedTitles === [] || trim($offerTitle) === '') {
            return 0;
        }

        return collect($normalizedTitles)
            ->map(function (string $targetTitle) use ($offerTitle): int {
                if ($this->normalizer->containsPhrase(
                    $offerTitle,
                    $targetTitle
                )) {
                    return 100;
                }

                return $this->normalizer->tokenOverlapScore(
                    $targetTitle,
                    $offerTitle
                );
            })
            ->max() ?? 0;
    }

    private function preferenceScore(
        array $preferences,
        ?string $candidate
    ): int {
        $normalizedPreferences = $this->normalizer->normalizeList(
            $preferences
        );

        if ($normalizedPreferences === []) {
            return 100;
        }

        if ($candidate === null || trim($candidate) === '') {
            return 0;
        }

        return collect($normalizedPreferences)
            ->map(function (string $preference) use ($candidate): int {
                if (
                    $this->normalizer->containsPhrase(
                        $candidate,
                        $preference
                    )
                    || $this->normalizer->containsPhrase(
                        $preference,
                        $candidate
                    )
                ) {
                    return 100;
                }

                return $this->normalizer->tokenOverlapScore(
                    $preference,
                    $candidate
                );
            })
            ->max() ?? 0;
    }

    private function exactPreferenceScore(
        array $preferences,
        ?string $candidate
    ): int {
        $normalizedPreferences = $this->normalizer->normalizeList(
            $preferences
        );

        if ($normalizedPreferences === []) {
            return 100;
        }

        $normalizedCandidate = $this->normalizer->normalize($candidate);

        if ($normalizedCandidate === '') {
            return 0;
        }

        return in_array(
            $normalizedCandidate,
            $normalizedPreferences,
            true
        ) ? 100 : 0;
    }

    private function remoteScore(
        ?string $expected,
        ?string $actual
    ): int {
        $normalizedExpected = $this->normalizer->normalize($expected);

        if ($normalizedExpected === '' || $normalizedExpected === 'any') {
            return 100;
        }

        $normalizedActual = $this->normalizer->normalize($actual);

        if ($normalizedActual === '') {
            return 0;
        }

        return $normalizedExpected === $normalizedActual ? 100 : 0;
    }

    private function keywordScore(
        array $keywords,
        string $searchableText
    ): array {
        $includedKeywords = collect($keywords)
            ->where('type', 'include')
            ->values();

        if ($includedKeywords->isEmpty()) {
            return [
                'score' => 100,
                'matched' => [],
            ];
        }

        $totalWeight = (int) $includedKeywords->sum('weight');

        $matched = $includedKeywords
            ->filter(
                fn (array $keyword): bool => $this->normalizer->containsPhrase(
                    $searchableText,
                    $keyword['keyword']
                )
            )
            ->values();

        $matchedWeight = (int) $matched->sum('weight');

        return [
            'score' => $totalWeight > 0
                ? (int) round(($matchedWeight / $totalWeight) * 100)
                : 0,
            'matched' => $matched
                ->pluck('keyword')
                ->values()
                ->all(),
        ];
    }

    private function skillScore(
        array $keywords,
        array $offerSkills,
        string $searchableText
    ): array {
        $requiredSkills = collect($keywords)
            ->filter(
                fn (array $keyword): bool => in_array(
                    $keyword['type'],
                    ['skill', 'include'],
                    true
                )
            )
            ->pluck('keyword')
            ->filter()
            ->unique()
            ->values();

        if ($requiredSkills->isEmpty()) {
            return [
                'score' => 100,
                'matched' => [],
                'missing' => [],
            ];
        }

        $matched = $requiredSkills
            ->filter(function (string $skill) use (
                $offerSkills,
                $searchableText
            ): bool {
                return in_array($skill, $offerSkills, true)
                    || $this->normalizer->containsPhrase(
                        $searchableText,
                        $skill
                    );
            })
            ->values();

        $missing = $requiredSkills
            ->diff($matched)
            ->values();

        return [
            'score' => (int) round(
                ($matched->count() / $requiredSkills->count()) * 100
            ),
            'matched' => $matched->all(),
            'missing' => $missing->all(),
        ];
    }

    private function prepareKeywords(array $keywords): array
    {
        return collect($keywords)
            ->map(function (mixed $keyword): ?array {
                if (! is_array($keyword)) {
                    return null;
                }

                $value = $this->normalizer->normalize(
                    (string) ($keyword['keyword'] ?? '')
                );

                if ($value === '') {
                    return null;
                }

                return [
                    'keyword' => $value,
                    'type' => (string) ($keyword['type'] ?? 'include'),
                    'weight' => max(
                        1,
                        (int) ($keyword['weight'] ?? 1)
                    ),
                ];
            })
            ->filter()
            ->unique(
                fn (array $keyword): string => $keyword['type'].'|'
                    .$keyword['keyword']
            )
            ->values()
            ->all();
    }

    private function emptyComponentScores(): array
    {
        return [
            'title' => 0,
            'location' => 0,
            'contract' => 0,
            'remote' => 0,
            'skills' => 0,
            'keywords' => 0,
        ];
    }
}
