<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_sources', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            // manual, api, rss ou partner_feed
            $table->string('driver', 30)->default('manual');

            $table->text('base_url')->nullable();
            $table->boolean('is_active')->default(true);

            /*
             * Configuration non sensible uniquement.
             * Les clés API devront rester dans .env.
             */
            $table->json('configuration')->nullable();

            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'driver']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_sources');
    }
};
