$path0 = "C:\laragon\www\SEA\app\Http\Controllers\PublicPortfolioController.php"
$content0 = @'
<?php

namespace App\Http\Controllers;

use App\Models\CvProfile;
use Illuminate\View\View;

class PublicPortfolioController extends Controller
{
    public function show(string $slug): View
    {
        $profile = CvProfile::query()
            ->where('public_slug', $slug)
            ->where('is_public', true)
            ->with(['educations', 'experiences', 'skills', 'languages', 'certifications', 'projects'])
            ->firstOrFail();

        return view('portfolio.show', ['profile' => $profile]);
    }
}

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/PublicPortfolioController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/PublicPortfolioController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Http\Controllers\Student\CvController.php"
$content1 = @'
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CvCertification;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvProfile;
use App\Models\CvSkill;
use App\Models\PortfolioProject;
use App\Services\AtsScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CvController extends Controller
{
    public function edit(): View
    {
        $profile = $this->currentProfile();

        $profile->load([
            'educations', 'experiences', 'skills',
            'languages', 'certifications', 'projects',
        ]);

        $ats = app(AtsScoreService::class)->evaluate($profile);

        return view('student.cv.edit', [
            'profile' => $profile,
            'ats' => $ats,
        ]);
    }

    private function currentProfile(): CvProfile
    {
        return CvProfile::firstOrCreate(
            ['user_id' => Auth::id()],
            ['full_name' => Auth::user()->name, 'email' => Auth::user()->email]
        );
    }

    // --- Profil principal ---

    public function updateProfile(Request $request): RedirectResponse
    {
        $profile = $this->currentProfile();

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'headline' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:200'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'cv_template' => ['required', 'in:classique,moderne'],
            'portfolio_template' => ['required', 'in:elegant'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('cv-photos', 'public');
        }

        $profile->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function togglePublic(Request $request): RedirectResponse
    {
        $profile = $this->currentProfile();

        if (! $profile->is_public && ! $profile->public_slug) {
            $profile->public_slug = Str::slug($profile->full_name ?: 'portfolio').'-'.Str::lower(Str::random(6));
        }

        $profile->is_public = ! $profile->is_public;
        $profile->save();

        return back()->with(
            'success',
            $profile->is_public
                ? 'Ton portfolio est maintenant public : '.$profile->public_url
                : 'Ton portfolio est de nouveau privé.'
        );
    }

    // --- Formation ---

    public function storeEducation(Request $request): RedirectResponse
    {
        $data = $this->validateEducation($request);
        $profile = $this->currentProfile();

        $profile->educations()->create($data + ['sort_order' => $profile->educations()->count()]);

        return back()->with('success', 'Formation ajoutée.');
    }

    public function updateEducation(Request $request, CvEducation $education): RedirectResponse
    {
        $this->authorizeOwnership($education->profile);

        $education->update($this->validateEducation($request));

        return back()->with('success', 'Formation mise à jour.');
    }

    public function destroyEducation(CvEducation $education): RedirectResponse
    {
        $this->authorizeOwnership($education->profile);
        $education->delete();

        return back()->with('success', 'Formation supprimée.');
    }

    private function validateEducation(Request $request): array
    {
        return $request->validate([
            'institution' => ['required', 'string', 'max:150'],
            'degree' => ['nullable', 'string', 'max:150'],
            'field_of_study' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    // --- Expérience ---

    public function storeExperience(Request $request): RedirectResponse
    {
        $data = $this->validateExperience($request);
        $profile = $this->currentProfile();

        $profile->experiences()->create($data + ['sort_order' => $profile->experiences()->count()]);

        return back()->with('success', 'Expérience ajoutée.');
    }

    public function updateExperience(Request $request, CvExperience $experience): RedirectResponse
    {
        $this->authorizeOwnership($experience->profile);

        $experience->update($this->validateExperience($request));

        return back()->with('success', 'Expérience mise à jour.');
    }

    public function destroyExperience(CvExperience $experience): RedirectResponse
    {
        $this->authorizeOwnership($experience->profile);
        $experience->delete();

        return back()->with('success', 'Expérience supprimée.');
    }

    private function validateExperience(Request $request): array
    {
        return $request->validate([
            'company' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    // --- Compétences ---

    public function storeSkill(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'in:debutant,intermediaire,avance,expert'],
        ]);

        $profile = $this->currentProfile();
        $profile->skills()->create($data + ['sort_order' => $profile->skills()->count()]);

        return back()->with('success', 'Compétence ajoutée.');
    }

    public function destroySkill(CvSkill $skill): RedirectResponse
    {
        $this->authorizeOwnership($skill->profile);
        $skill->delete();

        return back()->with('success', 'Compétence supprimée.');
    }

    // --- Langues ---

    public function storeLanguage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'in:debutant,intermediaire,courant,bilingue,natif'],
        ]);

        $profile = $this->currentProfile();
        $profile->languages()->create($data + ['sort_order' => $profile->languages()->count()]);

        return back()->with('success', 'Langue ajoutée.');
    }

    public function destroyLanguage(CvLanguage $language): RedirectResponse
    {
        $this->authorizeOwnership($language->profile);
        $language->delete();

        return back()->with('success', 'Langue supprimée.');
    }

    // --- Certifications ---

    public function storeCertification(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'issuer' => ['nullable', 'string', 'max:150'],
            'date_obtained' => ['nullable', 'date'],
            'credential_url' => ['nullable', 'url', 'max:255'],
        ]);

        $profile = $this->currentProfile();
        $profile->certifications()->create($data + ['sort_order' => $profile->certifications()->count()]);

        return back()->with('success', 'Certification ajoutée.');
    }

    public function destroyCertification(CvCertification $certification): RedirectResponse
    {
        $this->authorizeOwnership($certification->profile);
        $certification->delete();

        return back()->with('success', 'Certification supprimée.');
    }

    // --- Projets (portfolio) ---

    public function storeProject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('portfolio-projects', 'public');
        }

        $profile = $this->currentProfile();
        $profile->projects()->create($data + ['sort_order' => $profile->projects()->count()]);

        return back()->with('success', 'Projet ajouté.');
    }

    public function updateProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $this->authorizeOwnership($project->profile);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('portfolio-projects', 'public');
        }

        $project->update($data);

        return back()->with('success', 'Projet mis à jour.');
    }

    public function destroyProject(PortfolioProject $project): RedirectResponse
    {
        $this->authorizeOwnership($project->profile);
        $project->delete();

        return back()->with('success', 'Projet supprimé.');
    }

    // --- Rendus imprimables ---

    public function showCv(): View
    {
        $profile = $this->currentProfile();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications']);

        $view = $profile->cv_template === 'moderne' ? 'student.cv.templates.moderne' : 'student.cv.templates.classique';

        return view($view, ['profile' => $profile]);
    }

    public function showAts(): View
    {
        $profile = $this->currentProfile();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications']);

        return view('student.cv.templates.ats', ['profile' => $profile]);
    }

    private function authorizeOwnership(CvProfile $profile): void
    {
        abort_unless($profile->user_id === Auth::id(), 403);
    }
}

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Student/CvController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Student/CvController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\app\Models\CvCertification.php"
$content2 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_profile_id', 'name', 'issuer', 'date_obtained', 'credential_url', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['date_obtained' => 'date'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }
}

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvCertification.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvCertification.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\app\Models\CvEducation.php"
$content3 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvEducation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_profile_id', 'institution', 'degree', 'field_of_study',
        'start_date', 'end_date', 'is_current', 'description', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }
}

