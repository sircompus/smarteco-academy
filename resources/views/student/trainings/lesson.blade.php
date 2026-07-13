@extends('layouts.student')

@section('title', $lesson->title)
@section('page-title', $lesson->title)

@section('content')
    <article class="rounded-2xl bg-white p-6 shadow-sm">
        <a
            href="{{ route(
                'student.trainings.show',
                $training
            ) }}"
            class="text-sm font-semibold text-indigo-600"
        >
            ← Retour à la formation
        </a>

        <h1 class="mt-4 text-3xl font-bold">
            {{ $lesson->title }}
        </h1>

        @if ($lesson->video_url)
            <a
                href="{{ $lesson->video_url }}"
                target="_blank"
                class="mt-5 inline-flex text-indigo-600 underline"
            >
                Ouvrir la vidéo
            </a>
        @endif

        <div class="mt-8 whitespace-pre-line leading-8 text-gray-700">
            {{ $lesson->content }}
        </div>

        @if ($progress->status !== 'completed')
            <form
                method="POST"
                action="{{ route(
                    'student.trainings.lessons.complete',
                    [$training, $lesson]
                ) }}"
                class="mt-8"
            >
                @csrf
                @method('PATCH')

                <button
                    class="rounded-lg bg-green-600 px-5 py-3 font-semibold text-white"
                >
                    Marquer comme terminée
                </button>
            </form>
        @else
            <p class="mt-8 font-semibold text-green-700">
                Leçon terminée.
            </p>
        @endif
    </article>
@endsection