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
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

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
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store('academic-resources/'.$data['subject_id'], 'public');

        AcademicResource::create([
            'uuid' => (string) Str::uuid(),
            'subject_id' => $data['subject_id'],
            'type' => $data['type'],
            'professor_name' => $data['professor_name'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'is_published' => true,
            'sort_order' => AcademicResource::where('subject_id', $data['subject_id'])->count(),
        ]);

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $data['subject_id']])
            ->with('success', 'Le document a été mis en ligne.');
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
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/AcademicResourceController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/AcademicResourceController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Http\Controllers\Student\LibraryController.php"
$content1 = @'
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->with('semester.program.level')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedSubject = null;
        $resourcesByType = collect();
        $hasAccess = false;

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByType = $selectedSubject->resources()
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('type');

                $hasAccess = Auth::user()->hasAccessToSubject($selectedSubject);
            }
        }

        return view('student.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByType' => $resourcesByType,
            'hasAccess' => $hasAccess,
        ]);
    }
}

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Student/LibraryController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Student/LibraryController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\app\Models\AcademicResource.php"
$content2 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AcademicResource extends Model
{
    use HasFactory;

    public const TYPES = [
        'cours' => 'Cours',
        'td' => 'TD',
        'examen' => 'Examens',
        'resume' => 'Résumés',
    ];

    protected $fillable = [
        'uuid',
        'subject_id',
        'type',
        'professor_name',
        'title',
        'description',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getSizeForHumansAttribute(): string
    {
        $size = $this->size;

        if ($size < 1024) {
            return $size.' o';
        }

        if ($size < 1024 * 1024) {
            return round($size / 1024, 1).' Ko';
        }

        return round($size / (1024 * 1024), 1).' Mo';
    }
}

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/AcademicResource.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/AcademicResource.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\app\Models\Subject.php"
$content3 = @'
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
}
'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/Subject.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/Subject.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\database\migrations\2026_08_01_100000_create_academic_resources_table.php"
$content4 = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_resources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->enum('type', ['cours', 'td', 'examen', 'resume']);

            $table->string('professor_name')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            // Qui a mis le fichier en ligne (compte du site) — distinct du nom
            // du prof de la fac, qui lui est juste une étiquette libre.
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['subject_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_resources');
    }
};

