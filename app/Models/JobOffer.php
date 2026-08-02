<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOffer extends Model
{
    /** @use HasFactory<\Database\Factories\JobOfferFactory> */
    use HasFactory;

    protected $fillable = [
        'job_source_id',
        'external_id',
        'title',
        'normalized_title',
        'company',
        'normalized_company',
        'location',
        'country_code',
        'description',
        'requirements',
        'contract_type',
        'remote_mode',
        'experience_level',
        'salary_min',
        'salary_max',
        'salary_currency',
        'url',
        'canonical_url',
        'fingerprint',
        'raw_payload',
        'published_at',
        'expires_at',
        'first_seen_at',
        'last_seen_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'raw_payload' => 'array',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(JobSource::class, 'job_source_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(JobOfferSkill::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(JobMatch::class);
    }
}