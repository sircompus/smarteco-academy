<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('avatar_path')->nullable();
            $table->text('bio')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};