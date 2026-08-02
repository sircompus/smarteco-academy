<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('pack_enrollment_id')
                ->constrained('pack_enrollments')
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
        Schema::dropIfExists('pack_payment_reminders');
    }
};
