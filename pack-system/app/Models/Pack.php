<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Pack extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'semester_id',
        'subject_id',
        'name',
        'description',
        'price',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(PackEnrollment::class);
    }

    /**
     * Les matières réellement incluses dans ce pack :
     * toutes les matières du semestre si type "semestre",
     * la matière unique si type "module".
     */
    public function subjects(): Collection
    {
        if ($this->type === 'semestre') {
            return $this->semester?->subjects()->orderBy('sort_order')->get()
                ?? collect();
        }

        return $this->subject ? collect([$this->subject]) : collect();
    }

    public function isTypeSemestre(): bool
    {
        return $this->type === 'semestre';
    }
}
