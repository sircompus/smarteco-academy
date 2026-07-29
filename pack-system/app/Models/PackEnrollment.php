<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'status',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
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

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
