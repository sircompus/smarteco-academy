<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->nullableMorphs('subject');

    $table->string('action', 100);
    $table->string('module')->nullable();
    $table->text('description')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->ipAddress('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();

    $table->index(['action', 'created_at']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
