<?php

namespace App\Http\Requests\JobWatch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreJobWatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $targetTitles = is_array($this->input('target_titles'))
            ? $this->input('target_titles')
            : $this->splitList($this->input('target_titles_text'));

        $preferredLocations = is_array($this->input('preferred_locations'))
            ? $this->input('preferred_locations')
            : $this->splitList($this->input('preferred_locations_text'));

        $keywords = $this->input('keywords');

        if (! is_array($keywords)) {
            $keywords = array_merge(
                $this->prepareKeywords(
                    $this->input('keywords_include_text'),
                    'include'
                ),
                $this->prepareKeywords(
                    $this->input('keywords_exclude_text'),
                    'exclude'
                )
            );
        }

        $this->merge([
            'target_titles' => $targetTitles,
            'preferred_locations' => $preferredLocations,
            'keywords' => $keywords,
        ]);
    }

    public function rules(): array
    {
        $userId = (int) $this->user()->id;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'cv_profile_id' => [
                Rule::requiredIf(
                    in_array($this->input('source_mode'), ['cv', 'both'], true)
                ),
                'nullable',
                'integer',
                Rule::exists('cv_profiles', 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId)),
            ],

            'source_mode' => [
                'required',
                Rule::in(['cv', 'portfolio', 'both']),
            ],

            'target_titles' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'target_titles.*' => [
                'required',
                'string',
                'max:150',
                'distinct',
            ],

            'preferred_locations' => [
                'nullable',
                'array',
                'max:10',
            ],

            'preferred_locations.*' => [
                'required',
                'string',
                'max:150',
                'distinct',
            ],

            'contract_types' => [
                'nullable',
                'array',
            ],

            'contract_types.*' => [
                'required',
                Rule::in([
                    'cdi',
                    'cdd',
                    'stage',
                    'alternance',
                    'freelance',
                    'interim',
                ]),
                'distinct',
            ],

            'remote_mode' => [
                'nullable',
                Rule::in([
                    'onsite',
                    'hybrid',
                    'remote',
                    'any',
                ]),
            ],

            'minimum_score' => [
                'required',
                'integer',
                'between:0,100',
            ],

            'frequency_minutes' => [
                'required',
                'integer',
                Rule::in([
                    60,
                    360,
                    720,
                    1440,
                    10080,
                ]),
            ],

            'status' => [
                'nullable',
                Rule::in(['active', 'paused']),
            ],

            'keywords' => [
                'nullable',
                'array',
                'max:30',
            ],

            'keywords.*.keyword' => [
                'required',
                'string',
                'max:100',
            ],

            'keywords.*.type' => [
                'required',
                Rule::in([
                    'include',
                    'exclude',
                    'title',
                    'skill',
                    'company',
                    'sector',
                ]),
            ],

            'keywords.*.weight' => [
                'nullable',
                'integer',
                'between:1,10',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cv_profile_id.required' => 'Sélectionnez un CV pour ce mode de veille.',
            'cv_profile_id.exists' => 'Le profil CV sélectionné ne vous appartient pas.',
            'target_titles.required' => 'Ajoutez au moins un intitulé de poste.',
            'target_titles.min' => 'Ajoutez au moins un intitulé de poste.',
            'minimum_score.between' => 'Le score minimum doit être compris entre 0 et 100.',
        ];
    }

    private function splitList(mixed $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return collect(
            preg_split('/[\r\n,;]+/u', $value) ?: []
        )
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique(
                fn (string $item): string => Str::lower(
                    Str::ascii($item)
                )
            )
            ->values()
            ->all();
    }

    private function prepareKeywords(
        mixed $value,
        string $type
    ): array {
        return collect($this->splitList($value))
            ->map(fn (string $keyword): array => [
                'keyword' => $keyword,
                'type' => $type,
                'weight' => 1,
            ])
            ->values()
            ->all();
    }
}
