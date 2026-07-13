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
    </article>
@endsection