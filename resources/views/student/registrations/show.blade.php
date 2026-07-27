@extends('layouts.student')

@section('title', 'Ma demande d’inscription')
@section('page-title', 'Ma demande d’inscription')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                    {{ $registration->reference }}
                </p>

                <h2 class="mt-1 text-xl font-bold">
                    {{ $registration->program?->name ?? 'Filière non définie' }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $registration->level?->name }}
                    — Année {{ $registration->academic_year }}
                </p>
            </div>

            <span class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">
                {{ $registration->status_label }}
            </span>
        </div>

        @if ($registration->decision_reason)
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <strong>Motif :</strong> {{ $registration->decision_reason }}
            </div>
        @endif

        <div class="mt-6 grid gap-4 text-sm md:grid-cols-2">
            <div>
                <p class="text-gray-400">Nom complet</p>
                <p class="font-medium">{{ $registration->first_name }} {{ $registration->last_name }}</p>
            </div>

            <div>
                <p class="text-gray-400">Téléphone</p>
                <p class="font-medium">{{ $registration->phone ?? '—' }}</p>
            </div>

            <div>
                <p class="text-gray-400">Date de naissance</p>
                <p class="font-medium">
                    {{ $registration->birth_date?->format('d/m/Y') ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-400">Ville / Pays</p>
                <p class="font-medium">
                    {{ $registration->city ?? '—' }}, {{ $registration->country ?? '—' }}
                </p>
            </div>
        </div>

        @if ($registration->canBeEdited())
            <div class="mt-6 flex flex-wrap gap-3">
                <a
                    href="{{ route('student.registrations.edit', $registration) }}"
                    class="rounded-lg border border-gray-300 px-5 py-3 font-semibold text-gray-700"
                >
                    Modifier
                </a>

                <form
                    method="POST"
                    action="{{ route('student.registrations.submit', $registration) }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white"
                    >
                        Soumettre le dossier
                    </button>
                </form>
            </div>
        @endif
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold">Documents</h3>

        <div class="mt-4 space-y-3">
            @forelse ($registration->documents as $document)
                <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                    <div>
                        <p class="font-medium">{{ $document->type_label }}</p>
                        <p class="text-xs text-gray-400">{{ $document->original_name }}</p>
                    </div>

                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $document->is_verified ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $document->is_verified ? 'Vérifié' : 'En attente' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Aucun document déposé.</p>
            @endforelse
        </div>

        @if ($registration->canBeEdited())
            <form
                method="POST"
                action="{{ route('student.registrations.documents.store', $registration) }}"
                enctype="multipart/form-data"
                class="mt-6 grid gap-3 md:grid-cols-4"
            >
                @csrf

                <select name="type" class="rounded-lg border-gray-300" required>
                    <option value="">Type de document</option>
                    <option value="identity">Pièce d’identité</option>
                    <option value="diploma">Diplôme</option>
                    <option value="transcript">Relevé de notes</option>
                    <option value="photo">Photo</option>
                    <option value="other">Autre document</option>
                </select>

                <input type="file" name="file" class="rounded-lg border-gray-300 md:col-span-2" required>

                <button class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white">
                    Déposer
                </button>
            </form>
        @endif
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold">Historique</h3>

        <ol class="mt-4 space-y-3 border-l border-gray-200 pl-4">
            @foreach ($registration->histories as $history)
                <li>
                    <p class="text-sm font-medium">
                        {{ $history->from_status ? \App\Models\Registration::STATUSES[$history->from_status] ?? $history->from_status : 'Création' }}
                        →
                        {{ \App\Models\Registration::STATUSES[$history->to_status] ?? $history->to_status }}
                    </p>

                    <p class="text-xs text-gray-400">
                        {{ $history->created_at->format('d/m/Y H:i') }}
                        @if ($history->comment)
                            — {{ $history->comment }}
                        @endif
                    </p>
                </li>
            @endforeach
        </ol>
    </section>
@endsection
