<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_resources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->enum('type', ['cours', 'td', 'examen', 'resume']);

            $table->string('professor_name')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            // Qui a mis le fichier en ligne (compte du site) — distinct du nom
            // du prof de la fac, qui lui est juste une étiquette libre.
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['subject_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_resources');
    }
};
