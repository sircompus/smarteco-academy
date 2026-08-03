@extends('layouts.student')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Modifier la veille</h1>
            <p class="mt-1 text-gray-600">{{ $jobWatch->name }}</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <form
                method="POST"
                action="{{ route('student.job-watches.update', $jobWatch) }}"
            >
                @csrf
                @method('PUT')

                @include('student.job-watches._form', [
                    'submitLabel' => 'Enregistrer les modifications',
                ])
            </form>
        </div>
    </div>
@endsection