'@
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/migrations/2026_08_01_100000_create_academic_resources_table.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/migrations/2026_08_01_100000_create_academic_resources_table.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\resources\views\admin\centre\library\index.blade.php"
$content5 = @'
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
                        {{ $subject->semester?->program?->level?->name }}
                        — {{ $subject->semester?->program?->name }}
                        — {{ $subject->semester?->name }}
                        — {{ $subject->name }}
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
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/centre/library/index.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/centre/library/index.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\layouts\admin.blade.php"
$content6 = @'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Administration') — SmartEco Academy
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-slate-100 text-slate-900"
>
    {{-- Arrière-plan mobile --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu latéral administrateur --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900 text-white shadow-xl transition-transform duration-300 lg:translate-x-0 print:hidden"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center border-b border-slate-800 px-6">
            <a
                href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 font-bold text-white"
                >
                    SE
                </div>

                <div>
                    <p class="font-bold text-white">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-slate-400">
                        Administration
                    </p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Vue générale
            </p>

            <div class="space-y-1">
                {{-- Tableau de bord --}}
                <a
                    href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('supervisor.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard', 'supervisor.dashboard')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-12h8V3h-8v6z"
                        />
                    </svg>

                    Statistiques
                </a>
            </div>

            @if (auth()->user()->hasRole('admin'))
                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Centre de formation
                </p>

                <div class="space-y-1">
                    {{-- Module Centre --}}
                    <a
                        href="{{ route('admin.centre.index') }}"
                        class="{{ request()->routeIs('admin.centre.index')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118 14.5c0 2.21-2.686 4-6 4s-6-1.79-6-4c0-1.381.484-2.651 1.29-3.672L12 14z"
                            />
                        </svg>

                        Niveaux, filières & cours
                    </a>

                    {{-- Générateur de cursus --}}
                    <a
                        href="{{ route('admin.centre.curriculum.index') }}"
                        class="{{ request()->routeIs('admin.centre.curriculum.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>

                        Générateur de cursus
                    </a>

                    {{-- Packs (semestres / modules) --}}
                    <a
                        href="{{ route('admin.centre.packs.index') }}"
                        class="{{ request()->routeIs('admin.centre.packs.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                            />
                        </svg>

                        Packs (semestres / modules)
                    </a>

                    {{-- Bibliothèque de ressources --}}
                    <a
                        href="{{ route('admin.centre.library.index') }}"
                        class="{{ request()->routeIs('admin.centre.library.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>

                        Bibliothèque de ressources
                    </a>
                </div>
            @endif

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Inscriptions & validations
            </p>

            <div class="space-y-1">
                {{-- Inscriptions & paiements --}}
                <a
                    href="{{ route('admin.centre.pack-enrollments.index') }}"
                    class="{{ request()->routeIs('admin.centre.pack-enrollments.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>

                    Inscriptions & paiements aux packs
                </a>

                <a
                    href="{{ route('admin.centre.reports.index') }}"
                    class="{{ request()->routeIs('admin.centre.reports.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 17v-6m4 6V7m4 10v-3M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"
                        />
                    </svg>

                    État financier (imprimable)
                </a>

                {{-- Module Inscriptions (dossiers d'admission) --}}
                <a
                    href="{{ route('admin.registrations.index') }}"
                    class="{{ request()->routeIs('admin.registrations.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6M9 8h6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"
                        />
                    </svg>

                    Dossiers d’admission
                </a>

                {{-- Inscriptions & paiements formations --}}
                <a
                    href="{{ route('admin.trainings.enrollments.index') }}"
                    class="{{ request()->routeIs('admin.trainings.enrollments.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>

                    Inscriptions & paiements formations
                </a>
            </div>

            @if (auth()->user()->hasRole('admin'))
                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Autres modules
                </p>

                <div class="space-y-1">
                    {{-- Module Formations --}}
                    <a
                        href="{{ route('admin.trainings.index') }}"
                        class="{{ request()->routeIs('admin.trainings.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-9v6a6 6 0 0012 0v-6"
                            />
                        </svg>

                        Formations courtes
                    </a>
                </div>

                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Comptes
                </p>

                <div class="space-y-1">
                    {{-- Comptes Prof / Superviseur / Admin --}}
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"
                            />
                        </svg>

                        Utilisateurs & rôles
                    </a>
                </div>
            @endif
        </nav>

        {{-- Profil administrateur --}}
        <div class="border-t border-slate-800 p-4">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-xl bg-slate-800 p-3 transition hover:bg-slate-700"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500 font-semibold text-white"
                >
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-slate-400">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Zone principale --}}
    <div class="min-h-screen lg:pl-72 print:pl-0">
        {{-- Barre supérieure --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6 print:hidden"
        >
            <div class="flex items-center gap-4">
                {{-- Bouton mobile --}}
                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <span class="sr-only">
                        Ouvrir le menu
                    </span>

                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <div>
                    <h1 class="font-semibold text-gray-900">
                        @yield('page-title', 'Administration')
                    </h1>

                    <p class="hidden text-xs text-gray-500 sm:block">
                        Gestion générale de SmartEco Academy
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Accueil public --}}
                <a
                    href="{{ route('home') }}"
                    class="hidden rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 sm:block"
                >
                    Voir le site
                </a>

                {{-- Profil --}}
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Profil
                </a>

                {{-- Déconnexion --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700 sm:px-4"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Messages de succès --}}
        @if (session('success'))
            <div
                class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6"
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- Messages d’erreur --}}
        @if (session('error'))
            <div
                class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 sm:mx-6"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Contenu --}}
        <main class="p-4 sm:p-6 print:p-0">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
'@
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/admin.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/admin.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\resources\views\layouts\student.blade.php"
$content7 = @'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Espace étudiant') — SmartEco Academy
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-gray-100 text-gray-900"
>
    {{-- Arrière-plan mobile --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu latéral étudiant --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
            <a
                href="{{ route('student.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white"
                >
                    SE
                </div>

                <div>
                    <p class="font-bold text-gray-900">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-gray-500">
                        Espace étudiant
                    </p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Principal
            </p>

            <div class="space-y-1">
                {{-- Tableau de bord --}}
                <a
                    href="{{ route('student.dashboard') }}"
                    class="{{ request()->routeIs('student.dashboard')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-12h8V3h-8v6z"
                        />
                    </svg>

                    Tableau de bord
                </a>

                {{-- Cours du module Centre --}}
                <a
                    href="{{ route('student.courses.index') }}"
                    class="{{ request()->routeIs('student.courses.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A8.968 8.968 0 003 6.253v13A8.968 8.968 0 017.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5A8.968 8.968 0 0121 6.253v13A8.968 8.968 0 0016.5 18c-1.746 0-3.332.477-4.5 1.253"
                        />
                    </svg>

                    Mes cours
                </a>

                {{-- Packs (semestres / modules) --}}
                <a
                    href="{{ route('student.packs.index') }}"
                    class="{{ request()->routeIs('student.packs.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        />
                    </svg>

                    Packs (semestres / modules)
                </a>

                {{-- Bibliothèque de ressources --}}
                <a
                    href="{{ route('student.library.index') }}"
                    class="{{ request()->routeIs('student.library.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>

                    Bibliothèque de ressources
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Formation
            </p>

            <div class="space-y-1">
                {{-- Inscriptions --}}
                <a
    href="{{ route('student.registrations.index') }}"
    class="{{ request()->routeIs('student.registrations.*')
        ? 'bg-indigo-50 text-indigo-700'
        : 'text-gray-700 hover:bg-gray-100' }}
        flex items-center rounded-lg px-4 py-3 text-sm font-medium"
>
    Mes inscriptions
</a>

                {{-- Formations --}}
                <a
                    href="{{ route('student.trainings.index') }}"
                    class="{{ request()->routeIs('student.trainings.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"
                        />
                    </svg>

                    Mes formations
                </a>

                {{-- Examens --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5h6m-6 4h6m-6 4h4m-8-9h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"
                        />
                    </svg>

                    Mes examens
                </a>

                {{-- Projets --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 7h18M5 7v12h14V7M9 11h6"
                        />
                    </svg>

                    Mes projets
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Services
            </p>

            <div class="space-y-1">
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    CV ATS
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Portfolio
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Community
                </a>
            </div>
        </nav>

        {{-- Profil étudiant --}}
        <div class="border-t border-gray-200 p-4">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-lg p-3 transition hover:bg-gray-100"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700"
                >
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-gray-500">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Zone principale --}}
    <div class="min-h-screen lg:pl-64">
        {{-- Barre supérieure --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6"
        >
            <div class="flex items-center gap-4">
                {{-- Bouton mobile --}}
                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <span class="sr-only">
                        Ouvrir le menu
                    </span>

                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <div>
                    <h1 class="font-semibold text-gray-900">
                        @yield('page-title', 'Tableau de bord')
                    </h1>

                    <p class="hidden text-xs text-gray-500 sm:block">
                        Bienvenue sur votre espace personnel
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Notifications --}}
                <button
                    type="button"
                    class="relative rounded-lg p-2 text-gray-600 transition hover:bg-gray-100"
                    title="Notifications"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>

                    @if (auth()->user()->unreadNotifications()->count() > 0)
                        <span
                            class="absolute right-0 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                        >
                            {{ auth()->user()->unreadNotifications()->count() }}
                        </span>
                    @endif
                </button>

                {{-- Profil --}}
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Profil
                </a>

                {{-- Déconnexion --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 sm:px-4"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Messages --}}
        @if (session('success'))
            <div
                class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6"
            >
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 sm:mx-6"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Contenu --}}
        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
'@
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/student.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/student.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\resources\views\student\library\index.blade.php"
$content8 = @'
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
                        {{ $subject->semester?->program?->level?->name }}
                        — {{ $subject->semester?->program?->name }}
                        — {{ $subject->semester?->name }}
                        — {{ $subject->name }}
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
$dir8 = Split-Path $path8 -Parent
if (-not (Test-Path $dir8)) { New-Item -ItemType Directory -Path $dir8 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/library/index.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/library/index.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path9 = "C:\laragon\www\SEA\routes\centre.php"
$content9 = @'
<?php

use App\Http\Controllers\Admin\AcademicResourceController;
use App\Http\Controllers\Admin\CentreController;
use App\Http\Controllers\Admin\CourseContentController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\PackController as AdminPackController;
use App\Http\Controllers\Admin\PackEnrollmentController;
use App\Http\Controllers\Admin\PaymentReportController;
use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\PackController as StudentPackController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:admin',
    'module.active:centre',
])
    ->prefix('admin/centre')
    ->name('admin.centre.')
    ->group(function () {
        Route::get('/', [CentreController::class, 'index'])
            ->name('index');

        Route::post('/levels', [CentreController::class, 'storeLevel'])
            ->name('levels.store');

        Route::post('/programs', [CentreController::class, 'storeProgram'])
            ->name('programs.store');

        Route::post('/semesters', [CentreController::class, 'storeSemester'])
            ->name('semesters.store');

        Route::post('/subjects', [CentreController::class, 'storeSubject'])
            ->name('subjects.store');

        Route::post('/courses', [CentreController::class, 'storeCourse'])
            ->name('courses.store');

        Route::patch(
            '/courses/{course}/publish',
            [CentreController::class, 'publishCourse']
        )->name('courses.publish');

        Route::get('/packs', [AdminPackController::class, 'index'])
            ->name('packs.index');

        Route::post('/packs', [AdminPackController::class, 'store'])
            ->name('packs.store');

        Route::post('/packs/generate', [AdminPackController::class, 'generate'])
            ->name('packs.generate');

        Route::get('/packs/{pack}/edit', [AdminPackController::class, 'edit'])
            ->name('packs.edit');

        Route::patch('/packs/{pack}', [AdminPackController::class, 'update'])
            ->name('packs.update');

        Route::delete('/packs/{pack}', [AdminPackController::class, 'destroy'])
            ->name('packs.destroy');

        Route::delete('/packs', [AdminPackController::class, 'destroyBulk'])
            ->name('packs.destroy-bulk');

        Route::get('/curriculum', [CurriculumController::class, 'index'])
            ->name('curriculum.index');

        Route::post('/curriculum', [CurriculumController::class, 'sync'])
            ->name('curriculum.sync');

        Route::delete('/curriculum/programs/{program}', [CurriculumController::class, 'destroyProgram'])
            ->name('curriculum.programs.destroy');

        Route::get('/courses/{course}/content', [CourseContentController::class, 'edit'])
            ->name('courses.content');

        Route::post('/courses/{course}/sections', [CourseContentController::class, 'storeSection'])
            ->name('courses.sections.store');

        Route::post('/courses/{course}/lessons', [CourseContentController::class, 'storeLesson'])
            ->name('courses.lessons.store');

        Route::patch('/lessons/{lesson}/toggle-publish', [CourseContentController::class, 'togglePublishLesson'])
            ->name('lessons.toggle-publish');

        Route::delete('/lessons/{lesson}', [CourseContentController::class, 'destroyLesson'])
            ->name('lessons.destroy');

        Route::post('/courses/{course}/resources', [CourseContentController::class, 'storeResource'])
            ->name('courses.resources.store');

        Route::delete('/resources/{resource}', [CourseContentController::class, 'destroyResource'])
            ->name('resources.destroy');

        Route::get('/library', [AcademicResourceController::class, 'index'])
            ->name('library.index');

        Route::post('/library', [AcademicResourceController::class, 'store'])
            ->name('library.store');

        Route::delete('/library/{resource}', [AcademicResourceController::class, 'destroy'])
            ->name('library.destroy');
    });

// Validation des inscriptions/paiements : ouverte à l'admin ET au superviseur.
Route::middleware([
    'auth',
    'verified',
    'role:admin,superviseur',
    'module.active:centre',
])
    ->prefix('admin/centre')
    ->name('admin.centre.')
    ->group(function () {
        Route::get('/pack-enrollments', [PackEnrollmentController::class, 'index'])
            ->name('pack-enrollments.index');

        Route::patch(
            '/pack-enrollments/{packEnrollment}/status',
            [AdminPackController::class, 'updateEnrollmentStatus']
        )->name('pack-enrollments.status');

        Route::post(
            '/pack-enrollments/{packEnrollment}/payments',
            [PackEnrollmentController::class, 'storePayment']
        )->name('pack-enrollments.payments.store');

        Route::post(
            '/pack-enrollments/{packEnrollment}/reminder',
            [PackEnrollmentController::class, 'sendReminder']
        )->name('pack-enrollments.reminder');

        Route::patch(
            '/pack-enrollments/{packEnrollment}/toggle-pause',
            [PackEnrollmentController::class, 'togglePause']
        )->name('pack-enrollments.toggle-pause');

        Route::get(
            '/pack-enrollments/{packEnrollment}/payments/{payment}/receipt',
            [PackEnrollmentController::class, 'receipt']
        )->name('pack-enrollments.payments.receipt');

        Route::get('/reports', [PaymentReportController::class, 'index'])
            ->name('reports.index');
    });

Route::middleware([
    'auth',
    'verified',
    'module.active:centre',
])
    ->prefix('student/courses')
    ->name('student.courses.')
    ->group(function () {
        Route::get('/', [CourseController::class, 'index'])
            ->name('index');

        Route::get('/{course}', [CourseController::class, 'show'])
            ->name('show');
    });

Route::middleware([
    'auth',
    'verified',
    'module.active:centre',
])
    ->prefix('student/packs')
    ->name('student.packs.')
    ->group(function () {
        Route::get('/', [StudentPackController::class, 'index'])
            ->name('index');

        Route::post('/{pack}/enroll', [StudentPackController::class, 'enroll'])
            ->name('enroll');
    });

Route::middleware([
    'auth',
    'verified',
    'module.active:centre',
])
    ->prefix('student/library')
    ->name('student.library.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Student\LibraryController::class, 'index'])
            ->name('index');
    });
'@
$dir9 = Split-Path $path9 -Parent
if (-not (Test-Path $dir9)) { New-Item -ItemType Directory -Path $dir9 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path9, $content9, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/centre.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/centre.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie ci-dessus qu il n y a AUCUNE ligne ECHEC en rouge." -ForegroundColor Cyan
