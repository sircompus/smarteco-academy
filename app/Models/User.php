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