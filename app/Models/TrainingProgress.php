<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingProgress extends Model
{
    use HasFactory;

    protected $table = 'training_progress';

    protected $fillable = [
        'training_enrollment_id',
        'training_lesson_id',
        'status',
        'progress_percentage',
        'started_at',
        'last_accessed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'decimal:2',
            'started_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(
            TrainingEnrollment::class,
            'training_enrollment_id'
        );
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(
            TrainingLesson::class,
            'training_lesson_id'
        );
    }
}