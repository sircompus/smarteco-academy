@extends('layouts.admin')

@section('title', 'Contenu du cours')
@section('page-title', 'Contenu du cours')

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
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
            {{ $course->subject?->semester?->program?->level?->name }}
            — {{ $course->subject?->name }}
        </p>

        <h2 class="mt-1 text-xl font-bold">
            {{ $course->title }}
        </h2>

        <span class="mt-2 inline-block rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
            {{ $course->status === 'published' ? 'Publié' : 'Brouillon' }}
        </span>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">Ajouter une section (optionnel)</h3>

        <p class="mt-1 text-sm text-gray-500">
            Les sections permettent de regrouper des leçons (ex : "Chapitre 1"). Une leçon peut aussi rester hors section.
        </p>

        <form method="POST" action="{{ route('admin.centre.courses.sections.store', $course) }}" class="mt-4 flex flex-wrap gap-3">
            @csrf
            <input name="title" placeholder="Titre de la section" class="flex-1 min-w-[200px] rounded-lg border-gray-300" required>
            <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                Ajouter la section
            </button>
        </form>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">Ajouter une leçon</h3>

        <form method="POST" action="{{ route('admin.centre.courses.lessons.store', $course) }}" class="mt-4 space-y-4">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium">Titre de la leçon</label>
                    <input name="title" class="mt-1 block w-full rounded-lg border-gray-300" required>
                </div>

                <div>
                    <label class="text-sm font-medium">Section (optionnel)</label>
                    <select name="course_section_id" class="mt-1 block w-full rounded-lg border-gray-300">
                        <option value="">Aucune (leçon libre)</option>
                        @foreach ($course->sections as $section)
                            <option value="{{ $section->id }}">{{ $section->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium">Contenu texte (optionnel)</label>
                <textarea name="content" rows="4" class="mt-1 block w-full rounded-lg border-gray-300"></textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm font-medium">Lien vidéo (optionnel)</label>
                    <input name="video_url" type="url" placeholder="https://..." class="mt-1 block w-full rounded-lg border-gray-300">
                </div>

                <div>
                    <label class="text-sm font-medium">Durée (minutes)</label>
                    <input name="duration_minutes" type="number" min="0" class="mt-1 block w-full rounded-lg border-gray-300">
                </div>

                <div class="flex items-end gap-4 pb-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_preview" value="1" class="rounded border-gray-300">
                        Aperçu gratuit
                    </label>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300" checked>
                        Publiée
                    </label>
                </div>
            </div>

            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Ajouter la leçon
            </button>
        </form>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">Leçons existantes</h3>

        @foreach ($course->sections as $section)
            <div class="mt-5">
                <h4 class="text-sm font-bold text-gray-700">{{ $section->title }}</h4>

                <div class="mt-2 space-y-2">
                    @forelse ($section->lessons as $lesson)
                        @include('admin.centre.courses._lesson-row', ['lesson' => $lesson])
                    @empty
                        <p class="text-sm text-gray-400">Aucune leçon dans cette section.</p>
                    @endforelse
                </div>
            </div>
        @endforeach

        <div class="mt-5">
            @if ($course->sections->isNotEmpty())
                <h4 class="text-sm font-bold text-gray-700">Sans section</h4>
            @endif

            <div class="mt-2 space-y-2">
                @forelse ($course->lessons as $lesson)
                    @include('admin.centre.courses._lesson-row', ['lesson' => $lesson])
                @empty
                    @if ($course->sections->isEmpty())
                        <p class="text-sm text-gray-400">Aucune leçon pour ce cours.</p>
                    @endif
                @endforelse
            </div>
        </div>
    </section>
@endsection
