<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TrainingEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'training_id',
        'training_session_id',
        'user_id',
        'status',
        'progress_percentage',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'decimal:2',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TrainingEnrollment $enrollment): void {
            if (empty($enrollment->uuid)) {
                $enrollment->uuid = (string) Str::uuid();
            }
        });
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            TrainingSession::class,
            'training_session_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(
            TrainingProgress::class,
            'training_enrollment_id'
        );
    }
}