'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvEducation.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvEducation.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\app\Models\CvExperience.php"
$content4 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_profile_id', 'company', 'position', 'location',
        'start_date', 'end_date', 'is_current', 'description', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }
}

'@
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvExperience.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvExperience.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\app\Models\CvLanguage.php"
$content5 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvLanguage extends Model
{
    use HasFactory;

    public const LEVELS = [
        'debutant' => 'Débutant',
        'intermediaire' => 'Intermédiaire',
        'courant' => 'Courant',
        'bilingue' => 'Bilingue',
        'natif' => 'Langue maternelle',
    ];

    protected $fillable = ['cv_profile_id', 'name', 'level', 'sort_order'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVELS[$this->level] ?? $this->level;
    }
}

'@
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvLanguage.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvLanguage.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\app\Models\CvProfile.php"
$content6 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CvProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'full_name',
        'headline',
        'email',
        'phone',
        'address',
        'photo_path',
        'summary',
        'linkedin_url',
        'github_url',
        'website_url',
        'cv_template',
        'portfolio_template',
        'is_public',
        'public_slug',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CvEducation::class)->orderBy('sort_order')->orderByDesc('end_date');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CvExperience::class)->orderBy('sort_order')->orderByDesc('end_date');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(CvSkill::class)->orderBy('sort_order');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(CvLanguage::class)->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(CvCertification::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(PortfolioProject::class)->orderBy('sort_order');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getPublicUrlAttribute(): ?string
    {
        return $this->public_slug ? route('portfolio.show', $this->public_slug) : null;
    }
}

'@
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvProfile.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvProfile.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\app\Models\CvSkill.php"
$content7 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvSkill extends Model
{
    use HasFactory;

    public const LEVELS = [
        'debutant' => 'Débutant',
        'intermediaire' => 'Intermédiaire',
        'avance' => 'Avancé',
        'expert' => 'Expert',
    ];

    protected $fillable = ['cv_profile_id', 'name', 'level', 'sort_order'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVELS[$this->level] ?? $this->level;
    }

    public function getLevelPercentAttribute(): int
    {
        return match ($this->level) {
            'debutant' => 25,
            'intermediaire' => 50,
            'avance' => 75,
            'expert' => 100,
            default => 50,
        };
    }
}

'@
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvSkill.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvSkill.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\app\Models\PortfolioProject.php"
$content8 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PortfolioProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_profile_id', 'title', 'description', 'image_path',
        'project_url', 'repo_url', 'tags', 'sort_order',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function getTagsArrayAttribute(): array
    {
        return $this->tags ? array_map('trim', explode(',', $this->tags)) : [];
    }
}

