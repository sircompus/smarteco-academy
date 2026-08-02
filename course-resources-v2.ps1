$path0 = "C:\laragon\www\SEA\app\Http\Controllers\Admin\CourseContentController.php"
$content0 = @'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseResource;
use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseContentController extends Controller
{
    public function edit(Course $course): View
    {
        $course->load([
            'subject.semester.program.level',
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->orderBy('sort_order');
            },
            'lessons' => function ($query) {
                $query->whereNull('course_section_id')->orderBy('sort_order');
            },
            'resources' => function ($query) {
                $query->orderBy('sort_order');
            },
        ]);

        return view('admin.centre.courses.content', [
            'course' => $course,
            'resourcesByType' => $course->resources->groupBy('type'),
        ]);
    }

    public function storeSection(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        CourseSection::create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'sort_order' => $course->sections()->count(),
            'is_active' => true,
        ]);

        return back()->with('success', 'La section a été créée.');
    }

    public function storeLesson(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'course_section_id' => ['nullable', 'exists:course_sections,id'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        Lesson::create([
            'uuid' => (string) Str::uuid(),
            'course_id' => $course->id,
            'course_section_id' => $data['course_section_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($course->id.'-'.$data['title']),
            'content' => $data['content'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'is_preview' => $request->boolean('is_preview'),
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $course->lessons()->count(),
        ]);

        return back()->with('success', 'La leçon a été créée.');
    }

    public function togglePublishLesson(Lesson $lesson): RedirectResponse
    {
        $lesson->update(['is_published' => ! $lesson->is_published]);

        return back()->with(
            'success',
            $lesson->is_published ? 'Leçon publiée.' : 'Leçon dépubliée.'
        );
    }

    public function destroyLesson(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return back()->with('success', 'La leçon a été supprimée.');
    }

    public function storeResource(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:cours,td,exercice,resume'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store('course-resources/'.$course->id, 'public');

        CourseResource::create([
            'uuid' => (string) Str::uuid(),
            'course_id' => $course->id,
            'uploaded_by' => Auth::id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'is_published' => true,
            'sort_order' => $course->resources()->count(),
        ]);

        return back()->with('success', 'Le fichier a été mis en ligne.');
    }

    public function destroyResource(CourseResource $resource): RedirectResponse
    {
        Storage::disk($resource->disk)->delete($resource->path);
        $resource->delete();

        return back()->with('success', 'Le fichier a été supprimé.');
    }

    private function uniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $number = 2;

        while (Lesson::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$number;
            $number++;
        }

        return $slug;
    }
}

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/CourseContentController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/CourseContentController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Http\Controllers\Professor\CourseController.php"
$content1 = @'
<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseResource;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->where('teacher_id', Auth::id())
            ->with('subject.semester.program.level')
            ->orderByDesc('created_at')
            ->get();

        return view('professor.dashboard', [
            'courses' => $courses,
        ]);
    }

    public function show(Course $course): View
    {
        $this->authorizeOwnership($course);

        $course->load([
            'subject.semester.program.level',
            'sections' => fn ($query) => $query->orderBy('sort_order'),
            'sections.lessons' => fn ($query) => $query->orderBy('sort_order'),
            'lessons' => fn ($query) => $query->whereNull('course_section_id')->orderBy('sort_order'),
            'resources' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $students = $this->enrolledStudents($course);

        return view('professor.courses.show', [
            'course' => $course,
            'students' => $students,
            'resourcesByType' => $course->resources->groupBy('type'),
        ]);
    }

    public function storeSection(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        CourseSection::create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'sort_order' => $course->sections()->count(),
            'is_active' => true,
        ]);

        return back()->with('success', 'La section a été créée.');
    }

    public function storeLesson(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'course_section_id' => ['nullable', 'exists:course_sections,id'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        Lesson::create([
            'uuid' => (string) Str::uuid(),
            'course_id' => $course->id,
            'course_section_id' => $data['course_section_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($course->id.'-'.$data['title']),
            'content' => $data['content'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'is_preview' => $request->boolean('is_preview'),
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $course->lessons()->count(),
        ]);

        return back()->with('success', 'La leçon a été créée.');
    }

    public function togglePublishLesson(Lesson $lesson): RedirectResponse
    {
        $this->authorizeOwnership($lesson->course);

        $lesson->update(['is_published' => ! $lesson->is_published]);

        return back()->with(
            'success',
            $lesson->is_published ? 'Leçon publiée.' : 'Leçon dépubliée.'
        );
    }

    public function destroyLesson(Lesson $lesson): RedirectResponse
    {
        $this->authorizeOwnership($lesson->course);

        $lesson->delete();

        return back()->with('success', 'La leçon a été supprimée.');
    }

    public function storeResource(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'type' => ['required', 'in:cours,td,exercice,resume'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:20480'], // 20 Mo max
        ]);

        $file = $request->file('file');
        $path = $file->store('course-resources/'.$course->id, 'public');

        CourseResource::create([
            'uuid' => (string) Str::uuid(),
            'course_id' => $course->id,
            'uploaded_by' => Auth::id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'is_published' => true,
            'sort_order' => $course->resources()->count(),
        ]);

        return back()->with('success', 'Le fichier a été mis en ligne.');
    }

    public function destroyResource(CourseResource $resource): RedirectResponse
    {
        $this->authorizeOwnership($resource->course);

        \Illuminate\Support\Facades\Storage::disk($resource->disk)->delete($resource->path);
        $resource->delete();

        return back()->with('success', 'Le fichier a été supprimé.');
    }

    private function authorizeOwnership(Course $course): void
    {
        abort_unless(
            $course->teacher_id === Auth::id(),
            403,
            'Ce cours ne vous est pas attribué.'
        );
    }

    /**
     * Étudiants ayant accès au module (matière) de ce cours,
     * via un pack module ou un pack semestre actif.
     */
    private function enrolledStudents(Course $course)
    {
        $subject = $course->subject;

        return User::query()
            ->whereHas('packEnrollments', function ($query) use ($subject) {
                $query->where('status', 'active')
                    ->whereHas('pack', function ($query) use ($subject) {
                        $query->where(function ($query) use ($subject) {
                            $query->where('type', 'module')
                                ->where('subject_id', $subject->id);
                        })->orWhere(function ($query) use ($subject) {
                            $query->where('type', 'semestre')
                                ->where('semester_id', $subject->semester_id);
                        });
                    });
            })
            ->orderBy('name')
            ->get();
    }

    private function uniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $number = 2;

        while (Lesson::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$number;
            $number++;
        }

        return $slug;
    }
}

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Professor/CourseController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Professor/CourseController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\app\Http\Controllers\Student\CourseController.php"
$content2 = @'
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->published()
            ->with([
                'subject.semester.program.level',
                'teacher',
            ])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('student.courses.index', [
            'courses' => $courses,
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless(
            $course->status === 'published'
            && $course->published_at !== null,
            404
        );

        $course->load([
            'subject.semester.program.level',
            'teacher',
            'sections' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            },
            'lessons' => function ($query) {
                $query->whereNull('course_section_id')
                    ->where('is_published', true)
                    ->orderBy('sort_order');
            },
            'resources' => function ($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            },
        ]);

        $hasAccess = auth()->user()->hasAccessToSubject($course->subject);

        return view('student.courses.show', [
            'course' => $course,
            'hasAccess' => $hasAccess,
            'resourcesByType' => $course->resources->groupBy('type'),
        ]);
    }
}
'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Student/CourseController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Student/CourseController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\app\Models\Course.php"
$content3 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'subject_id',
        'teacher_id',
        'title',
        'slug',
        'summary',
        'description',
        'thumbnail_path',
        'status',
        'published_at',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'teacher_id'
        );
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(CourseResource::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at');
    }
}
'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/Course.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/Course.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\app\Models\CourseResource.php"
$content4 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CourseResource extends Model
{
    use HasFactory;

    public const TYPES = [
        'cours' => 'Cours',
        'td' => 'TD',
        'exercice' => 'Exercices',
        'resume' => 'Résumés',
    ];

    protected $fillable = [
        'uuid',
        'course_id',
        'uploaded_by',
        'type',
        'title',
        'description',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CourseResource.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CourseResource.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\database\migrations\2026_08_01_090000_create_course_resources_table.php"
$content5 = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_resources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            // nullOnDelete : si le compte du prof est supprimé, la ressource
            // reste intacte, seul le lien "uploadé par" se détache.
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('type', ['cours', 'td', 'exercice', 'resume']);

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['course_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_resources');
    }
};

