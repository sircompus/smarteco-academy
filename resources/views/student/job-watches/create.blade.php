@extends('layouts.student')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Créer une veille d’emploi
            </h1>
            <p class="mt-1 text-gray-600">
                Définissez les critères utilisés pour rechercher les offres.
            </p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <form
                method="POST"
                action="{{ route('student.job-watches.store') }}"
            >
                @csrf

                @include('student.job-watches._form', [
                    'submitLabel' => 'Créer la veille',
                ])
            </form>
        </div>
    </div>
@endsection
