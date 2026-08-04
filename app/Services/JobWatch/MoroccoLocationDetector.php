<?php

namespace App\Services\JobWatch;

use Illuminate\Support\Str;

final class MoroccoLocationDetector
{
    private const MOROCCAN_LOCATIONS = [
        'agadir',
        'al hoceima',
        'beni mellal',
        'berrechid',
        'casablanca',
        'dakhla',
        'el jadida',
        'errachidia',
        'essaouira',
        'fes',
        'fès',
        'kenitra',
        'kénitra',
        'khouribga',
        'laayoune',
        'laâyoune',
        'larache',
        'marrakech',
        'meknes',
        'meknès',
        'mohammedia',
        'nador',
        'ouarzazate',
        'oujda',
        'rabat',
        'safi',
        'sale',
        'salé',
        'settat',
        'tanger',
        'tan-tan',
        'temara',
        'témara',
        'tetouan',
        'tétouan',
        'maroc',
        'morocco',
    ];

    public function isMoroccan(?string $countryCode, ?string $location): bool
    {
        if (Str::upper(trim((string) $countryCode)) === 'MA') {
            return true;
        }

        $normalizedLocation = $this->normalize($location);

        if ($normalizedLocation === '') {
            return false;
        }

        foreach (self::MOROCCAN_LOCATIONS as $moroccanLocation) {
            $needle = $this->normalize($moroccanLocation);

            if (
                $normalizedLocation === $needle
                || str_contains($normalizedLocation, $needle)
            ) {
                return true;
            }
        }

        return false;
    }

    public function normalize(?string $value): string
    {
        return Str::lower(
            trim(Str::ascii((string) $value))
        );
    }
}
