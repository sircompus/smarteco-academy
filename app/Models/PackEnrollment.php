<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackEnrollment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'en_attente' => 'En attente de validation',
        'active' => 'Active',
        'annulee' => 'Annulée',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'pack_id',
        'amount_due',
        'status',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'amount_due' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PackPayment::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(PackPaymentReminder::class);
    }

    public function lastReminder(): ?PackPaymentReminder
    {
        return $this->reminders()->latest('sent_at')->first();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * true si un paiement est réellement attendu (pack payant).
     * false si le pack est gratuit ("forfaitaire") — aucun suivi de paiement nécessaire.
     */
    public function requiresPayment(): bool
    {
        if ($this->pack?->isMonthly()) {
            return (float) ($this->pack->price ?? 0) > 0;
        }

        return $this->amount_due !== null && (float) $this->amount_due > 0;
    }

    /**
     * Nombre de mois "dus" depuis le début de l'inscription (mois en cours inclus).
     * Ex : inscrit le 5 juillet, on est le 20 août -> 2 mois dus (juillet + août).
     */
    public function monthsElapsed(): int
    {
        $start = $this->activated_at ?? $this->created_at;

        return max(1, (int) $start->diffInMonths(now()) + 1);
    }

    /**
     * Montant total dû à date : fixe pour un pack "unique",
     * cumulé (prix mensuel × mois écoulés) pour un pack "mensuel".
     */
    public function getCurrentAmountDueAttribute(): float
    {
        if ($this->pack?->isMonthly()) {
            return round((float) ($this->pack->price ?? 0) * $this->monthsElapsed(), 2);
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
