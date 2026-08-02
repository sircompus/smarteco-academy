<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_watch_id')
                ->constrained('job_watches')
                ->cascadeOnDelete();

            $table->foreignId('job_offer_id')
                ->constrained('job_offers')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('score')->default(0);

            $table->unsignedTinyInteger('skill_score')->nullable();
            $table->unsignedTinyInteger('title_score')->nullable();
            $table->unsignedTinyInteger('experience_score')->nullable();
            $table->unsignedTinyInteger('portfolio_score')->nullable();
            $table->unsignedTinyInteger('location_score')->nullable();
            $table->unsignedTinyInteger('contract_score')->nullable();
            $table->unsignedTinyInteger('language_score')->nullable();

            $table->json('score_details')->nullable();
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();

            /*
             * new, notified, viewed, saved,
             * dismissed, applied ou expired
             */
            $table->string('status', 20)->default('new');

            $table->timestamp('notified_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamp('applied_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['job_watch_id', 'job_offer_id'],
                'job_watch_offer_unique'
            );

            $table->index(['job_watch_id', 'status']);
            $table->index(['score', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};
