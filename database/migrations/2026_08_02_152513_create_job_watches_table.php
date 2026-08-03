<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_watches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('cv_profile_id')
                ->nullable()
                ->constrained('cv_profiles')
                ->nullOnDelete();

            $table->string('name');

            // cv, portfolio ou both
            $table->string('source_mode', 20)->default('both');

            $table->json('target_titles')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->json('contract_types')->nullable();

            $table->string('remote_mode', 30)->nullable();
            $table->unsignedTinyInteger('minimum_score')->default(70);
            $table->unsignedInteger('frequency_minutes')->default(1440);

            // active, paused ou disabled
            $table->string('status', 20)->default('active');

            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('cv_profile_id');
            $table->index('next_run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_watches');
    }
};
