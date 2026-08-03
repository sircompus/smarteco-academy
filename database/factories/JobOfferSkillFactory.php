<?php

namespace Database\Factories;

use App\Models\JobOffer;
use App\Models\JobOfferSkill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobOfferSkill>
 */
class JobOfferSkillFactory extends Factory
{
    protected $model = JobOfferSkill::class;

    public function definition(): array
    {
        $skill = fake()->randomElement([
            'PHP',
            'Laravel',
            'MySQL',
            'JavaScript',
            'Docker',
            'Git',
        ]);

        return [
            'job_offer_id' => JobOffer::factory(),
            'name' => $skill,
            'normalized_name' => Str::lower(Str::ascii($skill)),
            'importance' => fake()->numberBetween(1, 3),
            'is_required' => fake()->boolean(70),
        ];
    }
}
