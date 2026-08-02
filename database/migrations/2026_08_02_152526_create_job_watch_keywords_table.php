<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_watch_keywords', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_watch_id')
                ->constrained('job_watches')
                ->cascadeOnDelete();

            $table->string('keyword');
            $table->string('normalized_keyword');

            // include, exclude, title, skill, company ou sector
            $table->string('type', 20)->default('include');

            $table->unsignedSmallInteger('weight')->default(1);

            $table->timestamps();

            $table->unique(
                ['job_watch_id', 'normalized_keyword', 'type'],
                'job_watch_keyword_type_unique'
            );

            $table->index(['job_watch_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_watch_keywords');
    }
};