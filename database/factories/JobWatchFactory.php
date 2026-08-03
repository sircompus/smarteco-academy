<?php

namespace Database\Factories;

use App\Models\JobWatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobWatch>
 */
class JobWatchFactory extends Factory
{
    protected $model = JobWatch::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cv_profile_id' => null,
            'name' => fake()->words(3, true),
            'source_mode' => fake()->randomElement([
                'cv',
                'portfolio',
                'both',
            ]),
            'target_titles' => fake()->randomElements([
                'Développeur Laravel',
                'Développeur PHP',
                'Data analyst',
                'Administrateur système',
            ], 2),
            'preferred_locations' => [
                fake()->city(),
            ],
            'contract_types' => fake()->randomElements([
                'cdi',
                'cdd',
                'stage',
                'freelance',
            ], 2),
            'remote_mode' => fake()->randomElement([
                'onsite',
                'hybrid',
                'remote',
            ]),
            'minimum_score' => 70,
            'frequency_minutes' => 1440,
            'status' => 'active',
            'last_run_at' => null,
            'next_run_at' => now()->addDay(),
        ];
    }
}
