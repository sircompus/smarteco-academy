<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingSession extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'training_id',
        'trainer_id',
        'title',
        'code',
        'status',
        'registration_starts_at',
        'registration_ends_at',
        'starts_at',
        'ends_at',
        'capacity',
        'location',
        'meeting_url',
        'price',
        'billing_type',
    ];

    protected function casts(): array
    {
        return [
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function isMonthly(): bool
    {
        return $this->billing_type === 'mensuel';
    }

    protected static function booted(): void
    {
        static::creating(function (TrainingSession $session): void {
            if (empty($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'trainer_id'
        );
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class);
    }

    public function isOpenForEnrollment(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if (
            $this->registration_starts_at
            && now()->isBefore($this->registration_starts_at)
        ) {
            return false;
        }

        if (
            $this->registration_ends_at
            && now()->isAfter($this->registration_ends_at)
        ) {
            return false;
        }

        if (
            $this->capacity !== null
            && $this->enrollments()
                ->where('status', 'active')
                ->count() >= $this->capacity
        ) {
            return false;
        }

        return true;
    }
}