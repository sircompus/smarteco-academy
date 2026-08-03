<?php

namespace Tests\Unit\JobWatch;

use App\Services\JobWatch\JobMatchingService;
use App\Services\JobWatch\JobTextNormalizer;
use PHPUnit\Framework\TestCase;

class JobMatchingServiceTest extends TestCase
{
    private JobMatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new JobMatchingService(
            new JobTextNormalizer
        );
    }

    public function test_it_calculates_a_high_score_for_a_matching_offer(): void
    {
        $result = $this->service->calculate(
            $this->watch(),
            $this->offer()
        );

        $this->assertGreaterThanOrEqual(80, $result['score']);
        $this->assertTrue($result['eligible']);
        $this->assertFalse($result['rejected']);
        $this->assertContains('laravel', $result['matched_skills']);
        $this->assertContains('php', $result['matched_skills']);
    }

    public function test_an_excluded_keyword_rejects_the_offer(): void
    {
        $offer = $this->offer();
        $offer['description'] .= ' Poste de directeur technique.';

        $result = $this->service->calculate(
            $this->watch(),
            $offer
        );

        $this->assertSame(0, $result['score']);
        $this->assertFalse($result['eligible']);
        $this->assertTrue($result['rejected']);
        $this->assertContains(
            'directeur',
            $result['rejection_reasons']['excluded_keywords']
        );
    }

    public function test_the_score_is_always_between_zero_and_one_hundred(): void
    {
        $result = $this->service->calculate(
            [
                'target_titles' => [],
                'preferred_locations' => [],
                'contract_types' => [],
                'remote_mode' => 'any',
                'minimum_score' => 0,
                'keywords' => [],
            ],
            [
                'title' => '',
                'company' => '',
                'location' => '',
                'description' => '',
                'requirements' => '',
                'contract_type' => null,
                'remote_mode' => null,
                'skills' => [],
            ]
        );

        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
    }

    public function test_minimum_score_controls_eligibility(): void
    {
        $watch = $this->watch();
        $watch['minimum_score'] = 101;

        $result = $this->service->calculate(
            $watch,
            $this->offer()
        );

        $this->assertFalse($result['eligible']);
        $this->assertFalse($result['rejected']);
    }

    public function test_missing_skills_are_reported(): void
    {
        $offer = $this->offer();
        $offer['skills'] = ['php'];
        $offer['description'] = 'Développement PHP sans framework.';

        $result = $this->service->calculate(
            $this->watch(),
            $offer
        );

        $this->assertContains('php', $result['matched_skills']);
        $this->assertContains('laravel', $result['missing_skills']);
    }

    private function watch(): array
    {
        return [
            'target_titles' => [
                'Développeur Laravel',
                'Développeur PHP',
            ],
            'preferred_locations' => [
                'Casablanca',
            ],
            'contract_types' => [
                'cdi',
            ],
            'remote_mode' => 'hybrid',
            'minimum_score' => 70,
            'keywords' => [
                [
                    'keyword' => 'Laravel',
                    'type' => 'include',
                    'weight' => 2,
                ],
                [
                    'keyword' => 'PHP',
                    'type' => 'include',
                    'weight' => 1,
                ],
                [
                    'keyword' => 'Directeur',
                    'type' => 'exclude',
                    'weight' => 1,
                ],
            ],
        ];
    }

    private function offer(): array
    {
        return [
            'title' => 'Développeur Laravel PHP',
            'company' => 'SmartEco',
            'location' => 'Casablanca',
            'description' => 'Développement web avec Laravel et PHP.',
            'requirements' => 'Bonne maîtrise de MySQL.',
            'contract_type' => 'cdi',
            'remote_mode' => 'hybrid',
            'skills' => [
                'Laravel',
                'PHP',
                'MySQL',
            ],
        ];
    }
}
