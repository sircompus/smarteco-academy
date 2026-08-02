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
        'paused_at',
        'paused_days',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'amount_due' => 'decimal:2',
            'paused_at' => 'datetime',
            'paused_days' => 'integer',
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

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    public function pause(): void
    {
        if (! $this->isPaused()) {
            $this->update(['paused_at' => now()]);
        }
    }

    public function resume(): void
    {
        if ($this->isPaused()) {
            $this->update([
                'paused_days' => $this->paused_days + $this->paused_at->diffInDays(now()),
                'paused_at' => null,
            ]);
        }
    }

    /**
     * Nombre de mois "dus" depuis le début de l'inscription (mois en cours inclus),
     * en excluant le temps passé en pause (vacances, interruption...).
     */
    public function monthsElapsed(): int
    {
        $start = $this->activated_at ?? $this->created_at;

        $totalPausedDays = $this->paused_days
            + ($this->isPaused() ? $this->paused_at->diffInDays(now()) : 0);

        $effectiveNow = now()->subDays($totalPausedDays);

        if ($effectiveNow->lessThan($start)) {
            return 1;
        }

        return max(1, (int) $start->diffInMonths($effectiveNow) + 1);
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

    /**
     * Niveau académique auquel ce pack appartient (via son semestre ou sa matière).
     */
    public function packLevel(): ?AcademicLevel
    {
        if ($this->pack?->isTypeSemestre()) {
            return $this->pack->semester?->program?->level;
        }

        return $this->pack?->subject?->semester?->program?->level;
    }

    /**
     * Message d'avertissement si le niveau du pack ne correspond pas au
     * niveau du dossier d'admission ACCEPTÉ de l'étudiant (ou s'il n'y en
     * a aucun). Retourne null si tout concorde.
     */
    public function levelMismatchWarning(): ?string
    {
        $packLevel = $this->packLevel();

        if (! $packLevel) {
            return null;
        }

        $acceptedRegistration = Registration::query()
            ->where('user_id', $this->user_id)
            ->where('status', Registration::STATUS_ACCEPTED)
            ->latest()
            ->first();

        if (! $acceptedRegistration) {
            return "Aucun dossier d'admission accepté trouvé pour cet étudiant — vérifie sa situation avant de valider.";
        }

        if ($acceptedRegistration->academic_level_id !== $packLevel->id) {
            return "Le dossier d'admission accepté est au niveau « {$acceptedRegistration->level?->name} », mais ce pack correspond au niveau « {$packLevel->name} ».";
        }

        return null;
    }
}
