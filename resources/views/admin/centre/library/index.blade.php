@extends('layouts.admin')

@section('title', 'Bibliothèque de ressources')
@section('page-title', 'Bibliothèque de ressources')

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
        <h2 class="text-lg font-bold">1. Choisir le module</h2>

        <p class="mt-1 text-sm text-gray-500">
            Ce réservoir est indépendant du système "Cours" — il classe les documents
            directement par niveau, filière, semestre et module.
        </p>

        <form method="GET" action="{{ route('admin.centre.library.index') }}" class="mt-4">
            <select
                name="subject_id"
                onchange="this.form.submit()"
                class="block w-full max-w-xl rounded-lg border-gray-300"
            >
                <option value="">Choisir un module</option>

                @foreach ($subjects as $subject)
                    <option
                        value="{{ $subject->id }}"
                        @selected($selectedSubject && $selectedSubject->id === $subject->id)
                    >
                        {{ $subject->compact_label }} — {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </section>

    @if ($selectedSubject)
        <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">
                2. Ajouter un document — {{ $selectedSubject->name }}
            </h2>

            <form
                method="POST"
                action="{{ route('admin.centre.library.store') }}"
                enctype="multipart/form-data"
                class="mt-4 grid gap-3 md:grid-cols-2"
            >
                @csrf
                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">

                <select name="type" class="rounded-lg border-gray-300" required>
                    <option value="">Type de document</option>
                    <option value="cours">Cours</option>
                    <option value="td">TD</option>
                    <option value="examen">Examen</option>
                    <option value="resume">Résumé</option>
                </select>

                <input name="professor_name" placeholder="Nom du professeur (optionnel)" class="rounded-lg border-gray-300">

                <input
                    name="title"
                    placeholder="Titre (utilisé seulement si un seul fichier — sinon le nom du fichier sert de titre)"
                    class="rounded-lg border-gray-300 md:col-span-2"
                >

                <div class="md:col-span-2">
                    <input type="file" name="files[]" multiple class="block w-full rounded-lg border-gray-300" required>
                    <p class="mt-1 text-xs text-gray-400">
                        Tu peux sélectionner plusieurs fichiers à la fois (Ctrl+clic ou Cmd+clic).
                    </p>
                </div>

                <input name="description" placeholder="Description (optionnel, appliquée à tous les fichiers)" class="rounded-lg border-gray-300 md:col-span-2">

                <button class="w-fit rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white md:col-span-2">
                    Mettre en ligne
                </button>
            </form>
        </section>

        <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">Documents existants</h2>

            <div class="mt-4 space-y-8">
                @forelse ($resourcesByProfessor as $professorName => $byType)
                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="bg-indigo-600 px-4 py-3 text-center">
                            <p class="text-sm font-bold text-white">
                                {{ $professorName }}
                            </p>
                        </div>

                        <div class="space-y-5 p-4">
                            @foreach (\App\Models\AcademicResource::TYPES as $typeKey => $typeLabel)
                                <div>
                                    <h4 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h4>

                                    <div class="mt-2 space-y-2">
                                        @forelse ($byType->get($typeKey, collect()) as $resource)
                                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-3">
                                                <div>
                                                    <a href="{{ $resource->download_url }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:underline">
                                                        {{ $resource->title }}
                                                    </a>
                                                    <p class="text-xs text-gray-400">
                                                        {{ $resource->original_name }} · {{ $resource->size_for_humans }}
                                                    </p>
                                                    @if ($resource->description)
                                                        <p class="mt-1 text-xs text-gray-500">{{ $resource->description }}</p>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <a
                                                        href="{{ route('admin.centre.library.edit', $resource) }}"
                                                        class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700"
                                                    >
                                                        Modifier
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin.centre.library.destroy', $resource) }}"
                                                        onsubmit="return confirm('Supprimer ce document ?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-400">Aucun document.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucun document pour ce module pour le moment.</p>
                @endforelse
            </div>
        </section>
    @else
        <section class="mt-8 rounded-2xl bg-white p-10 text-center shadow-sm">
            <p class="text-gray-500">Choisis un module ci-dessus pour voir ou ajouter des documents.</p>
        </section>
    @endif
@endsection
