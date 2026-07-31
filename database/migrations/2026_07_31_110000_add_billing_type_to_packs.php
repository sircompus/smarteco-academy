<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packs', function (Blueprint $table) {
            $table->enum('billing_type', ['unique', 'mensuel'])
                ->default('unique')
                ->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('packs', function (Blueprint $table) {
            $table->dropColumn('billing_type');
        });
    }
};
