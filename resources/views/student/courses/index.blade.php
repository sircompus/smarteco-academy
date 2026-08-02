@extends('layouts.student')

@section('title', 'Cours disponibles')
@section('page-title', 'Cours disponibles')

@section('content')
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($courses as $course)
            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-indigo-600">
                    {{ $course->subject->name }}
                </p>

                <h2 class="mt-2 text-xl font-bold text-gray-900">
                    {{ $course->title }}
                </h2>

                <p class="mt-3 text-sm leading-6 text-gray-600">
                    {{ $course->summary }}
                </p>

                <p class="mt-4 text-xs text-gray-500">
                    {{ $course->subject->semester->program->level->name }}
                    /
                    {{ $course->subject->semester->program->name }}
                    /
                    {{ $course->subject->semester->name }}
                </p>

                <a
                    href="{{ route('student.courses.show', $course) }}"
                    class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    Consulter le cours
                </a>
            </article>
        @empty
            <p class="text-gray-500">
                Aucun cours publié actuellement.
            </p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $courses->links() }}
    </div>
@endsection