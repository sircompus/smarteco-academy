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
    Schema::create('module_settings', function (Blueprint $table) {
    $table->id();

    $table->foreignId('module_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('key');
    $table->longText('value')->nullable();
    $table->string('type', 30)->default('string');
    $table->boolean('is_public')->default(false);
    $table->timestamps();

    $table->unique(['module_id', 'key']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_settings');
    }
};
