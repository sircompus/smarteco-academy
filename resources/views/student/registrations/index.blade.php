@extends('layouts.student')

@section('title', 'Mes inscriptions')
@section('page-title', 'Mes inscriptions')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-xl font-bold">
            Mes demandes d’inscription
        </h2>

        <a
            href="{{ route('student.registrations.create') }}"
            class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white"
        >
            Nouvelle inscription
        </a>
    </div>

    <div class="mt-8 space-y-4">
        @forelse ($registrations as $registration)
            <a
                href="{{ route('student.registrations.show', $registration) }}"
                class="block rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            {{ $registration->reference }}
                        </p>

                        <h3 class="mt-1 text-lg font-bold">
                            {{ $registration->program?->name ?? 'Filière non définie' }}
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $registration->level?->name }}
                            — Année {{ $registration->academic_year }}
                        </p>
                    </div>

                    <span class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">
                        {{ $registration->status_label }}
                    </span>
                </div>

                <p class="mt-4 text-xs text-gray-400">
                    Créée le {{ $registration->created_at->format('d/m/Y') }}
                </p>
            </a>
        @empty
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
                <p class="text-gray-500">
                    Vous n’avez encore aucune demande d’inscription.
                </p>

                <a
                    href="{{ route('student.registrations.create') }}"
                    class="mt-4 inline-block rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white"
                >
                    Créer ma première demande
                </a>
            </div>
        @endforelse
    </div>
@endsection
