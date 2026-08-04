<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $existingModule = DB::table('modules')
            ->where('slug', 'community')
            ->first();

        $attributes = [
            'name' => 'Community',
            'description' => (
                'Espace communautaire destiné aux étudiants et formateurs.'
            ),
            'version' => '1.0.0',
            'icon' => null,
            'route_prefix' => 'community',
            'is_active' => true,
            'is_core' => false,
            'menu_order' => 10,
            'deleted_at' => null,
            'updated_at' => now(),
        ];

        if ($existingModule !== null) {
            if (empty($existingModule->uuid)) {
                $attributes['uuid'] = (string) Str::uuid();
            }

            DB::table('modules')
                ->where('id', $existingModule->id)
                ->update($attributes);

            return;
        }

        DB::table('modules')->insert([
            'uuid' => (string) Str::uuid(),
            'slug' => 'community',
            'created_at' => now(),
            ...$attributes,
        ]);
    }

    public function down(): void
    {
        DB::table('modules')
            ->where('slug', 'community')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
};
