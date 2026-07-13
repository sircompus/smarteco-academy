<?php

namespace App\Core\Modules;

use App\Models\Module;
use Illuminate\Database\Eloquent\Collection;

class ModuleManager
{
    public function all(): Collection
    {
        return Module::query()
            ->ordered()
            ->get();
    }

    public function active(): Collection
    {
        return Module::query()
            ->active()
            ->ordered()
            ->get();
    }

    public function isActive(string $slug): bool
    {
        return Module::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();
    }

    public function activate(string $slug): Module
    {
        $module = Module::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $module->update([
            'is_active' => true,
        ]);

        return $module->refresh();
    }

    public function deactivate(string $slug): Module
    {
        $module = Module::query()
            ->where('slug', $slug)
            ->firstOrFail();

        if ($module->is_core) {
            throw new \RuntimeException(
                'Un module central ne peut pas être désactivé.'
            );
        }

        $module->update([
            'is_active' => false,
        ]);

        return $module->refresh();
    }
}