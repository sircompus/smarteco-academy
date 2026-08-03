<?php

namespace App\Models;

use Database\Factories\JobWatchKeywordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobWatchKeyword extends Model
{
    /** @use HasFactory<JobWatchKeywordFactory> */
    use HasFactory;

    protected $fillable = [
        'job_watch_id',
        'keyword',
        'normalized_keyword',
        'type',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }

    public function jobWatch(): BelongsTo
    {
        return $this->belongsTo(JobWatch::class);
    }
}
