<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail_path')->nullable();

            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'published_at',
            ]);
        });

        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnDelete();

            $table->foreignId('trainer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->string('code', 50)->nullable();
            $table->string('status', 30)->default('open');

            $table->timestamp('registration_starts_at')->nullable();
            $table->timestamp('registration_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();

            $table->unsignedInteger('capacity')->nullable();
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'training_id',
                'status',
            ]);
        });

        Schema::create('training_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_lessons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnDelete();

            $table->foreignId('training_section_id')
                ->nullable()
                ->constrained('training_sections')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'training_id',
                'is_published',
            ]);
        });

        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnDelete();

            $table->foreignId('training_session_id')
                ->constrained('training_sessions')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status', 30)->default('active');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'training_session_id',
                'user_id',
            ]);

            $table->index([
                'training_id',
                'status',
            ]);
        });

        Schema::create('training_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_enrollment_id')
                ->constrained('training_enrollments')
                ->cascadeOnDelete();

            $table->foreignId('training_lesson_id')
                ->constrained('training_lessons')
                ->cascadeOnDelete();

            $table->string('status', 30)->default('not_started');
            $table->decimal('progress_percentage', 5, 2)->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'training_enrollment_id',
                'training_lesson_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_progress');
        Schema::dropIfExists('training_enrollments');
        Schema::dropIfExists('training_lessons');
        Schema::dropIfExists('training_sections');
        Schema::dropIfExists('training_sessions');
        Schema::dropIfExists('trainings');
    }
};