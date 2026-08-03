<?php

namespace Tests\Unit\JobWatch;

use App\Services\JobWatch\JobTextNormalizer;
use PHPUnit\Framework\TestCase;

class JobTextNormalizerTest extends TestCase
{
    private JobTextNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new JobTextNormalizer;
    }

    public function test_it_normalizes_accents_case_and_punctuation(): void
    {
        $result = $this->normalizer->normalize(
            'Développeur C# / .NET — Télétravail'
        );

        $this->assertSame(
            'developpeur c# .net teletravail',
            $result
        );
    }

    public function test_it_normalizes_and_deduplicates_a_list(): void
    {
        $result = $this->normalizer->normalizeList([
            'Développeur PHP',
            'developpeur php',
            'Laravel',
            '',
            null,
        ]);

        $this->assertSame([
            'developpeur php',
            'laravel',
        ], $result);
    }

    public function test_it_detects_a_complete_phrase(): void
    {
        $this->assertTrue(
            $this->normalizer->containsPhrase(
                'Développeur PHP Senior',
                'développeur php'
            )
        );

        $this->assertFalse(
            $this->normalizer->containsPhrase(
                'Développeur Sphinx',
                'PHP'
            )
        );
    }

    public function test_it_calculates_token_overlap_score(): void
    {
        $score = $this->normalizer->tokenOverlapScore(
            'Développeur Laravel PHP',
            'Développeur PHP Symfony'
        );

        $this->assertSame(67, $score);
    }

    public function test_empty_values_return_safe_results(): void
    {
        $this->assertSame(
            '',
            $this->normalizer->normalize(null)
        );

        $this->assertSame(
            [],
            $this->normalizer->tokens('')
        );

        $this->assertSame(
            0,
            $this->normalizer->tokenOverlapScore('', '')
        );

        $this->assertFalse(
            $this->normalizer->containsPhrase('', 'PHP')
        );
    }
}
