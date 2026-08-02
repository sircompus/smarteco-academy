<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offer_skills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_offer_id')
                ->constrained('job_offers')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('normalized_name');

            /*
             * 1 = facultative
             * 2 = souhaitée
             * 3 = importante
             */
            $table->unsignedTinyInteger('importance')->default(1);

            $table->boolean('is_required')->default(false);

            $table->timestamps();

            $table->unique(
                ['job_offer_id', 'normalized_name'],
                'job_offer_skill_unique'
            );

            $table->index(['normalized_name', 'is_required']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offer_skills');
    }
};