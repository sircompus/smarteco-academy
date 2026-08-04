<?php

namespace App\Console\Commands;

use App\Actions\JobWatch\RunJobWatch;
use App\Models\JobWatch;
use App\Services\JobWatch\JobOfferImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportJobOffersCommand extends Command
{
    protected $signature = 'job-watch:import-offers
        {--source=arbeitnow : Slug de la source}
        {--pages=1 : Nombre de pages à importer, de 1 à 10}
        {--match : Relancer les veilles actives après l’import}';

    protected $description = (
        'Importe les offres externes et relance éventuellement le matching'
    );

    public function handle(
        JobOfferImporter $importer,
        RunJobWatch $runJobWatch
    ): int {
        $pages = max(
            1,
            min(10, (int) $this->option('pages'))
        );

        try {
            $result = $importer->import(
                (string) $this->option('source'),
                $pages
            );
        } catch (Throwable $exception) {
            $this->error(
                'Échec de l’import : '.$exception->getMessage()
            );

            return self::FAILURE;
        }

        $this->info('Import terminé.');
        $this->table(
            ['Pages', 'Reçues', 'Créées', 'Mises à jour', 'Ignorées'],
            [[
                $result['pages'],
                $result['received'],
                $result['created'],
                $result['updated'],
                $result['skipped'],
            ]]
        );

        if (! $this->option('match')) {
            return self::SUCCESS;
        }

        $watchCount = 0;
        $analyzed = 0;
        $matched = 0;

        JobWatch::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->chunkById(
                50,
                function ($jobWatches) use (
                    $runJobWatch,
                    &$watchCount,
                    &$analyzed,
                    &$matched
                ): void {
                    foreach ($jobWatches as $jobWatch) {
                        $statistics = $runJobWatch->execute(
                            $jobWatch
                        );

                        $watchCount++;
                        $analyzed += $statistics['analyzed'];
                        $matched += $statistics['matched'];
                    }
                }
            );

        $this->info(
            sprintf(
                'Matching terminé : %d veille(s), '
                .'%d analyse(s), %d correspondance(s).',
                $watchCount,
                $analyzed,
                $matched
            )
        );

        return self::SUCCESS;
    }
}
