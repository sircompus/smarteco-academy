<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_comments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('community_post_id')
                ->constrained('community_posts')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->text('body');
            $table->string('status', 30)
                ->default('published')
                ->index();
            $table->foreignId('hidden_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('hidden_at')->nullable();
            $table->text('moderation_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'community_post_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_comments');
    }
};
