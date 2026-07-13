<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference')->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('academic_level_id')
                ->constrained('academic_levels')
                ->restrictOnDelete();

            $table->foreignId('academic_program_id')
                ->constrained('academic_programs')
                ->restrictOnDelete();

            $table->string('academic_year', 20);
            $table->string('status', 30)->default('draft');

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();

            $table->text('student_note')->nullable();
            $table->text('decision_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                [
                    'user_id',
                    'academic_program_id',
                    'academic_year',
                ],
                'registrations_unique_application'
            );

            $table->index(['status', 'academic_year']);
        });

        Schema::create('registration_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('type', 50);
            $table->string('title')->nullable();
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->unique([
                'registration_id',
                'type',
            ]);
        });

        Schema::create('registration_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();

            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index([
                'registration_id',
                'to_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_status_history');
        Schema::dropIfExists('registration_documents');
        Schema::dropIfExists('registrations');
    }
};