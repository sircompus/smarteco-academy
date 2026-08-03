<?php

namespace Database\Factories;

use App\Models\JobWatch;
use App\Models\JobWatchKeyword;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobWatchKeyword>
 */
class JobWatchKeywordFactory extends Factory
{
    protected $model = JobWatchKeyword::class;

    public function definition(): array
    {
        $keyword = fake()->randomElement([
            'Laravel',
            'PHP',
            'MySQL',
            'JavaScript',
            'Docker',
            'Git',
        ]);

        return [
            'job_watch_id' => JobWatch::factory(),
            'keyword' => $keyword,
            'normalized_keyword' => Str::lower(Str::ascii($keyword)),
            'type' => fake()->randomElement([
                'include',
                'exclude',
                'title',
                'skill',
            ]),
            'weight' => fake()->numberBetween(1, 10),
        ];
    }
}
