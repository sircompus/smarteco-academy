<?php

namespace Database\Factories;

use App\Models\JobOffer;
use App\Models\JobSource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobOffer>
 */
class JobOfferFactory extends Factory
{
    protected $model = JobOffer::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Développeur Laravel Junior',
            'Développeur PHP',
            'Data Analyst',
            'Administrateur système',
            'Développeur Full Stack',
        ]);

        $company = fake()->company();
        $minimumSalary = fake()->numberBetween(6000, 15000);
        $maximumSalary = $minimumSalary + fake()->numberBetween(1000, 8000);

        return [
            'job_source_id' => JobSource::factory(),
            'external_id' => fake()->uuid(),
            'title' => $title,
            'normalized_title' => Str::lower(Str::ascii($title)),
            'company' => $company,
            'normalized_company' => Str::lower(Str::ascii($company)),
            'location' => fake()->city(),
            'country_code' => fake()->randomElement([
                'MA',
                'FR',
                'DE',
                'ES',
            ]),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraphs(2, true),
            'contract_type' => fake()->randomElement([
                'cdi',
                'cdd',
                'stage',
                'freelance',
            ]),
            'remote_mode' => fake()->randomElement([
                'onsite',
                'hybrid',
                'remote',
            ]),
            'experience_level' => fake()->randomElement([
                'internship',
                'junior',
                'intermediate',
                'senior',
            ]),
            'salary_min' => $minimumSalary,
            'salary_max' => $maximumSalary,
            'salary_currency' => 'MAD',
            'url' => fake()->url(),
            'canonical_url' => fake()->url(),
            'fingerprint' => hash('sha256', fake()->uuid()),
            'raw_payload' => [],
            'published_at' => now()->subDays(fake()->numberBetween(0, 15)),
            'expires_at' => now()->addDays(fake()->numberBetween(10, 60)),
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'status' => 'active',
        ];
    }
}