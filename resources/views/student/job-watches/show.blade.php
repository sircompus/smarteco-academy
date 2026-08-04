@extends('layouts.student')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $jobWatch->name }}
                </h1>

                <p class="mt-1 text-gray-600">
                    Créée le {{ $jobWatch->created_at->format('d/m/Y') }}
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    Dernière recherche :
                    {{ $jobWatch->last_run_at?->format('d/m/Y à H:i')
                        ?? 'jamais' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($jobWatch->status !== 'disabled')
                    <form
                        method="POST"
                        action="{{ route(
                            'student.job-watches.run',
                            $jobWatch
                        ) }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700"
                        >
                            Lancer la recherche
                        </button>
                    </form>
                @endif

                <a
                    href="{{ route(
                        'student.job-watches.edit',
                        $jobWatch
                    ) }}"
                    class="rounded-lg border px-4 py-2"
                >
                    Modifier
                </a>

                @if ($jobWatch->status !== 'disabled')
                    <form
                        method="POST"
                        action="{{ route(
                            'student.job-watches.status',
                            $jobWatch
                        ) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="rounded-lg border px-4 py-2"
                        >
                            {{ $jobWatch->status === 'active'
                                ? 'Suspendre'
                                : 'Réactiver' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="font-semibold text-red-800">
                Recherche limitée au Maroc
            </p>

            <p class="mt-1 text-sm text-red-700">
                Seules les offres avec le code pays MA sont analysées.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-semibold">
                    Critères de recherche
                </h2>

                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">Source</dt>
                        <dd class="font-medium">
                            {{ match ($jobWatch->source_mode) {
                                'cv' => 'CV',
                                'portfolio' => 'Portfolio',
                                default => 'CV et portfolio',
                            } }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Profil utilisé
                        </dt>
                        <dd class="font-medium">
                            {{ $jobWatch->cvProfile?->headline
                                ?? $jobWatch->cvProfile?->full_name
                                ?? 'Aucun profil sélectionné' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Score minimum
                        </dt>
                        <dd class="font-medium">
                            {{ $jobWatch->minimum_score }} %
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Statut</dt>
                        <dd class="font-medium">
                            {{ match ($jobWatch->status) {
                                'active' => 'Active',
                                'paused' => 'Suspendue',
                                default => 'Désactivée',
                            } }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Intitulés</dt>
                        <dd class="font-medium">
                            {{ implode(
                                ', ',
                                $jobWatch->target_titles ?? []
                            ) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Localisations
                        </dt>
                        <dd class="font-medium">
                            {{ implode(
                                ', ',
                                $jobWatch->preferred_locations ?? []
                            ) ?: 'Tout le Maroc' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Contrats</dt>
                        <dd class="font-medium">
                            {{ implode(
                                ', ',
                                $jobWatch->contract_types ?? []
                            ) ?: 'Tous' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Mode de travail
                        </dt>
                        <dd class="font-medium">
                            {{ $jobWatch->remote_mode ?? 'Tous' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <aside class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Mots-clés</h2>

                <div class="mt-4">
                    <h3 class="text-sm font-medium text-green-700">
                        À inclure
                    </h3>

                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse (
                            $jobWatch->keywords->where(
                                'type',
                                'include'
                            ) as $keyword
                        )
                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-800">
                                {{ $keyword->keyword }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">
                                Aucun
                            </span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-5">
                    <h3 class="text-sm font-medium text-red-700">
                        À exclure
                    </h3>

                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse (
                            $jobWatch->keywords->where(
                                'type',
                                'exclude'
                            ) as $keyword
                        )
                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-800">
                                {{ $keyword->keyword }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">
                                Aucun
                            </span>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>

        <section class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">
                        Importer des offres marocaines
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Importez un fichier CSV autorisé. Les offres sont
                        enregistrées avec le code pays MA, puis la veille
                        est relancée automatiquement.
                    </p>
                </div>

                <a
                    href="{{ asset(
                        'templates/offres-maroc-modele.csv'
                    ) }}"
                    download
                    class="text-sm font-medium text-blue-700 hover:underline"
                >
                    Télécharger le modèle CSV
                </a>
            </div>

            @error('offers_csv')
                <p class="mt-4 rounded-lg bg-red-100 p-3 text-sm text-red-800">
                    {{ $message }}
                </p>
            @enderror

            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ route(
                    'student.job-watches.import-morocco',
                    $jobWatch
                ) }}"
                class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end"
            >
                @csrf

                <div class="flex-1">
                    <label
                        for="offers_csv"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Fichier CSV
                    </label>

                    <input
                        id="offers_csv"
                        name="offers_csv"
                        type="file"
                        accept=".csv,.txt,text/csv"
                        required
                        class="mt-2 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm"
                    >
                </div>

                <button
                    type="submit"
                    class="rounded-lg bg-green-700 px-5 py-2.5 font-medium text-white hover:bg-green-800"
                >
                    Importer et analyser
                </button>
            </form>

            <p class="mt-3 text-xs text-gray-500">
                Colonnes obligatoires : title, location et url.
                Séparateur recommandé : point-virgule.
            </p>
        </section>

        <section class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">
                    Offres correspondantes
                </h2>

                <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">
                    {{ $jobWatch->matches->where('status', '!=', 'dismissed')->count() }}
                    correspondance(s)
                </span>
            </div>

            <div class="mt-4">
                @forelse ($jobWatch->matches as $match)
                    @continue($match->status === 'dismissed')

                    <article class="border-b py-5 last:border-b-0">
                        <div class="flex flex-wrap justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-gray-900">
                                        {{ $match->jobOffer->title }}
                                    </h3>

                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                        {{ match ($match->status) {
                                            'saved' => 'bg-blue-100 text-blue-800',
                                            'viewed' => 'bg-gray-100 text-gray-700',
                                            'applied' => 'bg-purple-100 text-purple-800',
                                            default => 'bg-green-100 text-green-800',
                                        } }}"
                                    >
                                        {{ match ($match->status) {
                                            'saved' => 'Enregistrée',
                                            'viewed' => 'Consultée',
                                            'applied' => 'Candidature envoyée',
                                            default => 'Nouvelle',
                                        } }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $match->jobOffer->company
                                        ?? 'Entreprise non précisée' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $match->jobOffer->location
                                        ?? 'Localisation non précisée' }}

                                    @if ($match->jobOffer->contract_type)
                                        · {{ strtoupper(
                                            $match->jobOffer->contract_type
                                        ) }}
                                    @endif

                                    @if ($match->jobOffer->remote_mode)
                                        · {{ $match->jobOffer->remote_mode }}
                                    @endif
                                </p>
                            </div>

                            <div class="text-right">
                                <strong class="text-xl text-blue-700">
                                    {{ $match->score }} %
                                </strong>

                                <p class="mt-1 text-xs text-gray-500">
                                    Score de correspondance
                                </p>
                            </div>
                        </div>

                        @if (! empty($match->matched_skills))
                            <div class="mt-3">
                                <p class="text-xs font-medium uppercase text-gray-500">
                                    Compétences trouvées
                                </p>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach (
                                        $match->matched_skills as $skill
                                    )
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs text-green-800">
                                            {{ $skill }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (! empty($match->missing_skills))
                            <div class="mt-3">
                                <p class="text-xs font-medium uppercase text-gray-500">
                                    Compétences manquantes
                                </p>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach (
                                        $match->missing_skills as $skill
                                    )
                                        <span class="rounded-full bg-orange-100 px-2.5 py-1 text-xs text-orange-800">
                                            {{ $skill }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($match->jobOffer->url)
                                <a
                                    href="{{ $match->jobOffer->url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="rounded-lg border px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50"
                                >
                                    Voir l’offre
                                </a>
                            @endif

                            @foreach ([
                                'viewed' => 'Marquer consultée',
                                'saved' => 'Enregistrer',
                                'applied' => 'J’ai postulé',
                                'dismissed' => 'Ignorer',
                            ] as $status => $label)
                                @if ($match->status !== $status)
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'student.job-watches.matches.status',
                                            [$jobWatch, $match]
                                        ) }}"
                                        @if ($status === 'dismissed')
                                            onsubmit="return confirm('Ignorer cette offre ?');"
                                        @endif
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="{{ $status }}"
                                        >

                                        <button
                                            type="submit"
                                            class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50"
                                        >
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg bg-gray-50 p-6 text-center">
                        <p class="text-gray-600">
                            Aucune offre marocaine correspondante.
                        </p>

                        <p class="mt-2 text-sm text-gray-500">
                            Importez un CSV marocain ou cliquez sur
                            « Lancer la recherche ».
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="mt-8 border-t pt-6">
            <form
                method="POST"
                action="{{ route(
                    'student.job-watches.destroy',
                    $jobWatch
                ) }}"
                onsubmit="return confirm(
                    'Supprimer définitivement cette veille ?'
                );"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="rounded-lg bg-red-600 px-5 py-2.5 font-medium text-white"
                >
                    Supprimer la veille
                </button>
            </form>
        </div>
    </div>
@endsection
