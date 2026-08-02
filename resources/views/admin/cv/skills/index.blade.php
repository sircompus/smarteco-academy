@extends('layouts.admin')

@section('title', 'Catalogue de compétences')
@section('page-title', 'Catalogue de compétences (CV)')

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
        <h2 class="text-lg font-bold">Ajouter une compétence au catalogue</h2>
        <p class="mt-1 text-sm text-gray-500">
            Ces compétences apparaissent en cases à cocher dans le CV builder des étudiants.
        </p>

        <form method="POST" action="{{ route('admin.cv.skills.store') }}" class="mt-4 flex flex-wrap gap-3">
            @csrf
            <input name="name" placeholder="Nom de la compétence" class="rounded-lg border-gray-300" required>
            <input name="category" placeholder="Catégorie (ex: Gestion, IT...)" class="rounded-lg border-gray-300">
            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Ajouter
            </button>
        </form>
    </section>

    <div class="mt-8 space-y-6">
        @foreach ($skills as $category => $categorySkills)
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">{{ $category }}</h3>

                <div class="mt-4 space-y-2">
                    @foreach ($categorySkills as $skill)
                        <form method="POST" action="{{ route('admin.cv.skills.update', $skill) }}" class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-100 p-3">
                            @csrf
                            @method('PATCH')

                            <input name="name" value="{{ $skill->name }}" class="rounded-lg border-gray-300 text-sm">
                            <input name="category" value="{{ $skill->category }}" placeholder="Catégorie" class="rounded-lg border-gray-300 text-sm">

                            <select name="is_active" class="rounded-lg border-gray-300 text-sm">
                                <option value="1" @selected($skill->is_active)>Active</option>
                                <option value="0" @selected(! $skill->is_active)>Désactivée</option>
                            </select>

                            <button class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700">
                                Enregistrer
                            </button>

                            <button
                                type="button"
                                onclick="if(confirm('Supprimer cette compétence du catalogue ?')) document.getElementById('del-skill-{{ $skill->id }}').submit();"
                                class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600"
                            >
                                Supprimer
                            </button>
                        </form>

                        <form id="del-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.cv.skills.destroy', $skill) }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection
