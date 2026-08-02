<?php

namespace App\Models;

use Database\Factories\JobWatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JobWatch extends Model
{
    /** @use HasFactory<JobWatchFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'cv_profile_id',
        'name',
        'source_mode',
        'target_titles',
        'preferred_locations',
        'contract_types',
        'remote_mode',
        'minimum_score',
        'frequency_minutes',
        'status',
        'last_run_at',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'target_titles' => 'array',
            'preferred_locations' => 'array',
            'contract_types' => 'array',
            'minimum_score' => 'integer',
            'frequency_minutes' => 'integer',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JobWatch $jobWatch): void {
            if (blank($jobWatch->uuid)) {
                $jobWatch->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cvProfile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(JobWatchKeyword::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(JobMatch::class);
    }
}
