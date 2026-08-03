<?php

namespace Database\Factories;

use App\Models\JobMatch;
use App\Models\JobOffer;
use App\Models\JobWatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobMatch>
 */
class JobMatchFactory extends Factory
{
    protected $model = JobMatch::class;

    public function definition(): array
    {
        return [
            'job_watch_id' => JobWatch::factory(),
            'job_offer_id' => JobOffer::factory(),
            'score' => fake()->numberBetween(40, 100),
            'skill_score' => fake()->numberBetween(40, 100),
            'title_score' => fake()->numberBetween(40, 100),
            'experience_score' => fake()->numberBetween(40, 100),
            'portfolio_score' => fake()->numberBetween(40, 100),
            'location_score' => fake()->numberBetween(40, 100),
            'contract_score' => fake()->numberBetween(40, 100),
            'language_score' => fake()->numberBetween(40, 100),
            'score_details' => [
                'version' => 1,
            ],
            'matched_skills' => [
                'php',
                'laravel',
                'mysql',
            ],
            'missing_skills' => [
                'docker',
            ],
            'status' => 'new',
            'notified_at' => null,
            'viewed_at' => null,
            'saved_at' => null,
            'applied_at' => null,
        ];
    }
}
