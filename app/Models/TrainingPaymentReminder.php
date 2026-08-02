<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingPaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'training_enrollment_id',
        'sent_by',
        'amount_remaining_at_time',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_remaining_at_time' => 'decimal:2',
            'sent_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'training_enrollment_id');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