'@
$dir8 = Split-Path $path8 -Parent
if (-not (Test-Path $dir8)) { New-Item -ItemType Directory -Path $dir8 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/PortfolioProject.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/PortfolioProject.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path9 = "C:\laragon\www\SEA\app\Models\User.php"
$content9 = @'
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

    public function cvProfile(): HasOne
    {
        return $this->hasOne(CvProfile::class);
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
$dir9 = Split-Path $path9 -Parent
if (-not (Test-Path $dir9)) { New-Item -ItemType Directory -Path $dir9 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path9, $content9, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/User.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/User.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path10 = "C:\laragon\www\SEA\app\Services\AtsScoreService.php"
$content10 = @'
<?php

namespace App\Services;

use App\Models\CvProfile;

class AtsScoreService
{
    /**
     * Calcule un score /100 et une liste de conseils d'amélioration,
     * basé sur des règles simples de compatibilité ATS (pas d'IA,
     * juste des vérifications de complétude et de bonnes pratiques).
     *
     * @return array{score: int, max: int, checks: array<int, array{label: string, passed: bool, advice: string}>}
     */
    public function evaluate(CvProfile $profile): array
    {
        $checks = [];

        // --- Informations de contact ---
        $checks[] = $this->check(
            'Nom complet renseigné',
            filled($profile->full_name),
            'Ajoute ton nom complet — sans lui, les logiciels ATS rejettent souvent le CV.'
        );

        $checks[] = $this->check(
            'Adresse e-mail renseignée',
            filled($profile->email),
            'Ajoute une adresse e-mail professionnelle et facilement identifiable.'
        );

        $checks[] = $this->check(
            'Téléphone renseigné',
            filled($profile->phone),
            'Ajoute un numéro de téléphone joignable.'
        );

        $checks[] = $this->check(
            'Titre / accroche professionnelle',
            filled($profile->headline),
            'Ajoute une accroche claire (ex: "Étudiant en Gestion — Comptabilité et Finance").'
        );

        // --- Résumé ---
        $summaryLength = $profile->summary ? str_word_count(strip_tags($profile->summary)) : 0;

        $checks[] = $this->check(
            'Résumé professionnel présent et suffisant (30-150 mots)',
            $summaryLength >= 30 && $summaryLength <= 150,
            $summaryLength === 0
                ? 'Ajoute un résumé de 2-3 phrases présentant ton profil.'
                : ($summaryLength < 30
                    ? 'Ton résumé est trop court — développe un peu plus (30 mots minimum).'
                    : 'Ton résumé est trop long — les ATS préfèrent des résumés concis (150 mots max).')
        );

        // --- Formation ---
        $checks[] = $this->check(
            'Au moins une formation renseignée',
            $profile->educations->isNotEmpty(),
            'Ajoute au moins une formation (diplôme, établissement, dates).'
        );

        $checks[] = $this->check(
            'Formations avec dates complètes',
            $profile->educations->isEmpty() || $profile->educations->every(
                fn ($e) => $e->start_date && ($e->end_date || $e->is_current)
            ),
            'Complète les dates de début/fin de chaque formation — les ATS analysent la chronologie.'
        );

        // --- Expérience ---
        $checks[] = $this->check(
            'Au moins une expérience ou un stage renseigné',
            $profile->experiences->isNotEmpty(),
            'Ajoute au moins une expérience professionnelle ou un stage, même court.'
        );

        $checks[] = $this->check(
            'Descriptions d\'expérience détaillées (10 mots min. chacune)',
            $profile->experiences->isEmpty() || $profile->experiences->every(
                fn ($e) => $e->description && str_word_count(strip_tags($e->description)) >= 10
            ),
            'Détaille chaque expérience avec des tâches concrètes et des résultats mesurables.'
        );

        // --- Compétences ---
        $checks[] = $this->check(
            'Au moins 5 compétences renseignées',
            $profile->skills->count() >= 5,
            'Liste au moins 5 compétences — les ATS scannent les mots-clés de compétences en priorité.'
        );

        // --- Langues ---
        $checks[] = $this->check(
            'Au moins une langue renseignée',
            $profile->languages->isNotEmpty(),
            'Ajoute au moins une langue avec ton niveau.'
        );

        // --- Photo ---
        $checks[] = $this->check(
            'Pas de photo dans la version ATS',
            true, // toujours vrai : notre version ATS n'inclut jamais de photo
            ''
        );

        $passed = collect($checks)->where('passed', true)->count();
        $total = count($checks);
        $score = $total > 0 ? (int) round(($passed / $total) * 100) : 0;

        return [
            'score' => $score,
            'max' => 100,
            'checks' => $checks,
        ];
    }

    private function check(string $label, bool $passed, string $advice): array
    {
        return [
            'label' => $label,
            'passed' => $passed,
            'advice' => $passed ? null : $advice,
        ];
    }
}

'@
$dir10 = Split-Path $path10 -Parent
if (-not (Test-Path $dir10)) { New-Item -ItemType Directory -Path $dir10 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path10, $content10, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Services/AtsScoreService.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Services/AtsScoreService.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path11 = "C:\laragon\www\SEA\database\migrations\2026_08_01_150000_create_cv_portfolio_tables.php"
$content11 = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('full_name')->nullable();
            $table->string('headline')->nullable(); // ex: "Étudiant en Gestion — Futur Comptable"
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('summary')->nullable();

            // Réseaux / liens
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('website_url')->nullable();

            $table->string('cv_template')->default('classique'); // classique | moderne
            $table->string('portfolio_template')->default('elegant');

            $table->boolean('is_public')->default(false);
            $table->string('public_slug')->unique()->nullable();

            $table->timestamps();
        });

        Schema::create('cv_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_profile_id')->constrained('cv_profiles')->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cv_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_profile_id')->constrained('cv_profiles')->cascadeOnDelete();
            $table->string('company');
            $table->string('position');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cv_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_profile_id')->constrained('cv_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->enum('level', ['debutant', 'intermediaire', 'avance', 'expert'])->default('intermediaire');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cv_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_profile_id')->constrained('cv_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->enum('level', ['debutant', 'intermediaire', 'courant', 'bilingue', 'natif'])->default('intermediaire');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cv_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_profile_id')->constrained('cv_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->date('date_obtained')->nullable();
            $table->string('credential_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_profile_id')->constrained('cv_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('project_url')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('tags')->nullable(); // séparés par virgule
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_projects');
        Schema::dropIfExists('cv_certifications');
        Schema::dropIfExists('cv_languages');
        Schema::dropIfExists('cv_skills');
        Schema::dropIfExists('cv_experiences');
        Schema::dropIfExists('cv_educations');
        Schema::dropIfExists('cv_profiles');
    }
};

'@
$dir11 = Split-Path $path11 -Parent
if (-not (Test-Path $dir11)) { New-Item -ItemType Directory -Path $dir11 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path11, $content11, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/migrations/2026_08_01_150000_create_cv_portfolio_tables.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/migrations/2026_08_01_150000_create_cv_portfolio_tables.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path12 = "C:\laragon\www\SEA\resources\views\layouts\student.blade.php"
$content12 = @'
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
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0 print:hidden"
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
                Carrière
            </p>

            <div class="space-y-1">
                {{-- CV & Portfolio --}}
                <a
                    href="{{ route('student.cv.edit') }}"
                    class="{{ request()->routeIs('student.cv.*')
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
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>

                    Mon CV & Portfolio
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
    <div class="min-h-screen lg:pl-64 print:pl-0">
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
        <main class="p-4 sm:p-6 print:p-0">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
'@
$dir12 = Split-Path $path12 -Parent
if (-not (Test-Path $dir12)) { New-Item -ItemType Directory -Path $dir12 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path12, $content12, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/student.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/student.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path13 = "C:\laragon\www\SEA\resources\views\portfolio\show.blade.php"
$content13 = @'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $profile->full_name }} — Portfolio</title>
    <meta name="description" content="{{ $profile->headline }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            @page { size: A4; margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <div class="no-print flex justify-center py-6">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    {{-- Bandeau d'en-tête --}}
    <header class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 py-16 text-white">
        <div class="mx-auto max-w-4xl px-6 text-center">
            @if ($profile->photo_url)
                <img
                    src="{{ $profile->photo_url }}"
                    class="mx-auto h-32 w-32 rounded-full border-4 border-white/40 object-cover shadow-xl"
                >
            @endif

            <h1 class="mt-6 text-4xl font-extrabold">{{ $profile->full_name }}</h1>

            @if ($profile->headline)
                <p class="mt-3 text-lg text-indigo-100">{{ $profile->headline }}</p>
            @endif

            <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm text-indigo-100">
                @if ($profile->email)
                    <a href="mailto:{{ $profile->email }}" class="hover:text-white">{{ $profile->email }}</a>
                @endif
                @if ($profile->phone)
                    <span>{{ $profile->phone }}</span>
                @endif
                @if ($profile->linkedin_url)
                    <a href="{{ $profile->linkedin_url }}" target="_blank" class="hover:text-white">LinkedIn</a>
                @endif
                @if ($profile->github_url)
                    <a href="{{ $profile->github_url }}" target="_blank" class="hover:text-white">GitHub</a>
                @endif
                @if ($profile->website_url)
                    <a href="{{ $profile->website_url }}" target="_blank" class="hover:text-white">Site web</a>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-6 py-12">

        @if ($profile->summary)
            <section class="rounded-2xl bg-white p-8 shadow-sm">
                <p class="text-center text-lg leading-8 text-gray-700">{{ $profile->summary }}</p>
            </section>
        @endif

        {{-- Projets --}}
        @if ($profile->projects->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-2xl font-extrabold text-gray-900">Projets</h2>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    @foreach ($profile->projects as $project)
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md">
                            @if ($project->image_url)
                                <img src="{{ $project->image_url }}" class="h-48 w-full object-cover">
                            @endif

                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900">{{ $project->title }}</h3>

                                @if ($project->description)
                                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ $project->description }}</p>
                                @endif

                                @if ($project->tags_array)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($project->tags_array as $tag)
                                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4 flex gap-3 text-sm font-semibold">
                                    @if ($project->project_url)
                                        <a href="{{ $project->project_url }}" target="_blank" class="text-indigo-600 hover:underline">Voir le projet →</a>
                                    @endif
                                    @if ($project->repo_url)
                                        <a href="{{ $project->repo_url }}" target="_blank" class="text-gray-500 hover:underline">Code source</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-10 grid gap-8 md:grid-cols-2">
            {{-- Expérience --}}
            @if ($profile->experiences->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Expérience</h2>

                    <div class="mt-4 space-y-4">
                        @foreach ($profile->experiences as $exp)
                            <div class="border-l-2 border-indigo-100 pl-4">
                                <p class="font-semibold text-gray-900">{{ $exp->position }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $exp->company }} · {{ $exp->start_date?->format('m/Y') }} –
                                    {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}
                                </p>
                                @if ($exp->description)
                                    <p class="mt-1 text-sm text-gray-600">{{ $exp->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Formation --}}
            @if ($profile->educations->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Formation</h2>

                    <div class="mt-4 space-y-4">
                        @foreach ($profile->educations as $edu)
                            <div class="border-l-2 border-indigo-100 pl-4">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $edu->institution }} · {{ $edu->start_date?->format('Y') }} –
                                    {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="mt-8 grid gap-8 md:grid-cols-3">
            {{-- Compétences --}}
            @if ($profile->skills->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Compétences</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($profile->skills as $skill)
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {{ $skill->name }}
                            </span>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Langues --}}
            @if ($profile->languages->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Langues</h2>
                    <ul class="mt-4 space-y-1 text-sm text-gray-600">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — {{ $lang->level_label }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Certifications --}}
            @if ($profile->certifications->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Certifications</h2>
                    <ul class="mt-4 space-y-1 text-sm text-gray-600">
                        @foreach ($profile->certifications as $cert)
                            <li>{{ $cert->name }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </main>

    <footer class="border-t border-gray-200 py-8 text-center text-xs text-gray-400">
        Portfolio généré via SmartEco Academy
    </footer>
</body>
</html>

'@
$dir13 = Split-Path $path13 -Parent
if (-not (Test-Path $dir13)) { New-Item -ItemType Directory -Path $dir13 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path13, $content13, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/portfolio/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/portfolio/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path14 = "C:\laragon\www\SEA\resources\views\student\cv\_section-certifications.blade.php"
$content14 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Certifications</h2>

    <div class="mt-4 space-y-2">
        @foreach ($profile->certifications as $certification)
            <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3">
                <div>
                    <p class="text-sm font-medium">{{ $certification->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $certification->issuer }}
                        @if ($certification->date_obtained)
                            · {{ $certification->date_obtained->format('m/Y') }}
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('student.cv.certifications.destroy', $certification) }}">
                    @csrf @method('DELETE')
                    <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">Supprimer</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.certifications.store') }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="name" placeholder="Nom de la certification" class="rounded-lg border-gray-300" required>
        <input name="issuer" placeholder="Organisme" class="rounded-lg border-gray-300">
        <input type="date" name="date_obtained" class="rounded-lg border-gray-300">
        <input name="credential_url" placeholder="Lien (optionnel)" class="rounded-lg border-gray-300">
        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">+ Ajouter</button>
    </form>
</section>

'@
$dir14 = Split-Path $path14 -Parent
if (-not (Test-Path $dir14)) { New-Item -ItemType Directory -Path $dir14 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path14, $content14, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-certifications.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-certifications.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path15 = "C:\laragon\www\SEA\resources\views\student\cv\_section-educations.blade.php"
$content15 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Formation</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->educations as $education)
            <form
                method="POST"
                action="{{ route('student.cv.educations.update', $education) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="institution" value="{{ $education->institution }}" placeholder="Établissement" class="rounded-lg border-gray-300" required>
                <input name="degree" value="{{ $education->degree }}" placeholder="Diplôme" class="rounded-lg border-gray-300">
                <input name="field_of_study" value="{{ $education->field_of_study }}" placeholder="Domaine" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $education->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $education->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2">{{ $education->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($education->is_current)>
                    En cours
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette formation ?')) document.getElementById('del-edu-{{ $education->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-edu-{{ $education->id }}" method="POST" action="{{ route('student.cv.educations.destroy', $education) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.educations.store') }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="institution" placeholder="Établissement" class="rounded-lg border-gray-300" required>
        <input name="degree" placeholder="Diplôme" class="rounded-lg border-gray-300">
        <input name="field_of_study" placeholder="Domaine" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            En cours
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une formation
        </button>
    </form>
</section>

'@
$dir15 = Split-Path $path15 -Parent
if (-not (Test-Path $dir15)) { New-Item -ItemType Directory -Path $dir15 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path15, $content15, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-educations.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-educations.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path16 = "C:\laragon\www\SEA\resources\views\student\cv\_section-experiences.blade.php"
$content16 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Expérience professionnelle</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->experiences as $experience)
            <form
                method="POST"
                action="{{ route('student.cv.experiences.update', $experience) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="company" value="{{ $experience->company }}" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
                <input name="position" value="{{ $experience->position }}" placeholder="Poste" class="rounded-lg border-gray-300" required>
                <input name="location" value="{{ $experience->location }}" placeholder="Lieu" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $experience->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $experience->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2">{{ $experience->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($experience->is_current)>
                    Poste actuel
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette expérience ?')) document.getElementById('del-exp-{{ $experience->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-exp-{{ $experience->id }}" method="POST" action="{{ route('student.cv.experiences.destroy', $experience) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.experiences.store') }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="company" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
        <input name="position" placeholder="Poste" class="rounded-lg border-gray-300" required>
        <input name="location" placeholder="Lieu" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            Poste actuel
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une expérience
        </button>
    </form>
</section>

'@
$dir16 = Split-Path $path16 -Parent
if (-not (Test-Path $dir16)) { New-Item -ItemType Directory -Path $dir16 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path16, $content16, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-experiences.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-experiences.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path17 = "C:\laragon\www\SEA\resources\views\student\cv\_section-languages.blade.php"
$content17 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Langues</h2>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($profile->languages as $language)
            <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                <span class="font-medium text-indigo-700">{{ $language->name }}</span>
                <span class="text-xs text-indigo-400">({{ $language->level_label }})</span>
                <form method="POST" action="{{ route('student.cv.languages.destroy', $language) }}">
                    @csrf @method('DELETE')
                    <button class="text-indigo-400 hover:text-red-600">×</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.languages.store') }}" class="mt-4 flex flex-wrap gap-2">
        @csrf
        <input name="name" placeholder="Ex : Français, Anglais..." class="rounded-lg border-gray-300" required>
        <select name="level" class="rounded-lg border-gray-300">
            <option value="debutant">Débutant</option>
            <option value="intermediaire" selected>Intermédiaire</option>
            <option value="courant">Courant</option>
            <option value="bilingue">Bilingue</option>
            <option value="natif">Langue maternelle</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter</button>
    </form>
</section>

'@
$dir17 = Split-Path $path17 -Parent
if (-not (Test-Path $dir17)) { New-Item -ItemType Directory -Path $dir17 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path17, $content17, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-languages.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-languages.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path18 = "C:\laragon\www\SEA\resources\views\student\cv\_section-projects.blade.php"
$content18 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Projets (Portfolio)</h2>
    <p class="mt-1 text-sm text-gray-500">Ces projets apparaissent sur ton portfolio public.</p>

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ($profile->projects as $project)
            <form
                method="POST"
                action="{{ route('student.cv.projects.update', $project) }}"
                enctype="multipart/form-data"
                class="space-y-2 rounded-xl border border-gray-100 p-4"
            >
                @csrf
                @method('PATCH')

                @if ($project->image_url)
                    <img src="{{ $project->image_url }}" class="h-32 w-full rounded-lg object-cover">
                @endif

                <input type="file" name="image" accept="image/*" class="w-full text-xs">
                <input name="title" value="{{ $project->title }}" placeholder="Titre du projet" class="w-full rounded-lg border-gray-300" required>
                <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border-gray-300">{{ $project->description }}</textarea>
                <input name="tags" value="{{ $project->tags }}" placeholder="Tags séparés par virgule" class="w-full rounded-lg border-gray-300">
                <input name="project_url" value="{{ $project->project_url }}" placeholder="Lien du projet" class="w-full rounded-lg border-gray-300">
                <input name="repo_url" value="{{ $project->repo_url }}" placeholder="Lien du code (optionnel)" class="w-full rounded-lg border-gray-300">

                <div class="flex items-center gap-2 pt-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>
                    <button
                        type="button"
                        onclick="if(confirm('Supprimer ce projet ?')) document.getElementById('del-proj-{{ $project->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-proj-{{ $project->id }}" method="POST" action="{{ route('student.cv.projects.destroy', $project) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.projects.store') }}" enctype="multipart/form-data" class="mt-4 space-y-2 rounded-xl border border-dashed border-gray-300 p-4">
        @csrf
        <input type="file" name="image" accept="image/*" class="w-full text-xs">
        <input name="title" placeholder="Titre du projet" class="w-full rounded-lg border-gray-300" required>
        <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border-gray-300"></textarea>
        <input name="tags" placeholder="Tags séparés par virgule (ex : Excel, Marketing)" class="w-full rounded-lg border-gray-300">
        <input name="project_url" placeholder="Lien du projet" class="w-full rounded-lg border-gray-300">
        <input name="repo_url" placeholder="Lien du code (optionnel)" class="w-full rounded-lg border-gray-300">
        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter un projet</button>
    </form>
</section>

'@
$dir18 = Split-Path $path18 -Parent
if (-not (Test-Path $dir18)) { New-Item -ItemType Directory -Path $dir18 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path18, $content18, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-projects.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-projects.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path19 = "C:\laragon\www\SEA\resources\views\student\cv\_section-skills.blade.php"
$content19 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Compétences</h2>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($profile->skills as $skill)
            <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                <span class="font-medium text-indigo-700">{{ $skill->name }}</span>
                <span class="text-xs text-indigo-400">({{ $skill->level_label }})</span>
                <form method="POST" action="{{ route('student.cv.skills.destroy', $skill) }}">
                    @csrf @method('DELETE')
                    <button class="text-indigo-400 hover:text-red-600">×</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.skills.store') }}" class="mt-4 flex flex-wrap gap-2">
        @csrf
        <input name="name" placeholder="Ex : Excel, Comptabilité..." class="rounded-lg border-gray-300" required>
        <select name="level" class="rounded-lg border-gray-300">
            <option value="debutant">Débutant</option>
            <option value="intermediaire" selected>Intermédiaire</option>
            <option value="avance">Avancé</option>
            <option value="expert">Expert</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter</button>
    </form>
</section>

'@
$dir19 = Split-Path $path19 -Parent
if (-not (Test-Path $dir19)) { New-Item -ItemType Directory -Path $dir19 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path19, $content19, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-skills.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-skills.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path20 = "C:\laragon\www\SEA\resources\views\student\cv\edit.blade.php"
$content20 = @'
@extends('layouts.student')

@section('title', 'Mon CV & Portfolio')
@section('page-title', 'Mon CV & Portfolio')

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

    {{-- Actions rapides : téléchargements + partage --}}
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold">Exports & partage</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Génère ton CV, sa version ATS, ou partage ton portfolio public.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('student.cv.download.cv') }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Voir / Imprimer mon CV
                </a>

                <a href="{{ route('student.cv.download.ats') }}" target="_blank" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                    Version ATS
                </a>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 rounded-xl bg-gray-50 p-4">
            <form method="POST" action="{{ route('student.cv.public.toggle') }}">
                @csrf
                @method('PATCH')
                <button class="rounded-lg {{ $profile->is_public ? 'bg-red-50 text-red-600' : 'bg-green-600 text-white' }} px-4 py-2 text-sm font-semibold">
                    {{ $profile->is_public ? 'Rendre privé' : 'Rendre public' }}
                </button>
            </form>

            @if ($profile->is_public && $profile->public_url)
                <div class="text-sm">
                    <span class="text-gray-500">Lien public :</span>
                    <a href="{{ $profile->public_url }}" target="_blank" class="font-semibold text-indigo-600 hover:underline">
                        {{ $profile->public_url }}
                    </a>
                </div>
            @else
                <p class="text-sm text-gray-400">Ton portfolio est actuellement privé.</p>
            @endif
        </div>
    </section>

    {{-- Score ATS --}}
    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-lg font-bold">Score de compatibilité ATS</h2>

            <div class="flex items-center gap-3">
                <div class="h-3 w-40 overflow-hidden rounded-full bg-gray-100">
                    <div
                        class="h-full {{ $ats['score'] >= 80 ? 'bg-green-500' : ($ats['score'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                        style="width: {{ $ats['score'] }}%"
                    ></div>
                </div>
                <span class="text-xl font-extrabold {{ $ats['score'] >= 80 ? 'text-green-600' : ($ats['score'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $ats['score'] }}/100
                </span>
            </div>
        </div>

        <div class="mt-4 grid gap-2 md:grid-cols-2">
            @foreach ($ats['checks'] as $check)
                <div class="flex items-start gap-2 text-sm">
                    <span>{{ $check['passed'] ? '✅' : '⚠️' }}</span>
                    <div>
                        <p class="{{ $check['passed'] ? 'text-gray-700' : 'font-medium text-amber-700' }}">
                            {{ $check['label'] }}
                        </p>
                        @if (! $check['passed'])
                            <p class="text-xs text-gray-500">{{ $check['advice'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Informations personnelles --}}
    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm" x-data="{ open: true }">
        <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
            <h2 class="text-lg font-bold">Informations personnelles</h2>
            <span x-text="open ? '−' : '+'" class="text-xl text-gray-400"></span>
        </button>

        <form
            x-show="open"
            method="POST"
            action="{{ route('student.cv.profile.update') }}"
            enctype="multipart/form-data"
            class="mt-4 grid gap-4 md:grid-cols-2"
        >
            @csrf
            @method('PATCH')

            <div class="md:col-span-2 flex items-center gap-4">
                @if ($profile->photo_url)
                    <img src="{{ $profile->photo_url }}" class="h-16 w-16 rounded-full object-cover" alt="Photo">
                @endif
                <input type="file" name="photo" accept="image/*" class="text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Nom complet</label>
                <input name="full_name" value="{{ old('full_name', $profile->full_name) }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="text-sm font-medium">Titre / accroche</label>
                <input name="headline" value="{{ old('headline', $profile->headline) }}" placeholder="Ex : Étudiant en Gestion — Comptabilité" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $profile->email) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">Téléphone</label>
                <input name="phone" value="{{ old('phone', $profile->phone) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium">Adresse</label>
                <input name="address" value="{{ old('address', $profile->address) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium">Résumé professionnel</label>
                <textarea name="summary" rows="4" class="mt-1 block w-full rounded-lg border-gray-300">{{ old('summary', $profile->summary) }}</textarea>
            </div>

            <div>
                <label class="text-sm font-medium">LinkedIn</label>
                <input name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" placeholder="https://linkedin.com/in/..." class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">GitHub</label>
                <input name="github_url" value="{{ old('github_url', $profile->github_url) }}" placeholder="https://github.com/..." class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium">Site web personnel</label>
                <input name="website_url" value="{{ old('website_url', $profile->website_url) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">Modèle de CV</label>
                <select name="cv_template" class="mt-1 block w-full rounded-lg border-gray-300">
                    <option value="classique" @selected($profile->cv_template === 'classique')>Classique</option>
                    <option value="moderne" @selected($profile->cv_template === 'moderne')>Moderne</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Modèle de portfolio</label>
                <select name="portfolio_template" class="mt-1 block w-full rounded-lg border-gray-300">
                    <option value="elegant" @selected($profile->portfolio_template === 'elegant')>Élégant</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                    Enregistrer
                </button>
            </div>
        </form>
    </section>

    @include('student.cv._section-educations', ['profile' => $profile])
    @include('student.cv._section-experiences', ['profile' => $profile])
    @include('student.cv._section-skills', ['profile' => $profile])
    @include('student.cv._section-languages', ['profile' => $profile])
    @include('student.cv._section-certifications', ['profile' => $profile])
    @include('student.cv._section-projects', ['profile' => $profile])
@endsection

'@
$dir20 = Split-Path $path20 -Parent
if (-not (Test-Path $dir20)) { New-Item -ItemType Directory -Path $dir20 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path20, $content20, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/edit.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/edit.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path21 = "C:\laragon\www\SEA\resources\views\student\cv\templates\ats.blade.php"
$content21 = @'
@extends('layouts.student')

@section('title', 'Mon CV — Version ATS')
@section('page-title', 'Mon CV — Version ATS')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 20mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-gray-800 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-10 font-mono text-sm leading-6 text-gray-900 print:border-0 print:p-0 print:shadow-none">

        <p class="mb-4 rounded-lg bg-amber-50 p-3 font-sans text-xs text-amber-700 print:hidden">
            Cette version simplifiée (une seule colonne, sans image, sans mise en forme complexe)
            est optimisée pour être correctement lue par les logiciels de tri automatique (ATS).
        </p>

        <h1 class="text-lg font-bold uppercase">{{ $profile->full_name }}</h1>
        @if ($profile->headline)
            <p>{{ $profile->headline }}</p>
        @endif

        <p class="mt-2">
            @if ($profile->email) {{ $profile->email }} @endif
            @if ($profile->phone) | {{ $profile->phone }} @endif
            @if ($profile->address) | {{ $profile->address }} @endif
        </p>

        @if ($profile->linkedin_url) <p>LinkedIn : {{ $profile->linkedin_url }}</p> @endif
        @if ($profile->github_url) <p>GitHub : {{ $profile->github_url }}</p> @endif

        @if ($profile->summary)
            <h2 class="mt-6 font-bold uppercase">PROFIL</h2>
            <p>{{ $profile->summary }}</p>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">EXPERIENCE PROFESSIONNELLE</h2>
            @foreach ($profile->experiences as $exp)
                <p class="mt-3 font-bold">{{ $exp->position }} - {{ $exp->company }}</p>
                <p>
                    {{ $exp->start_date?->format('m/Y') }} -
                    {{ $exp->is_current ? 'Present' : $exp->end_date?->format('m/Y') }}
                    @if ($exp->location) | {{ $exp->location }} @endif
                </p>
                @if ($exp->description)
                    <p>{{ $exp->description }}</p>
                @endif
            @endforeach
        @endif

        @if ($profile->educations->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">FORMATION</h2>
            @foreach ($profile->educations as $edu)
                <p class="mt-3 font-bold">{{ $edu->degree }} - {{ $edu->institution }}</p>
                <p>
                    {{ $edu->field_of_study }} |
                    {{ $edu->start_date?->format('Y') }} - {{ $edu->is_current ? 'Present' : $edu->end_date?->format('Y') }}
                </p>
                @if ($edu->description)
                    <p>{{ $edu->description }}</p>
                @endif
            @endforeach
        @endif

        @if ($profile->skills->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">COMPETENCES</h2>
            <p>{{ $profile->skills->pluck('name')->implode(', ') }}</p>
        @endif

        @if ($profile->languages->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">LANGUES</h2>
            <p>
                @foreach ($profile->languages as $lang)
                    {{ $lang->name }} ({{ $lang->level_label }}){{ ! $loop->last ? ', ' : '' }}
                @endforeach
            </p>
        @endif

        @if ($profile->certifications->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">CERTIFICATIONS</h2>
            @foreach ($profile->certifications as $cert)
                <p>{{ $cert->name }} @if ($cert->issuer) - {{ $cert->issuer }} @endif @if ($cert->date_obtained) ({{ $cert->date_obtained->format('Y') }}) @endif</p>
            @endforeach
        @endif
    </div>
@endsection

'@
$dir21 = Split-Path $path21 -Parent
if (-not (Test-Path $dir21)) { New-Item -ItemType Directory -Path $dir21 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path21, $content21, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/ats.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/ats.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path22 = "C:\laragon\www\SEA\resources\views\student\cv\templates\classique.blade.php"
$content22 = @'
@extends('layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle classique')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-10 text-sm print:border-0 print:p-0 print:shadow-none">

        <div class="flex items-center gap-6 border-b-2 border-gray-800 pb-6">
            @if ($profile->photo_url)
                <img src="{{ $profile->photo_url }}" class="h-24 w-24 rounded-full object-cover">
            @endif

            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $profile->full_name }}</h1>
                @if ($profile->headline)
                    <p class="mt-1 text-base text-gray-600">{{ $profile->headline }}</p>
                @endif

                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                    @if ($profile->email) <span>{{ $profile->email }}</span> @endif
                    @if ($profile->phone) <span>{{ $profile->phone }}</span> @endif
                    @if ($profile->address) <span>{{ $profile->address }}</span> @endif
                    @if ($profile->linkedin_url) <span>{{ $profile->linkedin_url }}</span> @endif
                </div>
            </div>
        </div>

        @if ($profile->summary)
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Profil</h2>
                <p class="mt-2 leading-6 text-gray-700">{{ $profile->summary }}</p>
            </div>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Expérience professionnelle</h2>
                <div class="mt-2 space-y-4">
                    @foreach ($profile->experiences as $exp)
                        <div>
                            <div class="flex items-baseline justify-between">
                                <p class="font-semibold text-gray-900">{{ $exp->position }} — {{ $exp->company }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}
                                </p>
                            </div>
                            @if ($exp->location)
                                <p class="text-xs text-gray-400">{{ $exp->location }}</p>
                            @endif
                            @if ($exp->description)
                                <p class="mt-1 text-gray-700">{{ $exp->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($profile->educations->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Formation</h2>
                <div class="mt-2 space-y-3">
                    @foreach ($profile->educations as $edu)
                        <div>
                            <div class="flex items-baseline justify-between">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }} — {{ $edu->institution }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}
                                </p>
                            </div>
                            @if ($edu->field_of_study)
                                <p class="text-xs text-gray-400">{{ $edu->field_of_study }}</p>
                            @endif
                            @if ($edu->description)
                                <p class="mt-1 text-gray-700">{{ $edu->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 grid grid-cols-2 gap-6">
            @if ($profile->skills->isNotEmpty())
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Compétences</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->skills as $skill)
                            <li>{{ $skill->name }} — <span class="text-xs text-gray-400">{{ $skill->level_label }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($profile->languages->isNotEmpty())
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Langues</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — <span class="text-xs text-gray-400">{{ $lang->level_label }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if ($profile->certifications->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Certifications</h2>
                <ul class="mt-2 space-y-1 text-gray-700">
                    @foreach ($profile->certifications as $cert)
                        <li>
                            {{ $cert->name }}
                            @if ($cert->issuer) — {{ $cert->issuer }} @endif
                            @if ($cert->date_obtained) <span class="text-xs text-gray-400">({{ $cert->date_obtained->format('Y') }})</span> @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection

'@
$dir22 = Split-Path $path22 -Parent
if (-not (Test-Path $dir22)) { New-Item -ItemType Directory -Path $dir22 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path22, $content22, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/classique.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/classique.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path23 = "C:\laragon\www\SEA\resources\views\student\cv\templates\moderne.blade.php"
$content23 = @'
@extends('layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle moderne')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 0mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto grid max-w-3xl grid-cols-3 overflow-hidden rounded-2xl border border-gray-200 bg-white text-sm print:mx-0 print:max-w-none print:rounded-none print:border-0 print:shadow-none">

        {{-- Colonne latérale --}}
        <div class="col-span-1 bg-indigo-600 p-6 text-white">
            @if ($profile->photo_url)
                <img src="{{ $profile->photo_url }}" class="h-24 w-24 rounded-full border-4 border-white/30 object-cover">
            @endif

            <h1 class="mt-4 text-lg font-extrabold leading-tight">{{ $profile->full_name }}</h1>
            @if ($profile->headline)
                <p class="mt-1 text-xs text-indigo-100">{{ $profile->headline }}</p>
            @endif

            <div class="mt-6 space-y-1 text-xs text-indigo-100">
                @if ($profile->email) <p>{{ $profile->email }}</p> @endif
                @if ($profile->phone) <p>{{ $profile->phone }}</p> @endif
                @if ($profile->address) <p>{{ $profile->address }}</p> @endif
                @if ($profile->linkedin_url) <p class="break-all">{{ $profile->linkedin_url }}</p> @endif
            </div>

            @if ($profile->skills->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-white">Compétences</h2>
                    <div class="mt-2 space-y-2">
                        @foreach ($profile->skills as $skill)
                            <div>
                                <p class="text-xs text-indigo-100">{{ $skill->name }}</p>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                                    <div class="h-full bg-white" style="width: {{ $skill->level_percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->languages->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-white">Langues</h2>
                    <ul class="mt-2 space-y-1 text-xs text-indigo-100">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — {{ $lang->level_label }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Colonne principale --}}
        <div class="col-span-2 p-6">
            @if ($profile->summary)
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Profil</h2>
                    <p class="mt-2 leading-6 text-gray-700">{{ $profile->summary }}</p>
                </div>
            @endif

            @if ($profile->experiences->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Expérience</h2>
                    <div class="mt-2 space-y-4">
                        @foreach ($profile->experiences as $exp)
                            <div class="border-l-2 border-indigo-100 pl-3">
                                <p class="font-semibold text-gray-900">{{ $exp->position }}</p>
                                <p class="text-xs text-gray-500">{{ $exp->company }} · {{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}</p>
                                @if ($exp->description)
                                    <p class="mt-1 text-gray-700">{{ $exp->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->educations->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Formation</h2>
                    <div class="mt-2 space-y-3">
                        @foreach ($profile->educations as $edu)
                            <div class="border-l-2 border-indigo-100 pl-3">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }}</p>
                                <p class="text-xs text-gray-500">{{ $edu->institution }} · {{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->certifications->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Certifications</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->certifications as $cert)
                            <li>{{ $cert->name }} @if ($cert->issuer) — {{ $cert->issuer }} @endif</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

'@
$dir23 = Split-Path $path23 -Parent
if (-not (Test-Path $dir23)) { New-Item -ItemType Directory -Path $dir23 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path23, $content23, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/moderne.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/moderne.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path24 = "C:\laragon\www\SEA\routes\cv.php"
$content24 = @'
<?php

use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\Student\CvController;
use Illuminate\Support\Facades\Route;

// --- Espace étudiant : CV builder ---
Route::middleware(['auth', 'verified'])
    ->prefix('cv')
    ->name('student.cv.')
    ->group(function () {
        Route::get('/', [CvController::class, 'edit'])->name('edit');
        Route::patch('/profile', [CvController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/public', [CvController::class, 'togglePublic'])->name('public.toggle');

        Route::post('/educations', [CvController::class, 'storeEducation'])->name('educations.store');
        Route::patch('/educations/{education}', [CvController::class, 'updateEducation'])->name('educations.update');
        Route::delete('/educations/{education}', [CvController::class, 'destroyEducation'])->name('educations.destroy');

        Route::post('/experiences', [CvController::class, 'storeExperience'])->name('experiences.store');
        Route::patch('/experiences/{experience}', [CvController::class, 'updateExperience'])->name('experiences.update');
        Route::delete('/experiences/{experience}', [CvController::class, 'destroyExperience'])->name('experiences.destroy');

        Route::post('/skills', [CvController::class, 'storeSkill'])->name('skills.store');
        Route::delete('/skills/{skill}', [CvController::class, 'destroySkill'])->name('skills.destroy');

        Route::post('/languages', [CvController::class, 'storeLanguage'])->name('languages.store');
        Route::delete('/languages/{language}', [CvController::class, 'destroyLanguage'])->name('languages.destroy');

        Route::post('/certifications', [CvController::class, 'storeCertification'])->name('certifications.store');
        Route::delete('/certifications/{certification}', [CvController::class, 'destroyCertification'])->name('certifications.destroy');

        Route::post('/projects', [CvController::class, 'storeProject'])->name('projects.store');
        Route::patch('/projects/{project}', [CvController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [CvController::class, 'destroyProject'])->name('projects.destroy');

        Route::get('/download/cv', [CvController::class, 'showCv'])->name('download.cv');
        Route::get('/download/ats', [CvController::class, 'showAts'])->name('download.ats');
    });

// --- Portfolio public (sans authentification) ---
Route::get('/portfolio/{slug}', [PublicPortfolioController::class, 'show'])->name('portfolio.show');

'@
$dir24 = Split-Path $path24 -Parent
if (-not (Test-Path $dir24)) { New-Item -ItemType Directory -Path $dir24 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path24, $content24, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/cv.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/cv.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path25 = "C:\laragon\www\SEA\routes\web.php"
$content25 = @'
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        if ($request->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('student.dashboard');
    })->name('dashboard');

    Route::get('/student/dashboard', function () {
        $activeModules = DB::table('modules')
            ->where('is_active', true)
            ->orderBy('menu_order')
            ->get();

        return view('student.dashboard', [
            'activeModules' => $activeModules,
        ]);
    })->name('student.dashboard');

    Route::get('/admin/dashboard', function (Request $request) {
        abort_unless(
            $request->user()->hasRole('admin'),
            403,
            'Accès réservé aux administrateurs.'
        );

        return view('admin.dashboard', [
            'usersCount' => DB::table('users')->count(),
            'activeModulesCount' => DB::table('modules')
                ->where('is_active', true)
                ->count(),
            'rolesCount' => DB::table('roles')->count(),
            'activityLogsCount' => DB::table('activity_logs')->count(),
        ]);
    })->name('admin.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/centre.php';
require __DIR__.'/registration.php';
require __DIR__.'/training.php';
require __DIR__.'/cv.php';
'@
$dir25 = Split-Path $path25 -Parent
if (-not (Test-Path $dir25)) { New-Item -ItemType Directory -Path $dir25 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path25, $content25, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/web.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/web.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
