@extends('layouts.admin')

@section('title', 'Générateur de cursus')
@section('page-title', 'Générateur de cursus (semestres + modules)')

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
        <h2 class="text-lg font-bold">Filières existantes</h2>

        <p class="mt-1 text-sm text-gray-500">
            Si une filière est en double (créée par erreur, doublon d'un ancien essai),
            supprime-la ici : ça retire aussi tous ses semestres, modules, cours et packs associés.
        </p>

        <div class="mt-4 divide-y divide-gray-100">
            @foreach ($programs as $program)
                <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div>
                        <p class="font-medium">
                            {{ $program->level?->name }} — {{ $program->name }}
                            <span class="text-xs font-normal text-gray-400">(id {{ $program->id }})</span>
                        </p>

                        <p class="text-xs text-gray-400">
                            {{ $program->semesters_count }} semestre(s)
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.centre.curriculum.programs.destroy', $program) }}"
                        onsubmit="return confirm('Supprimer définitivement « {{ $program->name }} » et TOUT son contenu (semestres, modules, cours, packs) ? Cette action est irréversible.');"
                    >
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">
                            Supprimer cette filière
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">1. Choisir la filière</h2>

        <form method="GET" action="{{ route('admin.centre.curriculum.index') }}" class="mt-4">
            <select
                name="program_id"
                onchange="this.form.submit()"
                class="block w-full max-w-lg rounded-lg border-gray-300"
            >
                <option value="">Choisir une filière</option>

                @foreach ($programs as $program)
                    <option
                        value="{{ $program->id }}"
                        @selected($selectedProgram && $selectedProgram->id === $program->id)
                    >
                        {{ $program->level?->name }} — {{ $program->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </section>

    @if ($selectedProgram)
        <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">
                2. Structure pour « {{ $selectedProgram->level?->name }} — {{ $selectedProgram->name }} »
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                Colle ou modifie la liste ci-dessous, puis clique sur « Synchroniser ».
                Format : une ligne <code class="rounded bg-gray-100 px-1">S1</code>, <code class="rounded bg-gray-100 px-1">S2</code>…
                suivie des noms de modules, un par ligne, une ligne vide entre chaque semestre.
                Tout module retiré de la liste sera supprimé de ce semestre ; les noms inchangés sont conservés tels quels.
            </p>

            <form method="POST" action="{{ route('admin.centre.curriculum.sync') }}" class="mt-4">
                @csrf
                <input type="hidden" name="academic_program_id" value="{{ $selectedProgram->id }}">

                <textarea
                    name="structure"
                    rows="18"
                    class="block w-full rounded-lg border-gray-300 font-mono text-sm"
                    placeholder="S1&#10;Microéconomie&#10;Comptabilité générale&#10;...&#10;&#10;S2&#10;..."
                >{{ old('structure', $textValue) }}</textarea>

                <button class="mt-4 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
                    Synchroniser cette filière
                </button>
            </form>
        </section>
    @else
        <section class="mt-8 rounded-2xl bg-white p-10 text-center shadow-sm">
            <p class="text-gray-500">
                Choisis une filière ci-dessus pour voir ou éditer sa structure.
            </p>
        </section>
    @endif

    <section class="mt-8 rounded-2xl bg-indigo-50 p-6">
        <h3 class="font-bold text-indigo-900">Exemple de format</h3>

        <pre class="mt-3 whitespace-pre-wrap rounded-lg bg-white p-4 text-xs text-gray-700">S1
Microéconomie
Comptabilité générale
Mathématiques appliquées
Introduction au droit
Anglais des affaires
Informatique de gestion
Communication

S2
Macroéconomie
Comptabilité analytique
Statistiques
Droit des sociétés
...</pre>
    </section>
@endsection
