$path0 = "C:\laragon\www\SEA\app\Models\Subject.php"
$content0 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'semester_id',
        'name',
        'slug',
        'code',
        'description',
        'credits',
        'coefficient',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'decimal:2',
            'coefficient' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function packs(): HasMany
    {
        return $this->hasMany(Pack::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(AcademicResource::class);
    }

    /**
     * Libellé compact pour les listes : "DEUG S1 Gestion" au lieu de
     * "DEUG — Tronc commun en gestion — Semestre 1". Raccourcit
     * spécifiquement les troncs communs ("Tronc commun en X" -> "X").
     */
    public function getCompactLabelAttribute(): string
    {
        $level = $this->semester?->program?->level?->name;
        $semesterNumber = $this->semester?->number;
        $programName = $this->semester?->program?->name;

        $shortProgram = $programName
            ? preg_replace('/^Tronc commun en /i', '', $programName)
            : $programName;

        $shortProgram = $shortProgram ? ucfirst($shortProgram) : $programName;

        return trim("{$level} S{$semesterNumber} {$shortProgram}");
    }
}
'@
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/Subject.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/Subject.php -- $($_.Exception.Message)" -ForegroundColor Red
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

                <input name="title" placeholder="Titre du document" class="rounded-lg border-gray-300 md:col-span-2" required>

                <input type="file" name="file" class="rounded-lg border-gray-300 md:col-span-2" required>

                <input name="description" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2">

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

$path2 = "C:\laragon\www\SEA\resources\views\student\library\index.blade.php"
$content2 = @'
@extends('layouts.student')

@section('title', 'Bibliothèque de ressources')
@section('page-title', 'Bibliothèque de ressources')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Choisir un module</h2>

        <form method="GET" action="{{ route('student.library.index') }}" class="mt-4">
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
        @unless ($hasAccess)
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Tu n'as pas encore accès à ce module. Inscris-toi au pack correspondant
                depuis <a href="{{ route('student.packs.index') }}" class="font-semibold underline">Packs (semestres / modules)</a>
                pour débloquer les téléchargements.
            </div>
        @endunless

        <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">{{ $selectedSubject->name }}</h2>

            <div class="mt-4 space-y-6">
                @foreach (\App\Models\AcademicResource::TYPES as $typeKey => $typeLabel)
                    @if ($resourcesByType->has($typeKey))
                        <div>
                            <h3 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h3>

                            <div class="mt-2 space-y-2">
                                @foreach ($resourcesByType->get($typeKey) as $resource)
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-3 {{ $hasAccess ? '' : 'opacity-60' }}">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $resource->title }}</p>
                                            <p class="text-xs text-gray-400">
                                                {{ $resource->size_for_humans }}
                                                @if ($resource->professor_name)
                                                    · Prof. {{ $resource->professor_name }}
                                                @endif
                                            </p>
                                        </div>

                                        @if ($hasAccess)
                                            <a
                                                href="{{ $resource->download_url }}"
                                                target="_blank"
                                                class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white"
                                            >
                                                Télécharger
                                            </a>
                                        @else
                                            <span class="text-xs font-semibold text-gray-400">🔒 Verrouillé</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @if ($resourcesByType->isEmpty())
                    <p class="text-sm text-gray-400">Aucun document disponible pour ce module pour le moment.</p>
                @endif
            </div>
        </section>
    @endif
@endsection

'@
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/library/index.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/library/index.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}
