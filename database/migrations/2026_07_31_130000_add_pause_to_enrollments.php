<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pack_enrollments', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('activated_at');
            $table->unsignedInteger('paused_days')->default(0)->after('paused_at');
        });

        Schema::table('training_enrollments', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('amount_due');
            $table->unsignedInteger('paused_days')->default(0)->after('paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('pack_enrollments', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'paused_days']);
        });

        Schema::table('training_enrollments', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'paused_days']);
        });
    }
};
