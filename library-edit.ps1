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
        $resourcesByProfessor = collect();

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByProfessor = $selectedSubject->resources()
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy(fn ($resource) => $resource->professor_name ?: 'Professeur non renseigné')
                    ->map(fn ($group) => $group->groupBy('type'))
                    ->sortKeys();
            }
        }

        return view('admin.centre.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByProfessor' => $resourcesByProfessor,
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

    public function edit(AcademicResource $resource): View
    {
        return view('admin.centre.library.edit', [
            'resource' => $resource,
        ]);
    }

    public function update(Request $request, AcademicResource $resource): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:cours,td,examen,resume'],
            'professor_name' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $resource->update($data);

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $resource->subject_id])
            ->with('success', 'Le document a été mis à jour.');
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

$path1 = "C:\laragon\www\SEA\resources\views\admin\centre\library\edit.blade.php"
$content1 = @'
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

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/centre/library/edit.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/centre/library/edit.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\resources\views\admin\centre\library\index.blade.php"
$content2 = @'
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

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/centre/library/index.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/centre/library/index.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\routes\centre.php"
$content3 = @'
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

        Route::get('/library/{resource}/edit', [AcademicResourceController::class, 'edit'])
            ->name('library.edit');

        Route::patch('/library/{resource}', [AcademicResourceController::class, 'update'])
            ->name('library.update');
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
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/centre.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/centre.php -- $($_.Exception.Message)" -ForegroundColor Red
}
