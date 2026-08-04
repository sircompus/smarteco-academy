<?php

namespace App\Models;

use Database\Factories\CommunityCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CommunityComment extends Model
{
    /** @use HasFactory<CommunityCommentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'community_post_id',
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
        static::creating(function (CommunityComment $comment): void {
            if (empty($comment->uuid)) {
                $comment->uuid = (string) Str::uuid();
            }
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(
            CommunityPost::class,
            'community_post_id'
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }
}