'@
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/migrations/2026_08_01_090000_create_course_resources_table.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/migrations/2026_08_01_090000_create_course_resources_table.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\admin\centre\courses\content.blade.php"
$content6 = @'
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

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">Ressources (Cours, TD, Exercices, Résumés)</h3>

        <form
            method="POST"
            action="{{ route('admin.centre.courses.resources.store', $course) }}"
            enctype="multipart/form-data"
            class="mt-4 grid gap-3 md:grid-cols-2"
        >
            @csrf

            <select name="type" class="rounded-lg border-gray-300" required>
                <option value="">Type de document</option>
                <option value="cours">Cours</option>
                <option value="td">TD</option>
                <option value="exercice">Exercices</option>
                <option value="resume">Résumé</option>
            </select>

            <input name="title" placeholder="Titre du document" class="rounded-lg border-gray-300" required>

            <input type="file" name="file" class="rounded-lg border-gray-300 md:col-span-2" required>

            <input name="description" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2">

            <button class="w-fit rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white md:col-span-2">
                Mettre en ligne
            </button>
        </form>

        <div class="mt-6 space-y-5">
            @foreach (\App\Models\CourseResource::TYPES as $typeKey => $typeLabel)
                <div>
                    <h4 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h4>

                    <div class="mt-2 space-y-2">
                        @forelse ($resourcesByType->get($typeKey, collect()) as $resource)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-3">
                                <div>
                                    <a href="{{ $resource->download_url }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:underline">
                                        {{ $resource->title }}
                                    </a>
                                    <p class="text-xs text-gray-400">
                                        {{ $resource->original_name }} · {{ $resource->size_for_humans }}
                                        @if ($resource->uploader)
                                            · par {{ $resource->uploader->name }}
                                        @endif
                                    </p>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('admin.centre.resources.destroy', $resource) }}"
                                    onsubmit="return confirm('Supprimer ce fichier ?');"
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
@endsection

