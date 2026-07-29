<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->enum('type', ['semestre', 'module']);

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pack_enrollments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('pack_id')
                ->constrained('packs')
                ->cascadeOnDelete();

            $table->enum('status', ['en_attente', 'active', 'annulee'])
                ->default('en_attente');

            $table->timestamp('activated_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['user_id', 'pack_id'],
                'pack_enrollments_user_pack_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_enrollments');
        Schema::dropIfExists('packs');
    }
};
