<?php

namespace Database\Factories;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityComment>
 */
class CommunityCommentFactory extends Factory
{
    protected $model = CommunityComment::class;

    public function definition(): array
    {
        return [
            'community_post_id' => CommunityPost::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
            'status' => 'published',
            'hidden_by' => null,
            'hidden_at' => null,
            'moderation_note' => null,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => [
            'status' => 'hidden',
            'hidden_at' => now(),
        ]);
    }
}
