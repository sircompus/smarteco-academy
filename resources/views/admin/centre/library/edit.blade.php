@extends('layouts.admin')

@section('title', 'Modifier le document')
@section('page-title', 'Modifier le document')

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

    <section class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow-sm">
        <p class="text-xs text-gray-400">
            Fichier : {{ $resource->original_name }} · {{ $resource->size_for_humans }}
        </p>

        <form method="POST" action="{{ route('admin.centre.library.update', $resource) }}" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="text-sm font-medium">Type de document</label>
                <select name="type" class="mt-1 block w-full rounded-lg border-gray-300" required>
                    <option value="cours" @selected($resource->type === 'cours')>Cours</option>
                    <option value="td" @selected($resource->type === 'td')>TD</option>
                    <option value="examen" @selected($resource->type === 'examen')>Examen</option>
                    <option value="resume" @selected($resource->type === 'resume')>Résumé</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Nom du professeur</label>
                <input name="professor_name" value="{{ old('professor_name', $resource->professor_name) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">Titre</label>
                <input name="title" value="{{ old('title', $resource->title) }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="text-sm font-medium">Description</label>
                <textarea name="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300">{{ old('description', $resource->description) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('admin.centre.library.index', ['subject_id' => $resource->subject_id]) }}" class="text-sm text-gray-600 underline">
                    Annuler
                </a>

                <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                    Enregistrer
                </button>
            </div>
        </form>
    </section>
@endsection
