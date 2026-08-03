@php
    $watch = $jobWatch ?? null;

    $targetTitlesText = old(
        'target_titles_text',
        implode(PHP_EOL, $watch?->target_titles ?? [])
    );

    $preferredLocationsText = old(
        'preferred_locations_text',
        implode(PHP_EOL, $watch?->preferred_locations ?? [])
    );

    $selectedContracts = old(
        'contract_types',
        $watch?->contract_types ?? []
    );

    $includeKeywordsText = old(
        'keywords_include_text',
        $watch
            ? $watch->keywords
                ->where('type', 'include')
                ->pluck('keyword')
                ->implode(PHP_EOL)
            : ''
    );

    $excludeKeywordsText = old(
        'keywords_exclude_text',
        $watch
            ? $watch->keywords
                ->where('type', 'exclude')
                ->pluck('keyword')
                ->implode(PHP_EOL)
            : ''
    );
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
        <p class="font-semibold">Veuillez corriger les erreurs suivantes :</p>

        <ul class="mt-2 list-inside list-disc text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="mb-1 block font-medium">Nom de la veille</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $watch?->name) }}"
            required
            maxlength="150"
            class="w-full rounded-lg border-gray-300"
        >
    </div>

    <div>
        <label for="source_mode" class="mb-1 block font-medium">Source du profil</label>
        <select id="source_mode" name="source_mode" required class="w-full rounded-lg border-gray-300">
            <option value="cv" @selected(old('source_mode', $watch?->source_mode ?? 'both') === 'cv')>
                CV uniquement
            </option>
            <option value="portfolio" @selected(old('source_mode', $watch?->source_mode ?? 'both') === 'portfolio')>
                Portfolio uniquement
            </option>
            <option value="both" @selected(old('source_mode', $watch?->source_mode ?? 'both') === 'both')>
                CV et portfolio
            </option>
        </select>
    </div>

    <div>
        <label for="cv_profile_id" class="mb-1 block font-medium">Profil CV</label>
        <select id="cv_profile_id" name="cv_profile_id" class="w-full rounded-lg border-gray-300">
            <option value="">Aucun profil sélectionné</option>

            @foreach ($cvProfiles as $cvProfile)
                <option
                    value="{{ $cvProfile->id }}"
                    @selected((string) old('cv_profile_id', $watch?->cv_profile_id) === (string) $cvProfile->id)
                >
                    {{ $cvProfile->title ?? 'CV nº '.$cvProfile->id }}
                </option>
            @endforeach
        </select>

        <p class="mt-1 text-sm text-gray-500">
            Obligatoire pour les modes CV et CV + portfolio.
        </p>
    </div>

    <div class="md:col-span-2">
        <label for="target_titles_text" class="mb-1 block font-medium">
            Intitulés de postes recherchés
        </label>
        <textarea
            id="target_titles_text"
            name="target_titles_text"
            rows="4"
            required
            class="w-full rounded-lg border-gray-300"
            placeholder="Développeur Laravel&#10;Développeur PHP"
        >{{ $targetTitlesText }}</textarea>
        <p class="mt-1 text-sm text-gray-500">Un intitulé par ligne.</p>
    </div>

    <div class="md:col-span-2">
        <label for="preferred_locations_text" class="mb-1 block font-medium">
            Localisations préférées
        </label>
        <textarea
            id="preferred_locations_text"
            name="preferred_locations_text"
            rows="3"
            class="w-full rounded-lg border-gray-300"
            placeholder="Casablanca&#10;Rabat"
        >{{ $preferredLocationsText }}</textarea>
    </div>

    <fieldset class="md:col-span-2">
        <legend class="mb-2 font-medium">Types de contrat</legend>

        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
            @foreach ([
                'cdi' => 'CDI',
                'cdd' => 'CDD',
                'stage' => 'Stage',
                'alternance' => 'Alternance',
                'freelance' => 'Freelance',
                'interim' => 'Intérim',
            ] as $value => $label)
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="contract_types[]"
                        value="{{ $value }}"
                        @checked(in_array($value, $selectedContracts, true))
                        class="rounded border-gray-300"
                    >
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div>
        <label for="remote_mode" class="mb-1 block font-medium">Mode de travail</label>
        <select id="remote_mode" name="remote_mode" class="w-full rounded-lg border-gray-300">
            @foreach ([
                'any' => 'Tous les modes',
                'onsite' => 'Sur site',
                'hybrid' => 'Hybride',
                'remote' => 'Télétravail',
            ] as $value => $label)
                <option value="{{ $value }}" @selected(old('remote_mode', $watch?->remote_mode ?? 'any') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="minimum_score" class="mb-1 block font-medium">Score minimum</label>
        <input
            id="minimum_score"
            name="minimum_score"
            type="number"
            min="0"
            max="100"
            value="{{ old('minimum_score', $watch?->minimum_score ?? 70) }}"
            required
            class="w-full rounded-lg border-gray-300"
        >
    </div>

    <div>
        <label for="frequency_minutes" class="mb-1 block font-medium">Fréquence de recherche</label>
        <select id="frequency_minutes" name="frequency_minutes" required class="w-full rounded-lg border-gray-300">
            @foreach ([
                60 => 'Toutes les heures',
                360 => 'Toutes les 6 heures',
                720 => 'Toutes les 12 heures',
                1440 => 'Une fois par jour',
                10080 => 'Une fois par semaine',
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected((int) old('frequency_minutes', $watch?->frequency_minutes ?? 1440) === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status" class="mb-1 block font-medium">Statut initial</label>
        <select id="status" name="status" class="w-full rounded-lg border-gray-300">
            <option value="active" @selected(old('status', $watch?->status ?? 'active') === 'active')>
                Active
            </option>
            <option value="paused" @selected(old('status', $watch?->status ?? 'active') === 'paused')>
                Suspendue
            </option>
        </select>
    </div>

    <div>
        <label for="keywords_include_text" class="mb-1 block font-medium">
            Mots-clés à inclure
        </label>
        <textarea
            id="keywords_include_text"
            name="keywords_include_text"
            rows="5"
            class="w-full rounded-lg border-gray-300"
            placeholder="Laravel&#10;PHP&#10;MySQL"
        >{{ $includeKeywordsText }}</textarea>
    </div>

    <div>
        <label for="keywords_exclude_text" class="mb-1 block font-medium">
            Mots-clés à exclure
        </label>
        <textarea
            id="keywords_exclude_text"
            name="keywords_exclude_text"
            rows="5"
            class="w-full rounded-lg border-gray-300"
            placeholder="Senior&#10;Directeur"
        >{{ $excludeKeywordsText }}</textarea>
    </div>
</div>

<div class="mt-8 flex flex-wrap gap-3">
    <button
        type="submit"
        class="rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700"
    >
        {{ $submitLabel }}
    </button>

    <a
        href="{{ route('student.job-watches.index') }}"
        class="rounded-lg border border-gray-300 px-5 py-2.5 hover:bg-gray-50"
    >
        Annuler
    </a>
</div>
