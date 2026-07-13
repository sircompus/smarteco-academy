@extends('layouts.student')

@section('title', $training->title)
@section('page-title', $training->title)

@section('content')
    <article class="rounded-2xl bg-white p-6 shadow-sm">
        <h1 class="text-3xl font-bold">
            {{ $training->title }}
        </h1>

        <p class="mt-4 text-gray-600">
            {{ $training->description }}
        </p>

        @if ($enrollment)
            <div class="mt-6 rounded-xl bg-indigo-50 p-4">
                <p class="font-semibold text-indigo-700">
                    Vous êtes inscrit à cette formation.
                </p>

                <p class="mt-1 text-sm text-indigo-600">
                    Progression :
                    {{ $enrollment->progress_percentage }} %
                </p>
            </div>
        @else
            <section class="mt-8">
                <h2 class="text-lg font-bold">
                    Sessions disponibles
                </h2>

                <div class="mt-4 space-y-3">
                    @forelse ($training->sessions as $session)
                        <div class="rounded-xl border p-4">
                            <h3 class="font-semibold">
                                {{ $session->title }}
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Début :
                                {{ $session->starts_at->format('d/m/Y H:i') }}
                            </p>

                            <form
                                method="POST"
                                action="{{ route(
                                    'student.trainings.enroll',
                                    [$training, $session]
                                ) }}"
                                class="mt-3"
                            >
                                @csrf

                                <button
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                                >
                                    S’inscrire
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-gray-500">
                            Aucune session disponible.
                        </p>
                    @endforelse
                </div>
            </section>
        @endif
    </article>

    @if ($enrollment)
        <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold">
                Contenu de la formation
            </h2>

            <div class="mt-6 space-y-6">
                @foreach ($training->sections as $section)
                    <div>
                        <h3 class="font-semibold text-gray-900">
                            {{ $section->title }}
                        </h3>

                        <div class="mt-3 space-y-2">
                            @forelse ($section->lessons as $lesson)
                                <a
                                    href="{{ route(
                                        'student.trainings.lessons.show',
                                        [$training, $lesson]
                                    ) }}"
                                    class="flex items-center justify-between rounded-lg border p-4 hover:bg-gray-50"
                                >
                                    <span>{{ $lesson->title }}</span>

                                    <span class="text-sm text-gray-500">
                                        {{ $progressByLesson[$lesson->id]
                                            ?? 'not_started' }}
                                    </span>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500">
                                    Aucune leçon.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection