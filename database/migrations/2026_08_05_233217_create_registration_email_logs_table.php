<?php

use App\Models\Registration;
use App\Models\RegistrationStatusHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_email_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Registration::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(
                RegistrationStatusHistory::class,
                'status_history_id'
            )
                ->nullable()
                ->constrained('registration_status_history')
                ->nullOnDelete();

            $table->string('event_key', 120);
            $table->string('email_type', 50);
            $table->string('status', 50)->nullable();
            $table->string('recipient');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->unique(
                ['registration_id', 'event_key'],
                'registration_email_event_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_email_logs');
    }
};
