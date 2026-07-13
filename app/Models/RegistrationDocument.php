<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RegistrationDocument extends Model
{
    use HasFactory;

    public const TYPES = [
        'identity' => 'Pièce d’identité',
        'diploma' => 'Diplôme',
        'transcript' => 'Relevé de notes',
        'photo' => 'Photo',
        'other' => 'Autre document',
    ];

    protected $fillable = [
        'uuid',
        'registration_id',
        'uploaded_by',
        'type',
        'title',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'is_verified',
        'verified_at',
        'verified_by',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RegistrationDocument $document): void {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}