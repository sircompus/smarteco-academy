$path0 = "C:\laragon\www\SEA\app\Models\User.php"
$content0 = @'
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;

    /**
     * Champs pouvant être remplis automatiquement.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * Champs cachés lors de la conversion en tableau ou JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversion automatique des types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Profil associé à l’utilisateur.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Rôles associés à l’utilisateur.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps();
    }

    /**
     * Historique des activités de l’utilisateur.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Vérifier si l’utilisateur possède un rôle.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()
            ->where('roles.name', $roleName)
            ->where('roles.is_active', true)
            ->exists();
    }

    public function packEnrollments(): HasMany
    {
        return $this->hasMany(PackEnrollment::class);
    }

    /**
     * Vérifie si l'étudiant a accès à une matière donnée,
     * via un pack "module" sur cette matière ou un pack
     * "semestre" incluant le semestre de cette matière.
     */
    public function hasAccessToSubject(Subject $subject): bool
    {
        return $this->packEnrollments()
            ->where('status', 'active')
            ->whereHas('pack', function ($query) use ($subject) {
                $query->where(function ($query) use ($subject) {
                    $query->where('type', 'module')
                        ->where('subject_id', $subject->id);
                })->orWhere(function ($query) use ($subject) {
                    $query->where('type', 'semestre')
                        ->where('semester_id', $subject->semester_id);
                });
            })
            ->exists();
    }

    /**
     * Accès strict : uniquement via un pack "semestre" actif (le pack
     * module seul ne suffit pas). Utilisé pour la bibliothèque de ressources.
     */
    public function hasSemesterAccessToSubject(Subject $subject): bool
    {
        return $this->packEnrollments()
            ->where('status', 'active')
            ->whereHas('pack', function ($query) use ($subject) {
                $query->where('type', 'semestre')
                    ->where('semester_id', $subject->semester_id);
            })
            ->exists();
    }

    /**
     * Vérifier si l’utilisateur possède au moins un rôle donné.
     *
     * @param array<int, string> $roleNames
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()
            ->whereIn('roles.name', $roleNames)
            ->where('roles.is_active', true)
            ->exists();
    }

    /**
     * Vérifier si l’utilisateur possède une permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles()
            ->where('roles.is_active', true)
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('permissions.slug', $permissionSlug);
            })
            ->exists();
    }
    
    public function registrations(): HasMany
    {
    return $this->hasMany(Registration::class);
    }
    
    public function trainingEnrollments(): HasMany
    {
    return $this->hasMany(TrainingEnrollment::class);
    }
}
'@
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/User.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/User.php -- $($_.Exception.Message)" -ForegroundColor Red
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
        $hasAccess = false;

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByProfessor = $selectedSubject->resources()
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy(fn ($resource) => $resource->professor_name ?: 'Professeur non renseigné')
                    ->map(fn ($group) => $group->groupBy('type'))
                    ->sortKeys();

                $hasAccess = Auth::user()->hasSemesterAccessToSubject($selectedSubject);
            }
        }

        return view('student.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByProfessor' => $resourcesByProfessor,
            'hasAccess' => $hasAccess,
        ]);
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Student/LibraryController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Student/LibraryController.php -- $($_.Exception.Message)" -ForegroundColor Red
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
                Tu n'as pas encore accès à cette bibliothèque. L'accès aux documents nécessite
                une inscription au <strong>semestre complet</strong> (un pack limité à ce seul module
                ne suffit pas). Inscris-toi depuis
                <a href="{{ route('student.packs.index') }}" class="font-semibold underline">Packs (semestres / modules)</a>.
            </div>
        @endunless

        <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">{{ $selectedSubject->name }}</h2>

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
                                @if ($byType->has($typeKey))
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h4>

                                        <div class="mt-2 space-y-2">
                                            @foreach ($byType->get($typeKey) as $resource)
                                                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-3 {{ $hasAccess ? '' : 'opacity-60' }}">
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
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucun document disponible pour ce module pour le moment.</p>
                @endforelse
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
