@extends('layouts.admin')

@section('title', 'Modifier le pack')
@section('page-title', 'Modifier le pack')

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
        <p class="text-sm text-gray-500">
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                {{ $pack->type === 'semestre' ? 'Pack semestre' : 'Pack module' }}
            </span>
        </p>

        <p class="mt-3 text-sm text-gray-600">
            Contenu :
            @if ($pack->isTypeSemestre())
                {{ $pack->semester?->program?->level?->name }}
                — {{ $pack->semester?->program?->name }}
                — {{ $pack->semester?->name }}
            @else
                {{ $pack->subject?->semester?->program?->level?->name }}
                — {{ $pack->subject?->name }}
            @endif
            <span class="text-xs text-gray-400">(non modifiable ici — supprime et recrée le pack pour changer le contenu)</span>
        </p>

        <form method="POST" action="{{ route('admin.centre.packs.update', $pack) }}" class="mt-6 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="text-sm font-medium">Nom du pack</label>
                <input name="name" value="{{ old('name', $pack->name) }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="text-sm font-medium">Description</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300">{{ old('description', $pack->description) }}</textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium">Prix (DH)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $pack->price) }}" class="mt-1 block w-full rounded-lg border-gray-300">
                </div>

                <div>
                    <label class="text-sm font-medium">Statut</label>
                    <select name="is_active" class="mt-1 block w-full rounded-lg border-gray-300">
                        <option value="1" @selected(old('is_active', $pack->is_active) == 1)>Actif (visible par les étudiants)</option>
                        <option value="0" @selected(old('is_active', $pack->is_active) == 0)>Inactif (masqué)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('admin.centre.packs.index') }}" class="text-sm text-gray-600 underline">
                    Annuler
                </a>

                <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </section>

    <section class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-6">
        <h2 class="font-bold text-red-800">Zone dangereuse</h2>

        <p class="mt-2 text-sm text-red-700">
            Supprimer ce pack supprimera aussi toutes les inscriptions des étudiants qui y sont liées
            ({{ $pack->enrollments()->count() }} inscription(s) au total,
            dont {{ $pack->enrollments()->where('status', 'active')->count() }} active(s)).
        </p>

        <form
            method="POST"
            action="{{ route('admin.centre.packs.destroy', $pack) }}"
            class="mt-4"
            onsubmit="return confirm('Supprimer définitivement ce pack et toutes les inscriptions associées ?');"
        >
            @csrf
            @method('DELETE')

            <button class="rounded-lg bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700">
                Supprimer ce pack
            </button>
        </form>
    </section>
@endsection
