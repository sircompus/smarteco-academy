@extends('layouts.admin')

@section('title', 'Gestion du centre')
@section('page-title', 'Module Centre')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">1. Créer un niveau</h2>

            <form method="POST"
                  action="{{ route('admin.centre.levels.store') }}"
                  class="mt-5 space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-medium">Nom</label>
                    <input
                        name="name"
                        value="{{ old('name') }}"
                        class="mt-1 block w-full rounded-lg border-gray-300"
                        placeholder="Licence"
                        required
                    >
                </div>

                <div>
                    <label class="text-sm font-medium">Description</label>
                    <textarea
                        name="description"
                        class="mt-1 block w-full rounded-lg border-gray-300"
                    >{{ old('description') }}</textarea>
                </div>

                <button class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white">
                    Créer le niveau
                </button>
            </form>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">2. Créer une filière</h2>

            <form method="POST"
                  action="{{ route('admin.centre.programs.store') }}"
                  class="mt-5 space-y-4">
                @csrf

                <select
                    name="academic_level_id"
                    class="block w-full rounded-lg border-gray-300"
                    required
                >
                    <option value="">Choisir le niveau</option>

                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}">
                            {{ $level->name }}
                        </option>
                    @endforeach
                </select>

                <input
                    name="name"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="Économie"
                    required
                >

                <input
                    name="duration_semesters"
                    type="number"
                    min="1"
                    max="20"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="Nombre de semestres"
                >

                <textarea
                    name="description"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="Description"
                ></textarea>

                <button class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white">
                    Créer la filière
                </button>
            </form>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">3. Créer un semestre</h2>

            <form method="POST"
                  action="{{ route('admin.centre.semesters.store') }}"
                  class="mt-5 space-y-4">
                @csrf

                <select
                    name="academic_program_id"
                    class="block w-full rounded-lg border-gray-300"
                    required
                >
                    <option value="">Choisir la filière</option>

                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}">
                            {{ $program->level->name }}
                            — {{ $program->name }}
                        </option>
                    @endforeach
                </select>

                <input
                    name="name"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="Semestre 1"
                    required
                >

                <input
                    name="code"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="S1"
                >

                <input
                    name="number"
                    type="number"
                    min="1"
                    max="20"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="Numéro du semestre"
                    required
                >

                <button class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white">
                    Créer le semestre
                </button>
            </form>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">4. Créer une matière</h2>

            <form method="POST"
                  action="{{ route('admin.centre.subjects.store') }}"
                  class="mt-5 space-y-4">
                @csrf

                <select
                    name="semester_id"
                    class="block w-full rounded-lg border-gray-300"
                    required
                >
                    <option value="">Choisir le semestre</option>

                    @foreach ($semesters as $semester)
                        <option value="{{ $semester->id }}">
                            {{ $semester->program->name }}
                            — {{ $semester->name }}
                        </option>
                    @endforeach
                </select>

                <input
                    name="name"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="Microéconomie"
                    required
                >

                <input
                    name="code"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="ECO101"
                >

                <div class="grid grid-cols-2 gap-3">
                    <input
                        name="credits"
                        type="number"
                        step="0.01"
                        min="0"
                        class="rounded-lg border-gray-300"
                        placeholder="Crédits"
                    >

                    <input
                        name="coefficient"
                        type="number"
                        step="0.01"
                        min="0"
                        class="rounded-lg border-gray-300"
                        placeholder="Coefficient"
                    >
                </div>

                <textarea
                    name="description"
                    class="block w-full rounded-lg border-gray-300"
                    placeholder="Description"
                ></textarea>

                <button class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white">
                    Créer la matière
                </button>
            </form>
        </section>
    </div>

    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">5. Créer un cours</h2>

        <form method="POST"
              action="{{ route('admin.centre.courses.store') }}"
              class="mt-5 grid gap-4 lg:grid-cols-2">
            @csrf

            <select
                name="subject_id"
                class="rounded-lg border-gray-300"
                required
            >
                <option value="">Choisir la matière</option>

                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}">
                        {{ $subject->semester->program->name }}
                        — {{ $subject->name }}
                    </option>
                @endforeach
            </select>

            <select
                name="teacher_id"
                class="rounded-lg border-gray-300"
            >
                <option value="">Aucun professeur attribué</option>

                @foreach ($professors as $professor)
                    <option value="{{ $professor->id }}">
                        {{ $professor->name }}
                    </option>
                @endforeach
            </select>

            <input
                name="title"
                class="rounded-lg border-gray-300 lg:col-span-2"
                placeholder="Introduction à la microéconomie"
                required
            >

            <textarea
                name="summary"
                class="rounded-lg border-gray-300 lg:col-span-2"
                placeholder="Résumé du cours"
            ></textarea>

            <textarea
                name="description"
                rows="6"
                class="rounded-lg border-gray-300 lg:col-span-2"
                placeholder="Description détaillée"
            ></textarea>

            <button class="w-fit rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white">
                Créer le cours
            </button>
        </form>
    </section>

    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Cours créés</h2>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-sm text-gray-500">
                        <th class="px-3 py-3">Cours</th>
                        <th class="px-3 py-3">Matière</th>
                        <th class="px-3 py-3">Statut</th>
                        <th class="px-3 py-3">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($courses as $course)
                        <tr>
                            <td class="px-3 py-4 font-medium">
                                {{ $course->title }}
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-600">
                                {{ $course->subject->name }}
                            </td>

                            <td class="px-3 py-4">
                                {{ $course->status === 'published'
                                    ? 'Publié'
                                    : 'Brouillon' }}
                            </td>

                            <td class="px-3 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a
                                        href="{{ route('admin.centre.courses.content', $course) }}"
                                        class="rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700"
                                    >
                                        Gérer le contenu
                                    </a>

                                    @if ($course->status !== 'published')
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.centre.courses.publish',
                                                $course
                                            ) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button class="rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white">
                                                Publier
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-sm text-green-700">
                                            Déjà publié
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-gray-500">
                                Aucun cours créé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection