<?php

namespace App\Models;

use Database\Factories\JobOfferSkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOfferSkill extends Model
{
    /** @use HasFactory<JobOfferSkillFactory> */
    use HasFactory;

    protected $fillable = [
        'job_offer_id',
        'name',
        'normalized_name',
        'importance',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'importance' => 'integer',
            'is_required' => 'boolean',
        ];
    }

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }
}
