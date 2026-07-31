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
        'amount_due',
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
            'amount_due' => 'decimal:2',
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

    public function payments(): HasMany
    {
        return $this->hasMany(TrainingPayment::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(TrainingPaymentReminder::class);
    }

    public function lastReminder(): ?TrainingPaymentReminder
    {
        return $this->reminders()->latest('sent_at')->first();
    }

    public function requiresPayment(): bool
    {
        if ($this->session?->isMonthly()) {
            return (float) ($this->session->price ?? 0) > 0;
        }

        return $this->amount_due !== null && (float) $this->amount_due > 0;
    }

    public function monthsElapsed(): int
    {
        $start = $this->enrolled_at ?? $this->created_at;

        return max(1, (int) $start->diffInMonths(now()) + 1);
    }

    public function getCurrentAmountDueAttribute(): float
    {
        if ($this->session?->isMonthly()) {
            return round((float) ($this->session->price ?? 0) * $this->monthsElapsed(), 2);
        }

        return (float) ($this->amount_due ?? 0);
    }

    public function getAmountPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getAmountRemainingAttribute(): float
    {
        if (! $this->requiresPayment()) {
            return 0.0;
        }

        return max(0, $this->current_amount_due - $this->amount_paid);
    }

    public function isFullyPaid(): bool
    {
        return ! $this->requiresPayment() || $this->amount_remaining <= 0;
    }
}