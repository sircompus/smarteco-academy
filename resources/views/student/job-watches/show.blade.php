@extends('layouts.student')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-100 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-100 p-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $jobWatch->name }}
                </h1>
                <p class="mt-1 text-gray-600">
                    Créée le {{ $jobWatch->created_at->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('student.job-watches.edit', $jobWatch) }}"
                    class="rounded-lg border px-4 py-2"
                >
                    Modifier
                </a>

                @if ($jobWatch->status !== 'disabled')
                    <form
                        method="POST"
                        action="{{ route('student.job-watches.status', $jobWatch) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="rounded-lg border px-4 py-2">
                            {{ $jobWatch->status === 'active' ? 'Suspendre' : 'Réactiver' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-semibold">Critères de recherche</h2>

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
                        <dt class="text-sm text-gray-500">Score minimum</dt>
                        <dd class="font-medium">{{ $jobWatch->minimum_score }} %</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Intitulés</dt>
                        <dd class="font-medium">
                            {{ implode(', ', $jobWatch->target_titles ?? []) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Localisations</dt>
                        <dd class="font-medium">
                            {{ implode(', ', $jobWatch->preferred_locations ?? []) ?: 'Toutes' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Contrats</dt>
                        <dd class="font-medium">
                            {{ implode(', ', $jobWatch->contract_types ?? []) ?: 'Tous' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Mode de travail</dt>
                        <dd class="font-medium">{{ $jobWatch->remote_mode ?? 'Tous' }}</dd>
                    </div>
                </dl>
            </section>

            <aside class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Mots-clés</h2>

                <div class="mt-4">
                    <h3 class="text-sm font-medium text-green-700">À inclure</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse ($jobWatch->keywords->where('type', 'include') as $keyword)
                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-800">
                                {{ $keyword->keyword }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">Aucun</span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-5">
                    <h3 class="text-sm font-medium text-red-700">À exclure</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse ($jobWatch->keywords->where('type', 'exclude') as $keyword)
                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-800">
                                {{ $keyword->keyword }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">Aucun</span>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>

        <section class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Offres correspondantes</h2>

            <div class="mt-4">
                @forelse ($jobWatch->matches as $match)
                    <div class="border-b py-4 last:border-b-0">
                        <div class="flex justify-between gap-4">
                            <div>
                                <p class="font-medium">{{ $match->jobOffer->title }}</p>
                                <p class="text-sm text-gray-600">{{ $match->jobOffer->company }}</p>
                            </div>

                            <strong>{{ $match->score }} %</strong>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600">
                        Aucune offre correspondante pour le moment.
                    </p>
                @endforelse
            </div>
        </section>

        <div class="mt-8 border-t pt-6">
            <form
                method="POST"
                action="{{ route('student.job-watches.destroy', $jobWatch) }}"
                onsubmit="return confirm('Supprimer définitivement cette veille ?');"
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
