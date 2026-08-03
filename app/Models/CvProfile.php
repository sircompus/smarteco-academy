<?php

namespace App\Models;

use App\Services\CvSummaryGeneratorService;
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
        'show_in_navigation',
        'public_slug',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'show_in_navigation' => 'boolean',
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

    /**
     * Résumé à afficher : celui saisi par l'étudiant s'il existe,
     * sinon un résumé généré automatiquement à partir de son profil
     * (jamais enregistré en base — recalculé à chaque affichage).
     */
    public function getEffectiveSummaryAttribute(): string
    {
        if (filled($this->summary)) {
            return $this->summary;
        }

        return app(CvSummaryGeneratorService::class)->generate($this);
    }

    public function jobWatches(): HasMany
    {
        return $this->hasMany(JobWatch::class);
    }
}
