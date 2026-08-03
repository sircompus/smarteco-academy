<?php

namespace App\Services\JobWatch;

use Illuminate\Support\Str;

class JobTextNormalizer
{
    public function normalize(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $normalized = Str::lower(
            Str::ascii($value)
        );

        $normalized = preg_replace(
            '/[^a-z0-9+#.]+/',
            ' ',
            $normalized
        ) ?? '';

        return trim(
            preg_replace('/\s+/', ' ', $normalized) ?? ''
        );
    }

    public function normalizeList(array $values): array
    {
        return collect($values)
            ->map(function (mixed $value): string {
                if (! is_scalar($value)) {
                    return '';
                }

                return $this->normalize((string) $value);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function tokens(?string $value): array
    {
        $normalized = $this->normalize($value);

        if ($normalized === '') {
            return [];
        }

        return collect(explode(' ', $normalized))
            ->filter(
                fn (string $token): bool => strlen($token) >= 2
            )
            ->unique()
            ->values()
            ->all();
    }

    public function containsPhrase(
        ?string $text,
        ?string $phrase
    ): bool {
        $normalizedText = $this->normalize($text);
        $normalizedPhrase = $this->normalize($phrase);

        if (
            $normalizedText === ''
            || $normalizedPhrase === ''
        ) {
            return false;
        }

        return str_contains(
            ' '.$normalizedText.' ',
            ' '.$normalizedPhrase.' '
        );
    }

    public function tokenOverlapScore(
        ?string $reference,
        ?string $candidate
    ): int {
        $referenceTokens = $this->tokens($reference);
        $candidateTokens = $this->tokens($candidate);

        if (
            $referenceTokens === []
            || $candidateTokens === []
        ) {
            return 0;
        }

        $matches = count(
            array_intersect(
                $referenceTokens,
                $candidateTokens
            )
        );

        return (int) round(
            ($matches / count($referenceTokens)) * 100
        );
    }
}
