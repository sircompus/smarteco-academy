<?php

namespace App\Services\JobWatch;

use App\Models\CvProfile;
use App\Models\JobWatch;

final class CvRecommendationProfileBuilder
{
    public function __construct(
        private readonly JobTextNormalizer $normalizer
    ) {}

    public function build(JobWatch $jobWatch): array
    {
        $mode = $this->normalizeMode($jobWatch->source_mode);

        if ($jobWatch->cv_profile_id === null) {
            return $this->emptyProfile($mode);
        }

        /*
         * La requête vérifie que le CV appartient bien au propriétaire
         * de la veille. Elle ne dépend pas du nom d'une relation Eloquent
         * éventuellement différente dans JobWatch.
         */
        $profile = CvProfile::query()
            ->whereKey($jobWatch->cv_profile_id)
            ->where('user_id', $jobWatch->user_id)
            ->first();

        if (! $profile instanceof CvProfile) {
            return $this->emptyProfile($mode);
        }

        $includeCv = in_array($mode, ['cv', 'both'], true);
        $includePortfolio = in_array(
            $mode,
            ['portfolio', 'both'],
            true
        );

        $relations = [];

        if ($includeCv) {
            $relations = [
                'skills',
                'experiences',
                'educations',
                'languages',
                'certifications',
            ];
        }

        if ($includePortfolio) {
            $relations[] = 'projects';
        }

        if ($relations !== []) {
            $profile->loadMissing($relations);
        }

        $skills = $includeCv
            ? $profile->skills
                ->map(fn ($skill): array => [
                    'name' => $skill->name,
                    'normalized_name' => $this->normalizer->normalize(
                        $skill->name
                    ),
                    'category' => $skill->category,
                    'level' => $skill->level,
                    'level_percent' => $skill->level_percent,
                ])
                ->values()
                ->all()
            : [];

        $experiences = $includeCv
            ? $profile->experiences
                ->map(fn ($experience): array => [
                    'company' => $experience->company,
                    'position' => $experience->position,
                    'location' => $experience->location,
                    'description' => $experience->description,
                    'start_date' => (
                        $experience->start_date?->toDateString()
                    ),
                    'end_date' => (
                        $experience->end_date?->toDateString()
                    ),
                    'is_current' => (bool) $experience->is_current,
                ])
                ->values()
                ->all()
            : [];

        $educations = $includeCv
            ? $profile->educations
                ->map(fn ($education): array => [
                    'institution' => $education->institution,
                    'degree' => $education->degree,
                    'field_of_study' => $education->field_of_study,
                    'description' => $education->description,
                    'start_date' => (
                        $education->start_date?->toDateString()
                    ),
                    'end_date' => (
                        $education->end_date?->toDateString()
                    ),
                    'is_current' => (bool) $education->is_current,
                ])
                ->values()
                ->all()
            : [];

        $languages = $includeCv
            ? $profile->languages
                ->map(fn ($language): array => [
                    'name' => $language->name,
                    'normalized_name' => $this->normalizer->normalize(
                        $language->name
                    ),
                    'level' => $language->level,
                ])
                ->values()
                ->all()
            : [];

        $certifications = $includeCv
            ? $profile->certifications
                ->map(fn ($certification): array => [
                    'name' => $certification->name,
                    'issuer' => $certification->issuer,
                    'date_obtained' => (
                        $certification->date_obtained?->toDateString()
                    ),
                ])
                ->values()
                ->all()
            : [];

        $projects = $includePortfolio
            ? $profile->projects
                ->map(fn ($project): array => [
                    'title' => $project->title,
                    'description' => $project->description,
                    'tags' => $project->tags_array,
                    'project_url' => $project->project_url,
                    'repo_url' => $project->repo_url,
                ])
                ->values()
                ->all()
            : [];

        $skillNames = $this->normalizer->normalizeList(
            array_column($skills, 'name')
        );

        $languageNames = $this->normalizer->normalizeList(
            array_column($languages, 'name')
        );

        $experienceTitles = $this->normalizer->normalizeList(
            array_column($experiences, 'position')
        );

        $portfolioTags = $this->normalizer->normalizeList(
            collect($projects)
                ->flatMap(
                    fn (array $project): array => $project['tags']
                )
                ->all()
        );

        $cvText = $includeCv
            ? $this->buildCvText(
                $profile,
                $skills,
                $experiences,
                $educations,
                $languages,
                $certifications
            )
            : '';

        $portfolioText = $includePortfolio
            ? $this->buildPortfolioText($projects)
            : '';

        return [
            'mode' => $mode,
            'profile_found' => true,
            'cv_profile_id' => $profile->getKey(),
            'headline' => $includeCv ? $profile->headline : null,
            'summary' => $includeCv ? $profile->summary : null,
            'skills' => $skills,
            'skill_names' => $skillNames,
            'experiences' => $experiences,
            'experience_titles' => $experienceTitles,
            'educations' => $educations,
            'languages' => $languages,
            'language_names' => $languageNames,
            'certifications' => $certifications,
            'projects' => $projects,
            'portfolio_tags' => $portfolioTags,
            'cv_text' => $cvText,
            'portfolio_text' => $portfolioText,
            'searchable_text' => $this->normalizer->normalize(
                trim($cvText.' '.$portfolioText)
            ),
        ];
    }

    private function normalizeMode(?string $mode): string
    {
        return in_array($mode, ['cv', 'portfolio', 'both'], true)
            ? $mode
            : 'both';
    }

    private function buildCvText(
        CvProfile $profile,
        array $skills,
        array $experiences,
        array $educations,
        array $languages,
        array $certifications
    ): string {
        $parts = [
            $profile->headline,
            $profile->summary,
        ];

        foreach ($skills as $skill) {
            $parts[] = implode(' ', array_filter([
                $skill['name'],
                $skill['category'],
                $skill['level'],
            ]));
        }

        foreach ($experiences as $experience) {
            $parts[] = implode(' ', array_filter([
                $experience['position'],
                $experience['company'],
                $experience['location'],
                $experience['description'],
            ]));
        }

        foreach ($educations as $education) {
            $parts[] = implode(' ', array_filter([
                $education['degree'],
                $education['field_of_study'],
                $education['institution'],
                $education['description'],
            ]));
        }

        foreach ($languages as $language) {
            $parts[] = implode(' ', array_filter([
                $language['name'],
                $language['level'],
            ]));
        }

        foreach ($certifications as $certification) {
            $parts[] = implode(' ', array_filter([
                $certification['name'],
                $certification['issuer'],
            ]));
        }

        return trim(
            implode(' ', array_filter(
                $parts,
                fn (mixed $value): bool => is_string($value)
                    && trim($value) !== ''
            ))
        );
    }

    private function buildPortfolioText(array $projects): string
    {
        $parts = [];

        foreach ($projects as $project) {
            $parts[] = implode(' ', array_filter([
                $project['title'],
                $project['description'],
                implode(' ', $project['tags']),
            ]));
        }

        return trim(implode(' ', array_filter($parts)));
    }

    private function emptyProfile(string $mode): array
    {
        return [
            'mode' => $mode,
            'profile_found' => false,
            'cv_profile_id' => null,
            'headline' => null,
            'summary' => null,
            'skills' => [],
            'skill_names' => [],
            'experiences' => [],
            'experience_titles' => [],
            'educations' => [],
            'languages' => [],
            'language_names' => [],
            'certifications' => [],
            'projects' => [],
            'portfolio_tags' => [],
            'cv_text' => '',
            'portfolio_text' => '',
            'searchable_text' => '',
        ];
    }
}