'@
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/centre/courses/content.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/centre/courses/content.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\resources\views\professor\courses\show.blade.php"
$content7 = @'
@extends('layouts.professor')

@section('title', $course->title)
@section('page-title', $course->title)

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
            {{ $course->subject?->semester?->program?->level?->name }}
            — {{ $course->subject?->name }}
        </p>

        <h2 class="mt-1 text-xl font-bold">{{ $course->title }}</h2>

        <span class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $course->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $course->status === 'published' ? 'Publié' : 'Brouillon' }}
        </span>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">
            Étudiants inscrits ({{ $students->count() }})
        </h3>

        <div class="mt-4 space-y-2">
            @forelse ($students as $student)
                <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3 text-sm">
                    <span class="font-medium">{{ $student->name }}</span>
                    <span class="text-gray-400">{{ $student->email }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Aucun étudiant inscrit pour le moment sur ce module.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">Ajouter une section (optionnel)</h3>

        <form method="POST" action="{{ route('professor.courses.sections.store', $course) }}" class="mt-4 flex flex-wrap gap-3">
            @csrf
            <input name="title" placeholder="Titre de la section" class="flex-1 min-w-[200px] rounded-lg border-gray-300" required>
            <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                Ajouter la section
            </button>
        </form>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">Ajouter une leçon</h3>

        <form method="POST" action="{{ route('professor.courses.lessons.store', $course) }}" class="mt-4 space-y-4">
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
                        @include('professor.courses._lesson-row', ['lesson' => $lesson])
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
                    @include('professor.courses._lesson-row', ['lesson' => $lesson])
                @empty
                    @if ($course->sections->isEmpty())
                        <p class="text-sm text-gray-400">Aucune leçon pour ce cours.</p>
                    @endif
                @endforelse
            </div>
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="font-bold">Ressources (Cours, TD, Exercices, Résumés)</h3>

        <form
            method="POST"
            action="{{ route('professor.courses.resources.store', $course) }}"
            enctype="multipart/form-data"
            class="mt-4 grid gap-3 md:grid-cols-2"
        >
            @csrf

            <select name="type" class="rounded-lg border-gray-300" required>
                <option value="">Type de document</option>
                <option value="cours">Cours</option>
                <option value="td">TD</option>
                <option value="exercice">Exercices</option>
                <option value="resume">Résumé</option>
            </select>

            <input name="title" placeholder="Titre du document" class="rounded-lg border-gray-300" required>

            <input type="file" name="file" class="rounded-lg border-gray-300 md:col-span-2" required>

            <input name="description" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2">

            <button class="w-fit rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white md:col-span-2">
                Mettre en ligne
            </button>
        </form>

        <div class="mt-6 space-y-5">
            @foreach (\App\Models\CourseResource::TYPES as $typeKey => $typeLabel)
                <div>
                    <h4 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h4>

                    <div class="mt-2 space-y-2">
                        @forelse ($resourcesByType->get($typeKey, collect()) as $resource)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-3">
                                <div>
                                    <a href="{{ $resource->download_url }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:underline">
                                        {{ $resource->title }}
                                    </a>
                                    <p class="text-xs text-gray-400">
                                        {{ $resource->original_name }} · {{ $resource->size_for_humans }}
                                    </p>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('professor.resources.destroy', $resource) }}"
                                    onsubmit="return confirm('Supprimer ce fichier ?');"
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
@endsection

'@
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/professor/courses/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/professor/courses/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\resources\views\student\courses\show.blade.php"
$content8 = @'
@extends('layouts.student')

@section('title', $course->title)
@section('page-title', $course->title)

