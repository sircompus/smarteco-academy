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
