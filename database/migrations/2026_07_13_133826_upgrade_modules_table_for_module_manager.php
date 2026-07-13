<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->uuid('uuid')
                ->nullable()
                ->after('id');

            $table->string('route_prefix')
                ->nullable()
                ->after('icon');

            $table->softDeletes();
        });

        // Attribuer un UUID aux éventuels modules déjà présents.
        DB::table('modules')
            ->whereNull('uuid')
            ->orderBy('id')
            ->get()
            ->each(function ($module): void {
                DB::table('modules')
                    ->where('id', $module->id)
                    ->update([
                        'uuid' => (string) Str::uuid(),
                    ]);
            });

        Schema::table('modules', function (Blueprint $table) {
            $table->uuid('uuid')
                ->nullable(false)
                ->change();

            $table->unique('uuid');

            $table->renameColumn('is_enabled', 'is_active');
            $table->renameColumn('sort_order', 'menu_order');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->renameColumn('is_active', 'is_enabled');
            $table->renameColumn('menu_order', 'sort_order');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid',
                'route_prefix',
            ]);
        });
    }
};