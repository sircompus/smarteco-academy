<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_source_id')
                ->constrained('job_sources')
                ->onDelete('restrict');

            $table->string('external_id', 191)->nullable();

            $table->string('title');
            $table->string('normalized_title');

            $table->string('company')->nullable();
            $table->string('normalized_company')->nullable();

            $table->string('location')->nullable();
            $table->char('country_code', 2)->nullable();

            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();

            $table->string('contract_type', 50)->nullable();
            $table->string('remote_mode', 30)->nullable();
            $table->string('experience_level', 50)->nullable();

            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->char('salary_currency', 3)->nullable();

            $table->text('url')->nullable();
            $table->text('canonical_url')->nullable();

            /*
             * Empreinte SHA-256 utilisée pour empêcher les doublons.
             */
            $table->char('fingerprint', 64)->unique();

            $table->json('raw_payload')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            // active, expired, removed ou archived
            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->unique(
                ['job_source_id', 'external_id'],
                'job_source_external_id_unique'
            );

            $table->index('normalized_title');
            $table->index('normalized_company');
            $table->index('published_at');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};
