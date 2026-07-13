@extends('layouts.admin')

@section('title', 'Gestion des formations')
@section('page-title', 'Gestion des formations')

@section('content')
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
        <h2 class="text-xl font-bold">
            Créer une formation
        </h2>

        <form
            method="POST"
            action="{{ route('admin.trainings.store') }}"
            class="mt-6 grid gap-4 lg:grid-cols-2"
        >
            @csrf

            <input
                name="title"
                class="rounded-lg border-gray-300 lg:col-span-2"
                placeholder="Titre de la formation"
                required
            >

            <input
                name="duration_minutes"
                type="number"
                min="0"
                class="rounded-lg border-gray-300"
                placeholder="Durée totale en minutes"
            >

            <textarea
                name="short_description"
                class="rounded-lg border-gray-300 lg:col-span-2"
                placeholder="Résumé"
            ></textarea>

            <textarea
                name="description"
                rows="5"
                class="rounded-lg border-gray-300 lg:col-span-2"
                placeholder="Description détaillée"
            ></textarea>

            <button
                class="w-fit rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white"
            >
                Créer la formation
            </button>
        </form>
    </section>

    <div class="mt-8 space-y-8">
        @forelse ($trainings as $training)
            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold">
                            {{ $training->title }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Statut :
                            {{ $training->status === 'published'
                                ? 'Publiée'
                                : 'Brouillon' }}
                        </p>
                    </div>

                    @if ($training->status !== 'published')
                        <form
                            method="POST"
                            action="{{ route(
                                'admin.trainings.publish',
                                $training
                            ) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white"
                            >
                                Publier
                            </button>
                        </form>
                    @endif
                </div>

                <div class="mt-6 grid gap-6 xl:grid-cols-3">
                    <section class="rounded-xl border p-4">
                        <h3 class="font-semibold">
                            Ajouter une session
                        </h3>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.trainings.sessions.store',
                                $training
                            ) }}"
                            class="mt-4 space-y-3"
                        >
                            @csrf

                            <input
                                name="title"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Session juillet 2026"
                                required
                            >

                            <input
                                name="code"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Code"
                            >

                            <input
                                name="starts_at"
                                type="datetime-local"
                                class="block w-full rounded-lg border-gray-300"
                                required
                            >

                            <input
                                name="ends_at"
                                type="datetime-local"
                                class="block w-full rounded-lg border-gray-300"
                            >

                            <input
                                name="capacity"
                                type="number"
                                min="1"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Capacité"
                            >

                            <input
                                name="location"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Lieu ou salle"
                            >

                            <button
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                            >
                                Ajouter
                            </button>
                        </form>
                    </section>

                    <section class="rounded-xl border p-4">
                        <h3 class="font-semibold">
                            Ajouter une section
                        </h3>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.trainings.sections.store',
                                $training
                            ) }}"
                            class="mt-4 space-y-3"
                        >
                            @csrf

                            <input
                                name="title"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Module 1"
                                required
                            >

                            <textarea
                                name="description"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Description"
                            ></textarea>

                            <button
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                            >
                                Ajouter
                            </button>
                        </form>
                    </section>

                    <section class="rounded-xl border p-4">
                        <h3 class="font-semibold">
                            Ajouter une leçon
                        </h3>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.trainings.lessons.store',
                                $training
                            ) }}"
                            class="mt-4 space-y-3"
                        >
                            @csrf

                            <select
                                name="training_section_id"
                                class="block w-full rounded-lg border-gray-300"
                            >
                                <option value="">
                                    Sans section
                                </option>

                                @foreach ($training->sections as $section)
                                    <option value="{{ $section->id }}">
                                        {{ $section->title }}
                                    </option>
                                @endforeach
                            </select>

                            <input
                                name="title"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Titre de la leçon"
                                required
                            >

                            <input
                                name="duration_minutes"
                                type="number"
                                min="0"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Durée"
                            >

                            <input
                                name="video_url"
                                type="url"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Lien vidéo"
                            >

                            <textarea
                                name="content"
                                rows="4"
                                class="block w-full rounded-lg border-gray-300"
                                placeholder="Contenu"
                            ></textarea>

                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="is_preview"
                                    value="1"
                                >
                                Leçon en aperçu
                            </label>

                            <button
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                            >
                                Ajouter
                            </button>
                        </form>
                    </section>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <h3 class="font-semibold">
                            Sessions
                        </h3>

                        <ul class="mt-3 space-y-2 text-sm">
                            @forelse ($training->sessions as $session)
                                <li class="rounded-lg bg-gray-50 p-3">
                                    {{ $session->title }}
                                    — {{ $session->starts_at->format('d/m/Y H:i') }}
                                </li>
                            @empty
                                <li class="text-gray-500">
                                    Aucune session.
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-semibold">
                            Contenu
                        </h3>

                        <ul class="mt-3 space-y-2 text-sm">
                            @forelse ($training->lessons as $lesson)
                                <li class="rounded-lg bg-gray-50 p-3">
                                    {{ $lesson->title }}
                                </li>
                            @empty
                                <li class="text-gray-500">
                                    Aucune leçon.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </article>
        @empty
            <p class="text-gray-500">
                Aucune formation créée.
            </p>
        @endforelse
    </div>
@endsection