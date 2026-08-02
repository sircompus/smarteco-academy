<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_profile_id', 'name', 'issuer', 'date_obtained', 'credential_url', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['date_obtained' => 'date'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }
}
