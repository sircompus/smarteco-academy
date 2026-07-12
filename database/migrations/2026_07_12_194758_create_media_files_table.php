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
      Schema::create('media_files', function (Blueprint $table) {
    $table->id();

    $table->foreignId('uploaded_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->nullableMorphs('fileable');

    $table->string('disk')->default('public');
    $table->string('directory')->nullable();
    $table->string('filename');
    $table->string('original_name');
    $table->string('path');
    $table->string('mime_type')->nullable();
    $table->string('extension', 20)->nullable();
    $table->unsignedBigInteger('size')->default(0);
    $table->string('title')->nullable();
    $table->text('description')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