@section('content')
    <article class="rounded-2xl bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-indigo-600">
            {{ $course->subject->name }}
        </p>

        <h1 class="mt-3 text-3xl font-bold text-gray-900">
            {{ $course->title }}
        </h1>

        <p class="mt-4 text-gray-600">
            {{ $course->summary }}
        </p>

        <div class="mt-8 border-t pt-6 leading-8 text-gray-700">
            {!! nl2br(e($course->description)) !!}
        </div>

        @if ($course->teacher)
            <p class="mt-8 text-sm text-gray-500">
                Professeur : {{ $course->teacher->name }}
            </p>
        @endif

        @unless ($hasAccess)
            <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Tu n'as pas encore accès à ce module. Les leçons marquées « Aperçu gratuit »
                restent consultables ; pour le reste, inscris-toi au pack correspondant
                depuis <a href="{{ route('student.packs.index') }}" class="font-semibold underline">Packs (semestres / modules)</a>.
            </div>
        @endunless
    </article>

    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Contenu du cours</h2>

        @php
            $hasAnyLesson = $course->sections->sum(fn ($section) => $section->lessons->count())
                + $course->lessons->count();
        @endphp

        @if ($hasAnyLesson === 0)
            <p class="mt-4 text-sm text-gray-500">
                Aucune leçon n'a encore été publiée pour ce cours.
            </p>
        @else
            <div class="mt-5 space-y-6">
                @foreach ($course->sections as $section)
                    @if ($section->lessons->isNotEmpty())
                        <div>
                            <h3 class="text-sm font-bold text-gray-700">
                                {{ $section->title }}
                            </h3>

                            <div class="mt-2 space-y-2">
                                @foreach ($section->lessons as $lesson)
                                    @include('student.courses._lesson-row', ['lesson' => $lesson, 'hasAccess' => $hasAccess])
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @if ($course->lessons->isNotEmpty())
                    <div>
                        @if ($course->sections->isNotEmpty())
                            <h3 class="text-sm font-bold text-gray-700">Autres leçons</h3>
                        @endif

                        <div class="mt-2 space-y-2">
                            @foreach ($course->lessons as $lesson)
                                @include('student.courses._lesson-row', ['lesson' => $lesson, 'hasAccess' => $hasAccess])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>

    @if ($resourcesByType->isNotEmpty())
        <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">Documents à télécharger</h2>

            <div class="mt-5 space-y-5">
                @foreach (\App\Models\CourseResource::TYPES as $typeKey => $typeLabel)
                    @if ($resourcesByType->has($typeKey))
                        <div>
                            <h3 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h3>

                            <div class="mt-2 space-y-2">
                                @foreach ($resourcesByType->get($typeKey) as $resource)
                                    <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3 {{ $hasAccess ? '' : 'opacity-60' }}">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $resource->title }}</p>
                                            <p class="text-xs text-gray-400">{{ $resource->size_for_humans }}</p>
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
            </div>
        </section>
    @endif
@endsection

'@
$dir8 = Split-Path $path8 -Parent
if (-not (Test-Path $dir8)) { New-Item -ItemType Directory -Path $dir8 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/courses/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/courses/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path9 = "C:\laragon\www\SEA\routes\centre.php"
$content9 = @'
<?php

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
'@
$dir9 = Split-Path $path9 -Parent
if (-not (Test-Path $dir9)) { New-Item -ItemType Directory -Path $dir9 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path9, $content9, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/centre.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/centre.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path10 = "C:\laragon\www\SEA\routes\professor.php"
$content10 = @'
<?php

use App\Http\Controllers\Professor\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:professeur',
])
    ->prefix('professeur')
    ->name('professor.')
    ->group(function () {
        Route::get('/dashboard', [CourseController::class, 'index'])
            ->name('dashboard');

        Route::get('/courses/{course}', [CourseController::class, 'show'])
            ->name('courses.show');

        Route::post('/courses/{course}/sections', [CourseController::class, 'storeSection'])
            ->name('courses.sections.store');

        Route::post('/courses/{course}/lessons', [CourseController::class, 'storeLesson'])
            ->name('courses.lessons.store');

        Route::patch('/lessons/{lesson}/toggle-publish', [CourseController::class, 'togglePublishLesson'])
            ->name('lessons.toggle-publish');

        Route::delete('/lessons/{lesson}', [CourseController::class, 'destroyLesson'])
            ->name('lessons.destroy');

        Route::post('/courses/{course}/resources', [CourseController::class, 'storeResource'])
            ->name('courses.resources.store');

        Route::delete('/resources/{resource}', [CourseController::class, 'destroyResource'])
            ->name('resources.destroy');
    });

'@
$dir10 = Split-Path $path10 -Parent
if (-not (Test-Path $dir10)) { New-Item -ItemType Directory -Path $dir10 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path10, $content10, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/professor.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/professor.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie ci-dessus qu il n y a AUCUNE ligne ECHEC en rouge." -ForegroundColor Cyan
