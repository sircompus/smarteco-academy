<?php

namespace Database\Factories;

use App\Models\JobSource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobSource>
 */
class JobSourceFactory extends Factory
{
    protected $model = JobSource::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 999999),
            'driver' => fake()->randomElement([
                'manual',
                'api',
                'rss',
                'partner_feed',
            ]),
            'base_url' => fake()->url(),
            'is_active' => true,
            'configuration' => [],
            'last_success_at' => null,
            'last_error_at' => null,
            'last_error' => null,
        ];
    }
}
