<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'semester_id',
        'name',
        'slug',
        'code',
        'description',
        'credits',
        'coefficient',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'decimal:2',
            'coefficient' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}