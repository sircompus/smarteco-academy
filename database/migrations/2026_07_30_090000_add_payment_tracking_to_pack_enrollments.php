<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pack_enrollments', function (Blueprint $table) {
            $table->decimal('amount_due', 10, 2)->nullable()->after('pack_id');
        });

        Schema::create('pack_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('pack_enrollment_id')
                ->constrained('pack_enrollments')
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
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_payments');

        Schema::table('pack_enrollments', function (Blueprint $table) {
            $table->dropColumn('amount_due');
        });
    }
};
