@extends('layouts.student')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">
                    Mes veilles d’emploi
                </h1>

                <p class="mt-1 text-gray-600">
                    Gérez vos critères de recherche d’emploi.
                </p>
            </div>

            <a
                href="{{ route('student.job-watches.create') }}"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white"
            >
                Nouvelle veille
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @forelse ($jobWatches as $jobWatch)
            <article class="mb-4 rounded-xl bg-white p-6 shadow">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">
                            {{ $jobWatch->name }}
                        </h2>

                        <p class="mt-2 text-gray-600">
                            {{ implode(', ', $jobWatch->target_titles ?? []) }}
                        </p>

                        <p class="mt-2 text-sm text-gray-500">
                            Statut : {{ $jobWatch->status }}
                        </p>

                        <p class="text-sm text-gray-500">
                            Mots-clés : {{ $jobWatch->keywords_count }}
                        </p>

                        <p class="text-sm text-gray-500">
                            Correspondances : {{ $jobWatch->matches_count }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <a
                            href="{{ route(
                                'student.job-watches.show',
                                $jobWatch
                            ) }}"
                            class="rounded border px-3 py-2"
                        >
                            Consulter
                        </a>

                        <a
                            href="{{ route(
                                'student.job-watches.edit',
                                $jobWatch
                            ) }}"
                            class="rounded border px-3 py-2"
                        >
                            Modifier
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl bg-white p-8 text-center shadow">
                <p>Aucune veille enregistrée.</p>

                <a
                    href="{{ route('student.job-watches.create') }}"
                    class="mt-4 inline-block rounded bg-blue-600 px-4 py-2 text-white"
                >
                    Créer une veille
                </a>
            </div>
        @endforelse

        <div class="mt-6">
            {{ $jobWatches->links() }}
        </div>
    </div>
@endsection