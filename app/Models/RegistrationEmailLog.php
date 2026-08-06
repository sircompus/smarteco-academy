<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationEmailLog extends Model
{
    protected $fillable = [
        'registration_id',
        'status_history_id',
        'event_key',
        'email_type',
        'status',
        'recipient',
        'sent_at',
        'failed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function statusHistory(): BelongsTo
    {
        return $this->belongsTo(
            RegistrationStatusHistory::class,
            'status_history_id'
        );
    }
}