<?php

namespace App\Models;

use Database\Factories\CommunityPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CommunityPost extends Model
{
    /** @use HasFactory<CommunityPostFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'body',
        'status',
        'hidden_by',
        'hidden_at',
        'moderation_note',
    ];

    protected function casts(): array
    {
        return [
            'hidden_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CommunityPost $post): void {
            if (empty($post->uuid)) {
                $post->uuid = (string) Str::uuid();
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommunityLike::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
