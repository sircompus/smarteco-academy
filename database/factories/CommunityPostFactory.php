<?php

namespace Database\Factories;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPost>
 */
class CommunityPostFactory extends Factory
{
    protected $model = CommunityPost::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
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
