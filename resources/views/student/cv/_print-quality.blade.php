@php
    $isAdminBuilder = isset($targetUser);

    $classicParameters = $isAdminBuilder
        ? ['user' => $targetUser, 'template' => 'classique']
        : ['template' => 'classique'];

    $modernParameters = $isAdminBuilder
        ? ['user' => $targetUser, 'template' => 'moderne']
        : ['template' => 'moderne'];

    $atsParameters = $isAdminBuilder
        ? ['user' => $targetUser]
        : [];

    $cvRoute = $isAdminBuilder
        ? 'admin.cv.download.cv'
        : 'student.cv.download.cv';

    $atsRoute = $isAdminBuilder
        ? 'admin.cv.download.ats'
        : 'student.cv.download.ats';

    $printChecks = collect([
        [
            'label' => 'Nom et adresse e-mail',
            'done' => filled($profile->full_name)
                && filled($profile->email),
        ],
        [
            'label' => 'Téléphone',
            'done' => filled($profile->phone),
        ],
        [
            'label' => 'Titre professionnel',
            'done' => filled($profile->headline),
        ],
        [
            'label' => 'Résumé professionnel',
            'done' => filled($profile->effective_summary),
        ],
        [
            'label' => 'Formation',
            'done' => $profile->educations->isNotEmpty(),
        ],
        [
            'label' => 'Expérience ou stage',
            'done' => $profile->experiences->isNotEmpty(),
        ],
        [
            'label' => 'Compétences',
            'done' => $profile->skills->isNotEmpty(),
        ],
        [
            'label' => 'Langues',
            'done' => $profile->languages->isNotEmpty(),
        ],
    ]);

    $readyChecks = $printChecks->where('done', true)->count();
    $isReadyToExport = $readyChecks === $printChecks->count();
@endphp

<section
    class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
    data-cv-print-quality
    aria-labelledby="cv-print-quality-title"
>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2
                    id="cv-print-quality-title"
                    class="text-lg font-bold text-gray-900"
                >
                    Vérification avant export
                </h2>

                <span
                    class="rounded-full px-3 py-1 text-xs font-bold
                        {{ $isReadyToExport
                            ? 'bg-green-100 text-green-700'
                            : 'bg-amber-100 text-amber-700' }}"
                >
                    {{ $readyChecks }}/{{ $printChecks->count() }}
                </span>
            </div>

            <p class="mt-1 text-sm text-gray-500">
                Contrôle les trois versions avant l’envoi à un recruteur.
            </p>
        </div>

        <p
            class="rounded-lg px-3 py-2 text-sm font-semibold
                {{ $isReadyToExport
                    ? 'bg-green-50 text-green-700'
                    : 'bg-amber-50 text-amber-700' }}"
        >
            {{ $isReadyToExport
                ? 'CV prêt à exporter'
                : 'Informations à compléter' }}
        </p>
    </div>

    <div class="mt-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($printChecks as $check)
            <div
                class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm
                    {{ $check['done']
                        ? 'border-green-100 bg-green-50 text-green-800'
                        : 'border-gray-200 bg-gray-50 text-gray-600' }}"
            >
                <span aria-hidden="true">
                    {{ $check['done'] ? '✓' : '○' }}
                </span>

                <span>{{ $check['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-3 md:grid-cols-3">
        <a
            href="{{ route($cvRoute, $classicParameters) }}"
            target="_blank"
            rel="noopener"
            class="rounded-xl border border-gray-200 bg-white p-4 transition hover:border-indigo-300 hover:bg-indigo-50"
        >
            <p class="font-bold text-gray-900">
                Vérifier le CV classique
            </p>

            <p class="mt-1 text-xs leading-5 text-gray-500">
                Contrôle les marges A4, les retours à la ligne et la photo.
            </p>
        </a>

        <a
            href="{{ route($cvRoute, $modernParameters) }}"
            target="_blank"
            rel="noopener"
            class="rounded-xl border border-gray-200 bg-white p-4 transition hover:border-indigo-300 hover:bg-indigo-50"
        >
            <p class="font-bold text-gray-900">
                Vérifier le CV moderne
            </p>

            <p class="mt-1 text-xs leading-5 text-gray-500">
                Contrôle la colonne latérale et la lisibilité des sections.
            </p>
        </a>

        <a
            href="{{ route($atsRoute, $atsParameters) }}"
            target="_blank"
            rel="noopener"
            class="rounded-xl border border-gray-200 bg-white p-4 transition hover:border-green-300 hover:bg-green-50"
        >
            <p class="font-bold text-gray-900">
                Vérifier la version ATS
            </p>

            <p class="mt-1 text-xs leading-5 text-gray-500">
                Contrôle la version sans photo et sans éléments décoratifs.
            </p>
        </a>
    </div>

    <p class="mt-4 text-xs leading-5 text-gray-500">
        Dans la fenêtre d’impression, choisis le format A4, l’échelle
        100 % et active les graphiques d’arrière-plan pour le modèle
        moderne.
    </p>
</section>
