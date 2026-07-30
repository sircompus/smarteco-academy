@extends('layouts.student')

@section('title', $course->title)
@section('page-title', $course->title)

@section('content')
    <article class="rounded-2xl bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-indigo-600">
            {{ $course->subject->name }}
        </p>

        <h1 class="mt-3 text-3xl font-bold text-gray-900">
            {{ $course->title }}
        </h1>

        <p class="mt-4 text-gray-600">
            {{ $course->summary }}
        </p>

        <div class="mt-8 border-t pt-6 leading-8 text-gray-700">
            {!! nl2br(e($course->description)) !!}
        </div>

        @if ($course->teacher)
            <p class="mt-8 text-sm text-gray-500">
                Professeur : {{ $course->teacher->name }}
            </p>
        @endif

        @unless ($hasAccess)
            <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Tu n'as pas encore accès à ce module. Les leçons marquées « Aperçu gratuit »
                restent consultables ; pour le reste, inscris-toi au pack correspondant
                depuis <a href="{{ route('student.packs.index') }}" class="font-semibold underline">Packs (semestres / modules)</a>.
            </div>
        @endunless
    </article>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Contenu du cours</h2>

        @php
            $hasAnyLesson = $course->sections->sum(fn ($section) => $section->lessons->count())
                + $course->lessons->count();
        @endphp

        @if ($hasAnyLesson === 0)
            <p class="mt-4 text-sm text-gray-500">
                Aucune leçon n'a encore été publiée pour ce cours.
            </p>
        @else
            <div class="mt-5 space-y-6">
                @foreach ($course->sections as $section)
                    @if ($section->lessons->isNotEmpty())
                        <div>
                            <h3 class="text-sm font-bold text-gray-700">
                                {{ $section->title }}
                            </h3>

                            <div class="mt-2 space-y-2">
                                @foreach ($section->lessons as $lesson)
                                    @include('student.courses._lesson-row', ['lesson' => $lesson, 'hasAccess' => $hasAccess])
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @if ($course->lessons->isNotEmpty())
                    <div>
                        @if ($course->sections->isNotEmpty())
                            <h3 class="text-sm font-bold text-gray-700">Autres leçons</h3>
                        @endif

                        <div class="mt-2 space-y-2">
                            @foreach ($course->lessons as $lesson)
                                @include('student.courses._lesson-row', ['lesson' => $lesson, 'hasAccess' => $hasAccess])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>
@endsection
