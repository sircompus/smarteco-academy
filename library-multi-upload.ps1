$path0 = "C:\laragon\www\SEA\app\Http\Controllers\Admin\AcademicResourceController.php"
$content0 = @'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicResource;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicResourceController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->with('semester.program.level')
            ->where('is_active', true)
            ->get()
            ->sortBy(function ($subject) {
                return sprintf(
                    '%03d-%s-%03d-%03d',
                    $subject->semester?->program?->level?->sort_order ?? 999,
                    $subject->semester?->program?->name ?? '',
                    $subject->semester?->number ?? 999,
                    $subject->sort_order ?? 999
                );
            })
            ->values();

        $selectedSubject = null;
        $resourcesByType = collect();

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByType = $selectedSubject->resources()
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('type');
            }
        }

        return view('admin.centre.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByType' => $resourcesByType,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'type' => ['required', 'in:cours,td,examen,resume'],
            'professor_name' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:20480'],
        ]);

        $count = 0;
        $sortOrder = AcademicResource::where('subject_id', $data['subject_id'])->count();

        foreach ($request->file('files') as $file) {
            $path = $file->store('academic-resources/'.$data['subject_id'], 'public');

            // Si un seul fichier ET un titre saisi -> on l'utilise.
            // Sinon (plusieurs fichiers, ou pas de titre) -> nom du fichier.
            $title = (count($request->file('files')) === 1 && ! empty($data['title']))
                ? $data['title']
                : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            AcademicResource::create([
                'uuid' => (string) Str::uuid(),
                'subject_id' => $data['subject_id'],
                'type' => $data['type'],
                'professor_name' => $data['professor_name'] ?? null,
                'title' => $title,
                'description' => $data['description'] ?? null,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
                'is_published' => true,
                'sort_order' => $sortOrder,
            ]);

            $sortOrder++;
            $count++;
        }

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $data['subject_id']])
            ->with('success', "{$count} fichier(s) mis en ligne.");
    }

    public function destroy(AcademicResource $resource): RedirectResponse
    {
        $subjectId = $resource->subject_id;

        Storage::disk($resource->disk)->delete($resource->path);
        $resource->delete();

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $subjectId])
            ->with('success', 'Le document a été supprimé.');
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/AcademicResourceController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/AcademicResourceController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\resources\views\admin\centre\library\index.blade.php"
$content1 = @'
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

            <div class="mt-4 space-y-6">
                @foreach (\App\Models\AcademicResource::TYPES as $typeKey => $typeLabel)
                    <div>
                        <h3 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h3>

                        <div class="mt-2 space-y-2">
                            @forelse ($resourcesByType->get($typeKey, collect()) as $resource)
                                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-3">
                                    <div>
                                        <a href="{{ $resource->download_url }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:underline">
                                            {{ $resource->title }}
                                        </a>
                                        <p class="text-xs text-gray-400">
                                            {{ $resource->original_name }} · {{ $resource->size_for_humans }}
                                            @if ($resource->professor_name)
                                                · Prof. {{ $resource->professor_name }}
                                            @endif
                                        </p>
                                    </div>

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
                            @empty
                                <p class="text-xs text-gray-400">Aucun document.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        <section class="mt-8 rounded-2xl bg-white p-10 text-center shadow-sm">
            <p class="text-gray-500">Choisis un module ci-dessus pour voir ou ajouter des documents.</p>
        </section>
    @endif
@endsection

'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/centre/library/index.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/centre/library/index.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}
