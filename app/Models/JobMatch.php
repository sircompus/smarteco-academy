<?php

namespace App\Models;

use Database\Factories\JobMatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMatch extends Model
{
    /** @use HasFactory<JobMatchFactory> */
    use HasFactory;

    protected $fillable = [
        'job_watch_id',
        'job_offer_id',
        'score',
        'skill_score',
        'title_score',
        'experience_score',
        'portfolio_score',
        'location_score',
        'contract_score',
        'language_score',
        'score_details',
        'matched_skills',
        'missing_skills',
        'status',
        'notified_at',
        'viewed_at',
        'saved_at',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'skill_score' => 'integer',
            'title_score' => 'integer',
            'experience_score' => 'integer',
            'portfolio_score' => 'integer',
            'location_score' => 'integer',
            'contract_score' => 'integer',
            'language_score' => 'integer',
            'score_details' => 'array',
            'matched_skills' => 'array',
            'missing_skills' => 'array',
            'notified_at' => 'datetime',
            'viewed_at' => 'datetime',
            'saved_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public function jobWatch(): BelongsTo
    {
        return $this->belongsTo(JobWatch::class);
    }

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }
}
