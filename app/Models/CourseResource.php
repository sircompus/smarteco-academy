<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CourseResource extends Model
{
    use HasFactory;

    public const TYPES = [
        'cours' => 'Cours',
        'td' => 'TD',
        'exercice' => 'Exercices',
        'resume' => 'Résumés',
    ];

    protected $fillable = [
        'uuid',
        'course_id',
        'uploaded_by',
        'type',
        'title',
        'description',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getSizeForHumansAttribute(): string
    {
        $size = $this->size;

        if ($size < 1024) {
            return $size.' o';
        }

        if ($size < 1024 * 1024) {
            return round($size / 1024, 1).' Ko';
        }

        return round($size / (1024 * 1024), 1).' Mo';
    }
}
