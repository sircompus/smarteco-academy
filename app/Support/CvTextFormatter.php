<?php

namespace App\Support;

final class CvTextFormatter
{
    public static function clean(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $lines = preg_split('/\R/u', $text) ?: [];

        $cleanedLines = array_map(
            static function (string $line): string {
                $cleaned = preg_replace(
                    '/^\s*(?:(?:—|–|•)\s*|[-.]\s+)+/u',
                    '',
                    $line
                );

                return trim((string) $cleaned);
            },
            $lines
        );

        return trim(implode(PHP_EOL, $cleanedLines));
    }
}
