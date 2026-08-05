<?php

namespace Database\Seeders;

use App\Models\JobOffer;
use App\Models\JobSource;
use App\Models\JobWatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class JobWatchDemoOfferSeeder extends Seeder
{
    public function run(): void
    {
        $jobWatch = JobWatch::query()
            ->with([
                'cvProfile.skills',
                'keywords',
            ])
            ->latest('id')
            ->first();

        if (! $jobWatch instanceof JobWatch) {
            throw new RuntimeException(
                'Aucune veille d’emploi trouvée. Créez d’abord une veille.'
            );
        }

        $source = JobSource::query()
            ->where('name', 'Source de démonstration SmartEco')
            ->first();

        if (! $source instanceof JobSource) {
            /*
             * La factory renseigne automatiquement les autres colonnes
             * obligatoires propres au modèle JobSource.
             */
            $source = JobSource::factory()->create([
                'name' => 'Source de démonstration SmartEco',
            ]);
        }

        $title = collect($jobWatch->target_titles ?? [])
            ->filter()
            ->first()
            ?? $jobWatch->cvProfile?->headline
            ?? 'Consultant systèmes d’information et intelligence artificielle';

        $location = collect($jobWatch->preferred_locations ?? [])
            ->filter()
            ->first()
            ?? 'Casablanca';

        $contractType = collect($jobWatch->contract_types ?? [])
            ->filter()
            ->first()
            ?? 'cdi';

        $remoteMode = in_array(
            $jobWatch->remote_mode,
            ['onsite', 'hybrid', 'remote'],
            true
        )
            ? $jobWatch->remote_mode
            : 'hybrid';

        $cvSkills = $jobWatch->cvProfile?->skills
            ?->pluck('name')
            ->filter()
            ->values()
            ->all() ?? [];

        $includedKeywords = $jobWatch->keywords
            ->where('type', 'include')
            ->pluck('keyword')
            ->filter()
            ->values()
            ->all();

        $skills = collect([
            ...$cvSkills,
            ...$includedKeywords,
        ])
            ->map(fn (mixed $skill): string => trim((string) $skill))
            ->filter()
            ->unique(
                fn (string $skill): string => Str::lower(
                    Str::ascii($skill)
                )
            )
            ->take(10)
            ->values();

        if ($skills->isEmpty()) {
            $skills = collect([
                'Intelligence artificielle',
                'Systèmes d’information',
                'Management',
            ]);
        }

        $externalId = 'smarteco-demo-watch-'.$jobWatch->id;
        $fingerprint = hash(
            'sha256',
            'smarteco-demo-offer-'.$jobWatch->id
        );

        $description = sprintf(
            'Offre de démonstration correspondant à la veille « %s ». '
            .'Compétences recherchées : %s.',
            $jobWatch->name,
            $skills->implode(', ')
        );

        $offer = JobOffer::query()->firstOrNew([
            'job_source_id' => $source->id,
            'external_id' => $externalId,
        ]);

        $offer->fill([
            'title' => $title,
            'normalized_title' => $this->normalize($title),
            'company' => 'SmartEco Demo',
            'normalized_company' => 'smarteco demo',
            'location' => $location,
            'country_code' => 'MA',
            'description' => $description,
            'requirements' => $skills->implode(', '),
            'contract_type' => $contractType,
            'remote_mode' => $remoteMode,
            'experience_level' => 'intermediate',
            'url' => 'https://example.test/jobs/'.$externalId,
            'canonical_url' => 'https://example.test/jobs/'.$externalId,
            'fingerprint' => $fingerprint,
            'raw_payload' => [
                'demo' => true,
                'job_watch_id' => $jobWatch->id,
            ],
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
            'first_seen_at' => $offer->first_seen_at ?? now(),
            'last_seen_at' => now(),
            'status' => 'active',
        ]);

        $offer->save();

        $offer->skills()->delete();

        $offer->skills()->createMany(
            $skills
                ->map(fn (string $skill): array => [
                    'name' => $skill,
                    'normalized_name' => $this->normalize($skill),
                    'importance' => 5,
                    'is_required' => true,
                ])
                ->all()
        );

        $this->command?->info(
            sprintf(
                'Offre démo créée ou mise à jour : ID %d — %s',
                $offer->id,
                $offer->title
            )
        );

        $this->command?->info(
            'Retournez dans la veille et cliquez sur « Lancer la recherche ».'
        );
    }

    private function normalize(string $value): string
    {
        return Str::lower(
            trim(Str::ascii($value))
        );
    }
}
