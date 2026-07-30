<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'pack_enrollment_id',
        'recorded_by',
        'amount',
        'paid_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(PackEnrollment::class, 'pack_enrollment_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
