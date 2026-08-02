@extends('layouts.student')

@section('title', 'Formations')
@section('page-title', 'Formations disponibles')

@section('content')
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($trainings as $training)
            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold">
                    {{ $training->title }}
                </h2>

                <p class="mt-3 text-sm leading-6 text-gray-600">
                    {{ $training->short_description }}
                </p>

                <div class="mt-4 flex gap-4 text-xs text-gray-500">
                    <span>
                        {{ $training->sessions_count }} session(s)
                    </span>

                    <span>
                        {{ $training->lessons_count }} leçon(s)
                    </span>
                </div>

                <a
                    href="{{ route(
                        'student.trainings.show',
                        $training
                    ) }}"
                    class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    Consulter
                </a>
            </article>
        @empty
            <p class="text-gray-500">
                Aucune formation publiée actuellement.
            </p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $trainings->links() }}
    </div>
@endsection