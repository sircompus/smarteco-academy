<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Module extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'version',
        'icon',
        'route_prefix',
        'is_active',
        'is_core',
        'menu_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_core' => 'boolean',
            'menu_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Module $module): void {
            if (empty($module->uuid)) {
                $module->uuid = (string) Str::uuid();
            }
        });
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ModuleSetting::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('menu_order');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}