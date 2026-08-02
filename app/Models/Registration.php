<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Brouillon',
        self::STATUS_SUBMITTED => 'Soumise',
        self::STATUS_UNDER_REVIEW => 'En cours d’examen',
        self::STATUS_INCOMPLETE => 'Dossier incomplet',
        self::STATUS_ACCEPTED => 'Acceptée',
        self::STATUS_REJECTED => 'Refusée',
        self::STATUS_SUSPENDED => 'Suspendue',
    ];

    protected $fillable = [
        'uuid',
        'reference',
        'user_id',
        'academic_level_id',
        'academic_program_id',
        'academic_year',
        'status',
        'first_name',
        'last_name',
        'phone',
        'birth_date',
        'gender',
        'address',
        'city',
        'country',
        'student_note',
        'decision_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Registration $registration): void {
            if (empty($registration->uuid)) {
                $registration->uuid = (string) Str::uuid();
            }

            if (empty($registration->reference)) {
                $registration->reference =
                    'REG-' .
                    now()->format('Y') .
                    '-' .
                    Str::upper(Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(
            AcademicLevel::class,
            'academic_level_id'
        );
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(
            AcademicProgram::class,
            'academic_program_id'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RegistrationDocument::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RegistrationStatusHistory::class)
            ->latest();
    }

    public function canBeEdited(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_INCOMPLETE,
            ],
            true
        );
    }

    public function canBeSubmitted(): bool
    {
        return $this->canBeEdited();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}