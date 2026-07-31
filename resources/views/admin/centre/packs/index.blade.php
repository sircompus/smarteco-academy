@extends('layouts.admin')

@section('title', 'Packs (semestres / modules)')
@section('page-title', 'Packs (semestres / modules)')

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
        <h2 class="text-lg font-bold">Génération automatique</h2>
        <p class="mt-1 text-sm text-gray-500">
            Crée en un clic un pack "semestre" pour chaque semestre existant,
            et un pack "module" pour chaque matière — sans dupliquer ceux déjà créés.
            Un prix est obligatoire (aucun pack gratuit).
        </p>

        <form method="POST" action="{{ route('admin.centre.packs.generate') }}" class="mt-4 flex flex-wrap items-end gap-4">
            @csrf

            <div>
                <label class="text-xs font-medium text-gray-500">Prix par défaut — pack semestre (DH)</label>
                <input type="number" step="0.01" min="0.01" name="default_semester_price" class="mt-1 block w-40 rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500">Prix par défaut — pack module (DH)</label>
                <input type="number" step="0.01" min="0.01" name="default_module_price" class="mt-1 block w-40 rounded-lg border-gray-300" required>
            </div>

            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
                Générer automatiquement les packs
            </button>
        </form>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Créer un pack manuellement</h2>

        <form
            method="POST"
            action="{{ route('admin.centre.packs.store') }}"
            x-data="{ type: 'semestre' }"
            class="mt-4 grid gap-4 md:grid-cols-2"
        >
            @csrf

            <div>
                <label class="text-sm font-medium">Type de pack</label>

                <select name="type" x-model="type" class="mt-1 block w-full rounded-lg border-gray-300" required>
                    <option value="semestre">Semestre complet</option>
                    <option value="module">Module unique</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Nom du pack</label>
                <input name="name" class="mt-1 block w-full rounded-lg border-gray-300" placeholder="Ex : Pack S1 — Tronc commun économie" required>
            </div>

            <div x-show="type === 'semestre'">
                <label class="text-sm font-medium">Semestre</label>

                <select name="semester_id" class="mt-1 block w-full rounded-lg border-gray-300">
                    <option value="">Choisir le semestre</option>

                    @foreach ($semesters as $semester)
                        <option value="{{ $semester->id }}">
                            {{ $semester->program?->level?->name }}
                            — {{ $semester->program?->name }}
                            — {{ $semester->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div x-show="type === 'module'" x-cloak>
                <label class="text-sm font-medium">Module (matière)</label>

                <select name="subject_id" class="mt-1 block w-full rounded-lg border-gray-300">
                    <option value="">Choisir le module</option>

                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">
                            {{ $subject->semester?->program?->level?->name }}
                            — {{ $subject->semester?->name }}
                            — {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Facturation</label>
                <select name="billing_type" class="mt-1 block w-full rounded-lg border-gray-300" required>
                    <option value="unique">Paiement unique (une fois)</option>
                    <option value="mensuel">Mensuel (récurrent, cumulé chaque mois depuis l'inscription)</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Prix (DH) — mensuel si facturation mensuelle</label>
                <input type="number" step="0.01" min="0.01" name="price" class="mt-1 block w-full rounded-lg border-gray-300" required>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium">Description (optionnel)</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300"></textarea>
            </div>

            <div class="md:col-span-2">
                <button class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white">
                    Créer le pack
                </button>
            </div>
        </form>
    </section>

    <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-6">
            <h2 class="text-lg font-bold">Packs existants ({{ $packs->count() }})</h2>

            <button
                type="submit"
                form="bulk-delete-packs-form"
                onclick="return confirm('Supprimer tous les packs cochés ? Cette action est irréversible.');"
                class="rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600"
            >
                Supprimer la sélection
            </button>
        </div>

        <form id="bulk-delete-packs-form" method="POST" action="{{ route('admin.centre.packs.destroy-bulk') }}">
            @csrf
            @method('DELETE')

            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-4">
                            <input type="checkbox" onclick="document.querySelectorAll('.pack-checkbox').forEach(c => c.checked = this.checked)">
                        </th>
                        <th class="px-6 py-4">Nom</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Contenu</th>
                        <th class="px-6 py-4">Prix</th>
                        <th class="px-6 py-4">Inscrits</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($packs as $pack)
                        <tr>
                            <td class="px-6 py-4">
                                <input type="checkbox" name="pack_ids[]" value="{{ $pack->id }}" class="pack-checkbox">
                            </td>

                            <td class="px-6 py-4 font-medium">{{ $pack->name }}</td>

                            <td class="px-6 py-4">
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    {{ $pack->type === 'semestre' ? 'Semestre' : 'Module' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                @if ($pack->isTypeSemestre())
                                    {{ $pack->semester?->program?->level?->name }}
                                    — {{ $pack->semester?->program?->name }}
                                    — {{ $pack->semester?->name }}
                                @else
                                    {{ $pack->subject?->semester?->program?->level?->name }}
                                    — {{ $pack->subject?->name }}
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                {{ $pack->price ? number_format($pack->price, 2).' DH' : '—' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $pack->enrollments()->where('status', 'active')->count() }}
                            </td>

                            <td class="px-6 py-4">
                                <a
                                    href="{{ route('admin.centre.packs.edit', $pack) }}"
                                    class="text-sm font-semibold text-indigo-600 hover:underline"
                                >
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                Aucun pack créé pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </section>

    <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="border-b border-gray-100 p-6">
            <h2 class="text-lg font-bold">Demandes d’inscription en attente</h2>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-4">Étudiant</th>
                    <th class="px-6 py-4">Pack</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($pendingEnrollments as $enrollment)
                    <tr>
                        <td class="px-6 py-4">
                            {{ $enrollment->user->name }}
                            <p class="text-xs text-gray-400">{{ $enrollment->user->email }}</p>
                        </td>

                        <td class="px-6 py-4">{{ $enrollment->pack->name }}</td>

                        <td class="px-6 py-4 text-gray-400">
                            {{ $enrollment->created_at->format('d/m/Y') }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.centre.pack-enrollments.status', $enrollment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="active">
                                    <button class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white">
                                        Valider
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.centre.pack-enrollments.status', $enrollment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="annulee">
                                    <button class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700">
                                        Refuser
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            Aucune demande en attente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
