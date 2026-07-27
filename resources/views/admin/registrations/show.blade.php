@extends('layouts.admin')

@section('title', 'Demande d’inscription')
@section('page-title', 'Demande d’inscription')

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
                    {{ $registration->first_name }} {{ $registration->last_name }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $registration->program?->name ?? '—' }}
                    — {{ $registration->level?->name }}
                    ({{ $registration->academic_year }})
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    {{ $registration->user?->email }}
                </p>
            </div>

            <span class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">
                {{ $registration->status_label }}
            </span>
        </div>

        <div class="mt-6 grid gap-4 text-sm md:grid-cols-2">
            <div>
                <p class="text-gray-400">Téléphone</p>
                <p class="font-medium">{{ $registration->phone ?? '—' }}</p>
            </div>

            <div>
                <p class="text-gray-400">Date de naissance</p>
                <p class="font-medium">{{ $registration->birth_date?->format('d/m/Y') ?? '—' }}</p>
            </div>

            <div>
                <p class="text-gray-400">Adresse</p>
                <p class="font-medium">{{ $registration->address ?? '—' }}</p>
            </div>

            <div>
                <p class="text-gray-400">Ville / Pays</p>
                <p class="font-medium">{{ $registration->city ?? '—' }}, {{ $registration->country ?? '—' }}</p>
            </div>

            @if ($registration->student_note)
                <div class="md:col-span-2">
                    <p class="text-gray-400">Note de l’étudiant</p>
                    <p class="font-medium">{{ $registration->student_note }}</p>
                </div>
            @endif
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold">Documents</h3>

        <div class="mt-4 space-y-3">
            @forelse ($registration->documents as $document)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-4">
                    <div>
                        <p class="font-medium">{{ $document->type_label }}</p>
                        <p class="text-xs text-gray-400">{{ $document->original_name }}</p>
                    </div>

                    <form method="POST" action="{{ route('admin.registrations.documents.verify', $document) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')

                        <select name="is_verified" class="rounded-lg border-gray-300 text-sm">
                            <option value="1" @selected($document->is_verified)>Vérifié</option>
                            <option value="0" @selected(! $document->is_verified)>Non vérifié</option>
                        </select>

                        <button class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700">
                            Mettre à jour
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500">Aucun document déposé.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold">Changer le statut</h3>

        <form
            method="POST"
            action="{{ route('admin.registrations.status.update', $registration) }}"
            class="mt-4 grid gap-3 md:grid-cols-3"
        >
            @csrf
            @method('PATCH')

            <select name="status" class="rounded-lg border-gray-300" required>
                <option value="">Choisir un statut</option>
                <option value="under_review">En cours d’examen</option>
                <option value="incomplete">Dossier incomplet</option>
                <option value="accepted">Acceptée</option>
                <option value="rejected">Refusée</option>
                <option value="suspended">Suspendue</option>
            </select>

            <input
                type="text"
                name="comment"
                placeholder="Motif / commentaire"
                class="rounded-lg border-gray-300 md:col-span-2"
            >

            <button class="w-fit rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white md:col-span-3">
                Appliquer le changement de statut
            </button>
        </form>
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
                        {{ $history->changedBy?->name }}
                        — {{ $history->created_at->format('d/m/Y H:i') }}
                        @if ($history->comment)
                            — {{ $history->comment }}
                        @endif
                    </p>
                </li>
            @endforeach
        </ol>
    </section>
@endsection
