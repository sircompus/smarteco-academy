<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('location');
            $table->enum('billing_type', ['unique', 'mensuel'])
                ->default('unique')
                ->after('price');
        });

        Schema::table('training_enrollments', function (Blueprint $table) {
            $table->decimal('amount_due', 10, 2)->nullable()->after('status');
        });

        Schema::create('training_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('training_enrollment_id')
                ->constrained('training_enrollments')
                ->cascadeOnDelete();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('amount', 10, 2);
            $table->date('paid_at');
            $table->string('note')->nullable();

            $table->timestamps();
        });

        Schema::create('training_payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('training_enrollment_id')
                ->constrained('training_enrollments')
                ->cascadeOnDelete();

            $table->foreignId('sent_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('amount_remaining_at_time', 10, 2);
            $table->timestamp('sent_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_payment_reminders');
        Schema::dropIfExists('training_payments');

        Schema::table('training_enrollments', function (Blueprint $table) {
            $table->dropColumn('amount_due');
        });

        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn(['price', 'billing_type']);
        });
    }
};
