<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingLesson extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'training_id',
        'training_section_id',
        'title',
        'slug',
        'content',
        'video_url',
        'duration_minutes',
        'is_preview',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'is_preview' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TrainingLesson $lesson): void {
            if (empty($lesson->uuid)) {
                $lesson->uuid = (string) Str::uuid();
            }
        });
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(
            TrainingSection::class,
            'training_section_id'
        );
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(
            TrainingProgress::class,
            'training_lesson_id'
        );
    }
}