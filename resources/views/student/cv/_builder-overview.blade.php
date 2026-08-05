@php
    $completionChecks = collect([
        [
            'label' => 'Identité et contact',
            'done' => filled($profile->full_name)
                && filled($profile->email)
                && filled($profile->phone),
            'target' => 'profile-info',
        ],
        [
            'label' => 'Titre professionnel',
            'done' => filled($profile->headline),
            'target' => 'profile-info',
        ],
        [
            'label' => 'Résumé professionnel',
            'done' => filled($profile->effective_summary),
            'target' => 'profile-info',
        ],
        [
            'label' => 'Formation',
            'done' => $profile->educations->isNotEmpty(),
            'target' => 'educations',
        ],
        [
            'label' => 'Expérience ou stage',
            'done' => $profile->experiences->isNotEmpty(),
            'target' => 'experiences',
        ],
        [
            'label' => 'Au moins cinq compétences',
            'done' => $profile->skills->count() >= 5,
            'target' => 'skills',
        ],
        [
            'label' => 'Langues',
            'done' => $profile->languages->isNotEmpty(),
            'target' => 'languages',
        ],
        [
            'label' => 'Certification ou projet',
            'done' => $profile->certifications->isNotEmpty()
                || $profile->projects->isNotEmpty(),
            'target' => $profile->certifications->isNotEmpty()
                ? 'certifications'
                : 'projects',
        ],
    ]);

    $completedChecks = $completionChecks
        ->where('done', true)
        ->count();

    $completionPercentage = (int) round(
        ($completedChecks / $completionChecks->count()) * 100
    );

    $missingChecks = $completionChecks
        ->where('done', false)
        ->values();

    $builderSections = [
        ['id' => 'exports', 'label' => 'Exports'],
        ['id' => 'profile-info', 'label' => 'Profil'],
        ['id' => 'educations', 'label' => 'Formation'],
        ['id' => 'experiences', 'label' => 'Expérience'],
        ['id' => 'skills', 'label' => 'Compétences'],
        ['id' => 'languages', 'label' => 'Langues'],
        ['id' => 'certifications', 'label' => 'Certifications'],
        ['id' => 'projects', 'label' => 'Projets'],
    ];
@endphp

<section
    class="mb-8 rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm"
    data-cv-builder-overview
    aria-labelledby="cv-builder-overview-title"
>
    <div class="flex flex-wrap items-start justify-between gap-5">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-3">
                <h2
                    id="cv-builder-overview-title"
                    class="text-lg font-bold text-gray-900"
                >
                    Progression du CV
                </h2>

                <span
                    class="rounded-full px-3 py-1 text-xs font-bold
                        {{ $completionPercentage >= 80
                            ? 'bg-green-100 text-green-700'
                            : ($completionPercentage >= 50
                                ? 'bg-amber-100 text-amber-700'
                                : 'bg-red-100 text-red-700') }}"
                    data-cv-completion-percentage
                >
                    {{ $completionPercentage }} %
                </span>
            </div>

            <p class="mt-1 text-sm text-gray-500">
                {{ $completedChecks }} élément(s) complété(s) sur
                {{ $completionChecks->count() }}.
            </p>

            <div
                class="mt-4 h-3 overflow-hidden rounded-full bg-gray-100"
                role="progressbar"
                aria-label="Progression du CV"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $completionPercentage }}"
            >
                <div
                    class="h-full rounded-full transition-all
                        {{ $completionPercentage >= 80
                            ? 'bg-green-500'
                            : ($completionPercentage >= 50
                                ? 'bg-amber-500'
                                : 'bg-red-500') }}"
                    style="width: {{ $completionPercentage }}%"
                ></div>
            </div>
        </div>

        <a
            href="#profile-info"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
        >
            Compléter mon CV
        </a>
    </div>

    <nav
        class="mt-5 flex flex-wrap gap-2 border-t border-gray-100 pt-5"
        aria-label="Navigation rapide du builder CV"
        data-cv-builder-navigation
    >
        @foreach ($builderSections as $section)
            <a
                href="#{{ $section['id'] }}"
                class="rounded-full border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700"
            >
                {{ $section['label'] }}
            </a>
        @endforeach
    </nav>

    @if ($missingChecks->isNotEmpty())
        <div class="mt-5 rounded-xl bg-amber-50 p-4">
            <p class="text-sm font-bold text-amber-900">
                Prochaines actions recommandées
            </p>

            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($missingChecks as $check)
                    <a
                        href="#{{ $check['target'] }}"
                        class="flex items-center gap-2 rounded-lg border border-amber-100 bg-white px-3 py-2 text-sm text-amber-800 transition hover:border-amber-300"
                    >
                        <span aria-hidden="true">○</span>
                        <span>{{ $check['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-5 rounded-xl bg-green-50 p-4 text-sm text-green-800">
            Ton CV contient tous les éléments essentiels. Vérifie maintenant
            l’aperçu et la version ATS avant de l’envoyer.
        </div>
    @endif
</section>
