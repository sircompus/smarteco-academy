<?php

namespace App\Console\Commands;

use App\Actions\JobWatch\RunJobWatch;
use App\Models\JobWatch;
use App\Services\JobWatch\Importers\MoroccoCsvJobImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportMoroccoJobOffersCommand extends Command
{
    protected $signature = 'job-watch:import-morocco-csv
        {file : Chemin du fichier CSV}
        {--match : Relancer les veilles actives après l’import}';

    protected $description = (
        'Importe un fichier CSV d’offres situées uniquement au Maroc'
    );

    public function handle(
        MoroccoCsvJobImporter $importer,
        RunJobWatch $runJobWatch
    ): int {
        $filePath = $this->resolveFilePath(
            (string) $this->argument('file')
        );

        try {
            $result = $importer->import($filePath);
        } catch (Throwable $exception) {
            $this->error(
                'Échec de l’import : '.$exception->getMessage()
            );

            return self::FAILURE;
        }

        $this->info('Import Maroc terminé.');

        $this->table(
            ['Reçues', 'Créées', 'Mises à jour', 'Ignorées'],
            [[
                $result['received'],
                $result['created'],
                $result['updated'],
                $result['skipped'],
            ]]
        );

        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

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
                'Matching Maroc : %d veille(s), '
                .'%d analyse(s), %d correspondance(s).',
                $watchCount,
                $analyzed,
                $matched
            )
        );

        return self::SUCCESS;
    }

    private function resolveFilePath(string $filePath): string
    {
        if (is_file($filePath)) {
            return $filePath;
        }

        return base_path($filePath);
    }
}
