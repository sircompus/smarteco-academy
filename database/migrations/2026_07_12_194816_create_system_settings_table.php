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
     Schema::create('system_settings', function (Blueprint $table) {
    $table->id();
    $table->string('group')->default('general');
    $table->string('key');
    $table->longText('value')->nullable();
    $table->string('type', 30)->default('string');
    $table->boolean('is_public')->default(false);
    $table->text('description')->nullable();
    $table->timestamps();

    $table->unique(['group', 'key']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
